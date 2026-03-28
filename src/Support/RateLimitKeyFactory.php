<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Data\RateLimitDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

/**
 * Builds deterministic keys for rate limit definitions and request context.
 */
class RateLimitKeyFactory
{
    public function __construct(private readonly string $separator = ':') {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function make(RateLimitDefinition $definition, array $context): string
    {
        $key = $this->value($definition->key, 'key');
        $separator = $this->separator();

        return match ($definition->scope) {
            'ip' => implode($separator, [$key, 'ip', $this->contextValue($context, 'ip')]),
            'user' => implode($separator, [$key, 'user', $this->contextValue($context, 'user')]),
            'ip_user' => implode($separator, [$key, 'ip_user', $this->contextValue($context, 'ip'), $this->contextValue($context, 'user')]),
            'custom' => implode($separator, [$key, 'custom', $this->customValue($context)]),
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

        return $this->normalize($value, $key);
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

        return implode($this->separator(), array_map(
            fn (string $segment): string => $this->normalize($segment, 'segment'),
            $segments,
        ));
    }

    private function normalize(string $value, string $name): string
    {
        // Escaping keeps dynamic values from changing the structural key shape.
        return strtr($this->value($value, $name), [
            '%' => '%25',
            $this->separator() => $this->escapedSeparator(),
        ]);
    }

    private function value(string $value, string $name): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidPackageDefinitionException(sprintf('Rate limit %s must not be empty.', $name));
        }

        return $normalized;
    }

    private function escapedSeparator(): string
    {
        return sprintf('%%%02X', ord($this->separator()));
    }

    private function separator(): string
    {
        $separator = trim($this->separator);

        if ($separator === '') {
            throw new InvalidPackageDefinitionException('Rate limit separator must not be empty.');
        }

        return $separator;
    }
}
