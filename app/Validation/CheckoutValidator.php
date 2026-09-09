<?php

declare(strict_types=1);

namespace App\Validation;

use App\Support\Validator;

/**
 * Validation for the checkout form (the shipping details).
 */
final class CheckoutValidator extends Validator
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validate(array $data): array
    {
        $this->reset();

        $fullName = (string) ($data['full_name'] ?? '');
        $address  = (string) ($data['address'] ?? '');
        $city     = (string) ($data['city'] ?? '');
        $phone    = (string) ($data['phone'] ?? '');

        if ($this->required('full_name', $fullName, 'Full name')) {
            $this->between('full_name', $fullName, 2, 100, 'Full name');
        }

        if ($this->required('address', $address, 'Address')) {
            $this->between('address', $address, 5, 255, 'Address');
        }

        if ($this->required('city', $city, 'City')) {
            $this->between('city', $city, 2, 100, 'City');
        }

        if ($this->required('phone', $phone, 'Phone number')) {
            if (!preg_match('/^[0-9 +\-()]{6,20}$/', $phone)) {
                $this->addError('phone', 'Please enter a valid phone number.');
            }
        }

        return $this->errors;
    }
}
