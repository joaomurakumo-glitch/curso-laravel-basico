<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidCpf implements ValidationRule
{
    public static function normalize(string $cpf): string
    {
        if (preg_match('/^\d{11}$/D', $cpf) === 1) {
            return $cpf;
        }

        if (preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/D', $cpf) === 1) {
            return str_replace(['.', '-'], '', $cpf);
        }

        return $cpf;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/^\d{11}$/D', $value) !== 1) {
            $fail('O CPF informado é inválido.');

            return;
        }

        if (preg_match('/^(\d)\1{10}$/D', $value) === 1) {
            $fail('O CPF informado é inválido.');

            return;
        }

        $digits = str_split($value);

        foreach ([9, 10] as $length) {
            $sum = 0;

            for ($index = 0; $index < $length; $index++) {
                $sum += (int) $digits[$index] * ($length + 1 - $index);
            }

            $remainder = ($sum * 10) % 11;
            $checkDigit = $remainder === 10 ? 0 : $remainder;

            if ($checkDigit !== (int) $digits[$length]) {
                $fail('O CPF informado é inválido.');

                return;
            }
        }
    }
}
