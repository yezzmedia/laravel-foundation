<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Data\RateLimitDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class RateLimitKeyFactory
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function make(RateLimitDefinition $definition, array $context): string
    {
        return match ($definition->scope) {
            'ip' => sprintf('%s:ip:%s', $definition->key, $this->contextValue($context, 'ip')),
            'user' => sprintf('%s:user:%s', $definition->key, $this->contextValue($context, 'user')),
            'ip_user' => sprintf(
                '%s:ip_user:%s:%s',
                $definition->key,
                $this->contextValue($context, 'ip'),
                $this->contextValue($context, 'user'),
            ),
            'custom' => sprintf('%s:custom:%s', $definition->key, $this->customValue($context)),
            default => throw new InvalidPackageDefinitionException(sprintf(
                'Rate limiter [%s] has unsupported scope [%s].',
                $definition->key,
                $definition->scope,
            )),
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function contextValue(array $context, string $key): string
    {
        if (! array_key_exists($key, $context)) {
            throw new InvalidPackageDefinitionException(sprintf('Rate limit context [%s] is required.', $key));
        }

        $value = trim((string) $context[$key]);

        if ($value === '') {
            throw new InvalidPackageDefinitionException(sprintf('Rate limit context [%s] must not be empty.', $key));
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function customValue(array $context): string
    {
        if (array_key_exists('custom', $context)) {
            return $this->contextValue($context, 'custom');
        }

        if (! array_key_exists('segments', $context) || ! is_array($context['segments'])) {
            throw new InvalidPackageDefinitionException('Rate limit custom scope requires a custom value or segments array.');
        }

        $segments = array_map(
            fn (mixed $segment): string => trim((string) $segment),
            $context['segments'],
        );

        $segments = array_values(array_filter($segments, static fn (string $segment): bool => $segment !== ''));

        if ($segments === []) {
            throw new InvalidPackageDefinitionException('Rate limit custom segments must not be empty.');
        }

        return implode(':', $segments);
    }
}
