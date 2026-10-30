<?php

namespace App\Services\Purchase;

class ProductMatcher
{
    public function normalize(string $name): string
    {
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($name)) ?? $name);
    }

    public function unit(string $unit): string
    {
        $unit = $this->normalize($unit);
        return match ($unit) {
            'kgs', 'kilogram', 'kilograms' => 'kg',
            'l', 'ltr', 'ltrs', 'litres', 'liters', 'liter' => 'litre',
            'pcs', 'pc', 'pieces', 'nos', 'no', 'each', 'ea' => 'piece',
            'g', 'grams' => 'gram',
            'ml', 'millilitres' => 'millilitre',
            default => $unit,
        };
    }

    public function match(string $name, string $unit, iterable $products): mixed
    {
        $matches = [];
        foreach ($products as $product) {
            if ($this->normalize($name) !== $this->normalize((string) $product['name'])) {
                continue;
            }
            $productUnit = (string) ($product['unit'] ?? '');
            if ($productUnit !== '' && $this->unit($productUnit) !== $this->unit($unit)) {
                continue;
            }
            $matches[] = $product;
        }
        // Ambiguous matches need human selection, never an arbitrary stock merge.
        return count($matches) === 1 ? $matches[0] : null;
    }
}
