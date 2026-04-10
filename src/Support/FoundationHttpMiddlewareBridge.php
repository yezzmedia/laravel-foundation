<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Router;

class FoundationHttpMiddlewareBridge
{
    public function __construct(private readonly HttpMiddlewareResolver $resolver) {}

    public static function apply(Middleware $middleware): void
    {
        $container = Container::getInstance();

        if (! $container instanceof Application) {
            return;
        }

        $apply = static function () use ($container, $middleware): void {
            if (! $container->bound(self::class)) {
                return;
            }

            /** @var self $bridge */
            $bridge = $container->make(self::class);
            $bridge->applyToConfiguration($middleware);

            if ($container->bound(Router::class)) {
                $bridge->applyToRouter($container->make(Router::class));
            }
        };

        if ($container->isBooted()) {
            $apply();

            return;
        }

        $container->booted($apply);
    }

    public function applyToConfiguration(Middleware $middleware): void
    {
        $resolved = $this->resolver->resolve();

        $aliases = array_filter(
            $resolved['aliases'],
            static fn (string $middlewareClass, string $alias): bool => ($middleware->getMiddlewareAliases()[$alias] ?? null) !== $middlewareClass,
            ARRAY_FILTER_USE_BOTH,
        );

        if ($aliases !== []) {
            $middleware->alias($aliases);
        }

        $prepends = $this->missingMiddleware($middleware->getMiddlewareGroups()['web'] ?? [], $resolved['web_prepend']);

        if ($prepends !== []) {
            $middleware->web(prepend: $prepends);
        }

        $appends = $this->missingMiddleware($middleware->getMiddlewareGroups()['web'] ?? [], $resolved['web_append']);

        if ($appends !== []) {
            $middleware->web(append: $appends);
        }
    }

    public function applyToRouter(Router $router): void
    {
        $resolved = $this->resolver->resolve();

        foreach ($resolved['aliases'] as $alias => $middlewareClass) {
            if (($router->getMiddleware()[$alias] ?? null) === $middlewareClass) {
                continue;
            }

            $router->aliasMiddleware($alias, $middlewareClass);
        }

        $prepends = $this->missingMiddleware($router->getMiddlewareGroups()['web'] ?? [], $resolved['web_prepend']);

        foreach (array_reverse($prepends) as $middlewareClass) {
            $router->prependMiddlewareToGroup('web', $middlewareClass);
        }

        $appends = $this->missingMiddleware($router->getMiddlewareGroups()['web'] ?? [], $resolved['web_append']);

        foreach ($appends as $middlewareClass) {
            $router->pushMiddlewareToGroup('web', $middlewareClass);
        }
    }

    /**
     * @param  array<int, string>  $current
     * @param  array<int, string>  $desired
     * @return array<int, string>
     */
    private function missingMiddleware(array $current, array $desired): array
    {
        return array_values(array_filter(
            $desired,
            static fn (string $middlewareClass): bool => ! in_array($middlewareClass, $current, true),
        ));
    }
}
