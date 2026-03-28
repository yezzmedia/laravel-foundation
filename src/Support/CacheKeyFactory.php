<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class CacheKeyFactory
{
    public function __construct(
        private readonly string $prefix = 'website',
        private readonly string $separator = ':',
    ) {}

    /**
     * @param  array<int, string|int>  $segments
     */
    public function make(string $package, string $domain, string $key, array $segments = []): string
    {
        $parts = [
            $this->normalize($this->prefix, 'prefix'),
            $this->normalize($package, 'package'),
            $this->normalize($domain, 'domain'),
            $this->normalize($key, 'key'),
        ];

        foreach ($segments as $segment) {
            $parts[] = $this->normalize((string) $segment, 'segment');
        }

        return implode($this->separator(), $parts);
    }

    private function normalize(string $value, string $name): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidPackageDefinitionException(sprintf('Cache key %s must not be empty.', $name));
        }

        return strtr($normalized, [
            '%' => '%25',
            $this->separator() => $this->escapedSeparator(),
        ]);
    }

    private function escapedSeparator(): string
    {
        return sprintf('%%%02X', ord($this->separator()));
    }

    private function separator(): string
    {
        $separator = trim($this->separator);

        if ($separator === '') {
            throw new InvalidPackageDefinitionException('Cache key separator must not be empty.');
        }

        return $separator;
    }
}
