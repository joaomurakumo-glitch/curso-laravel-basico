<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidCpf implements ValidationRule
{
    /**
     * Converte a forma pontuada para 11 dígitos, se a máscara recebida for válida.
     */
    public static function normalize(string $cpf): string
    {
        // Um CPF sem máscara já está no formato que a regra de validação espera.
        if (preg_match('/^\d{11}$/D', $cpf) === 1) {
            return $cpf;
        }

        if (preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/D', $cpf) === 1) {
            return str_replace(['.', '-'], '', $cpf);
        }

        return $cpf;
    }

    /**
     * Confere o formato e os dois dígitos verificadores do CPF.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Primeiro garante que existem exatamente 11 números; sem isso não há como calcular.
        if (! is_string($value) || preg_match('/^\d{11}$/D', $value) !== 1) {
            $fail('O CPF informado é inválido.');

            return;
        }

        if (preg_match('/^(\d)\1{10}$/D', $value) === 1) {
            $fail('O CPF informado é inválido.');

            return;
        }

        $digits = str_split($value);

        // Para cada dígito verificador, multiplica os números anteriores por pesos decrescentes.
        foreach ([9, 10] as $length) {
            $sum = 0;

            for ($index = 0; $index < $length; $index++) {
                $sum += (int) $digits[$index] * ($length + 1 - $index);
            }

            // O resto da divisão determina o dígito esperado; resto 10 vira zero.
            $remainder = ($sum * 10) % 11;
            $checkDigit = $remainder === 10 ? 0 : $remainder;

            if ($checkDigit !== (int) $digits[$length]) {
                $fail('O CPF informado é inválido.');

                return;
            }
        }
    }
}
