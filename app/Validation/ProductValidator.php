<?php

declare(strict_types=1);

namespace App\Validation;

use App\Support\Validator;

/**
 * Server side validation for the admin product form.
 */
final class ProductValidator extends Validator
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validate(array $data): array
    {
        $this->reset();

        $name        = (string) ($data['name'] ?? '');
        $price       = (string) ($data['price'] ?? '');
        $stock       = (string) ($data['stock'] ?? '0');
        $description = (string) ($data['description'] ?? '');

        if ($this->required('name', $name, 'Product name')) {
            $this->between('name', $name, 2, 150, 'Product name');
        }

        if ($this->required('price', $price, 'Price')) {
            $this->price('price', $price, 'Price');
        }

        $this->integerBetween('stock', $stock, 0, 100000, 'Stock');

        if ($description !== '' && mb_strlen($description) > 2000) {
            $this->addError('description', 'The description may not be longer than 2000 characters.');
        }

        return $this->errors;
    }
}
