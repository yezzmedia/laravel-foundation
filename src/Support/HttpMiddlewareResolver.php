<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;
use YezzMedia\Foundation\Registry\HttpMiddlewareRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;

class HttpMiddlewareResolver
{
    public function __construct(
        private readonly HttpMiddlewareRegistry $definitions,
        private readonly PackageRegistry $packages,
    ) {}

    /**
     * @return array{aliases: array<string, string>, web_prepend: array<int, string>, web_append: array<int, string>}
     */
    public function resolve(): array
    {
        $definitions = $this->sortedDefinitions();

        return [
            'aliases' => $this->resolveAliases($definitions),
            'web_prepend' => $this->resolveMiddlewareList($definitions, 'web_prepend'),
            'web_append' => $this->resolveMiddlewareList($definitions, 'web_append'),
        ];
    }

    /**
     * @return array<int, HttpMiddlewareDefinition>
     */
    private function sortedDefinitions(): array
    {
        $definitions = $this->definitions
            ->all()
            ->filter(static fn (HttpMiddlewareDefinition $definition): bool => $definition->enabled)
            ->values()
            ->all();

        usort($definitions, function (HttpMiddlewareDefinition $left, HttpMiddlewareDefinition $right): int {
            $priorityComparison = $this->packagePriority($left->package) <=> $this->packagePriority($right->package);

            if ($priorityComparison !== 0) {
                return $priorityComparison;
            }

            $packageComparison = $left->package <=> $right->package;

            if ($packageComparison !== 0) {
                return $packageComparison;
            }

            return $left->key <=> $right->key;
        });

        return $definitions;
    }

    /**
     * @param  array<int, HttpMiddlewareDefinition>  $definitions
     * @return array<string, string>
     */
    private function resolveAliases(array $definitions): array
    {
        $aliases = [];

        foreach ($definitions as $definition) {
            if ($definition->kind !== 'alias' || $definition->alias === null) {
                continue;
            }

            $aliases[$definition->alias] = $definition->middleware;
        }

        return $aliases;
    }

    /**
     * @param  array<int, HttpMiddlewareDefinition>  $definitions
     * @return array<int, string>
     */
    private function resolveMiddlewareList(array $definitions, string $kind): array
    {
        $middleware = [];

        foreach ($definitions as $definition) {
            if ($definition->kind !== $kind) {
                continue;
            }

            if (! in_array($definition->middleware, $middleware, true)) {
                $middleware[] = $definition->middleware;
            }
        }

        return $middleware;
    }

    private function packagePriority(string $package): int
    {
        return $this->packages->find($package)?->priority ?? PHP_INT_MAX;
    }
}
