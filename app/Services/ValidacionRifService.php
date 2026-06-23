<?php

namespace App\Services;

class ValidacionRifService
{
    protected array $letras = [
        'V' => 'Venezolano',
        'E' => 'Extranjero',
        'J' => 'Jurídico',
        'G' => 'Gubernamental',
        'P' => 'Pasaporte',
    ];

    protected array $pesos = [4, 3, 2, 7, 6, 5, 4, 3, 2];

    public function letrasValidas(): array
    {
        return $this->letras;
    }

    public function letras(): array
    {
        return array_keys($this->letras);
    }

    public function calcularDigito(string $letra, string $numero): ?string
    {
        $letra = strtoupper($letra);
        if (!isset($this->letras[$letra])) {
            return null;
        }

        $numero = preg_replace('/\D/', '', $numero);
        if ($numero === '' || strlen($numero) > 9) {
            return null;
        }

        if (strlen($numero) > 8) {
            $numero = substr($numero, -8);
        }

        $valorLetra = array_search($letra, array_keys($this->letras)) + 1;
        $numeroPadded = str_pad($numero, 8, '0', STR_PAD_LEFT);
        $digitos = array_merge([$valorLetra], array_map('intval', str_split($numeroPadded)));

        $suma = 0;
        foreach ($digitos as $i => $d) {
            $suma += $d * $this->pesos[$i];
        }

        $resto = $suma % 11;
        $digito = 11 - $resto;

        if ($digito === 11 || $digito === 10) {
            return '0';
        }

        return (string) $digito;
    }

    public function validar(?string $rif): bool
    {
        if ($rif === null || $rif === '') {
            return true;
        }

        if (!preg_match('/^([VEGJP])-(\d{1,9})-(\d)$/', strtoupper($rif), $m)) {
            return false;
        }

        $digitoCalculado = $this->calcularDigito($m[1], $m[2]);

        return $digitoCalculado === $m[3];
    }

    public function parsear(?string $rif): array
    {
        if ($rif === null || $rif === '') {
            return ['letra' => 'V', 'numero' => '', 'digito' => null];
        }

        if (preg_match('/^([VEGJP])-?(\d{1,9})-?(\d)$/', strtoupper($rif), $m)) {
            return [
                'letra' => $m[1],
                'numero' => $m[2],
                'digito' => $m[3],
            ];
        }

        return ['letra' => 'V', 'numero' => '', 'digito' => null];
    }
}
