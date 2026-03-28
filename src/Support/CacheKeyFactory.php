<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class CacheKeyFactory
{
    private const SEPARATOR = ':';

    /**
     * @param  array<int, string|int>  $segments
     */
    public function make(string $package, string $domain, string $key, array $segments = []): string
    {
        $parts = [
            'website',
            $this->normalize($package, 'package'),
            $this->normalize($domain, 'domain'),
            $this->normalize($key, 'key'),
        ];

        foreach ($segments as $segment) {
            $parts[] = $this->normalize((string) $segment, 'segment');
        }

        return implode(self::SEPARATOR, $parts);
    }

    private function normalize(string $value, string $name): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidPackageDefinitionException(sprintf('Cache key %s must not be empty.', $name));
        }

        return strtr($normalized, [
            '%' => '%25',
            self::SEPARATOR => '%3A',
        ]);
    }
}
