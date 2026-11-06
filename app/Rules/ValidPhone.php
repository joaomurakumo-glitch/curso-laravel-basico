<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidPhone implements ValidationRule
{
    /**
     * Remove a máscara visual quando o telefone está em um dos formatos aceitos.
     */
    public static function normalize(string $phone): string
    {
        // O formato simples já contém apenas os números necessários.
        if (preg_match('/^\d{11}$/D', $phone) === 1) {
            return $phone;
        }

        if (preg_match('/^\(\d{2}\) 9\d{4}-\d{4}$/D', $phone) === 1) {
            return str_replace(['(', ')', ' ', '-'], '', $phone);
        }

        return $phone;
    }

    /**
     * Exige DDD e 11 dígitos, com o nono dígito indicando um celular.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // O DDD ocupa os dois primeiros dígitos; o terceiro deve ser 9 para celulares.
        if (
            ! is_string($value)
            || preg_match('/^\d{11}$/D', $value) !== 1
            || $value[2] !== '9'
        ) {
            $fail('Informe um celular válido com DDD e 11 dígitos, começando com 9.');
        }
    }
}
