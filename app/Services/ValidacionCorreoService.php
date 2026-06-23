<?php

namespace App\Services;

class ValidacionCorreoService
{
    protected array $desechables = [
        'mailinator.com', '10minutemail.com', 'guerrillamail.com', 'sharklasers.com',
        'grr.la', 'yopmail.com', 'tempmail.com', 'temp-mail.org', 'throwaway.email',
        'maildrop.cc', 'getnada.com', 'mailexpire.com', 'mailmo.xyz',
        'dispostable.com', 'spamgourmet.com', 'trashmail.com', 'trashmail.me',
        'fakeinbox.com', 'emailondeck.com', 'inboxbear.com', 'mailnator.com',
        'mailcatch.com', 'mytemp.email', 'tempemail.net', 'tempinbox.com',
        'thisdomain.com', 'spambox.us', 'maileater.com', 'mailexpire.com',
        'emailtemporario.com.br', 'temporarioemail.org', 'anonbox.net',
        '0-00.usa.cc', '0815.ru', '0clickemail.com', '1-8.biz',
        '1000rebut.com', '1000rebates.com', '100likers.com', '105kg.ru',
        '10host.pro', '10mail.org', '10mail.xyz', '10minut.xyz',
        '10minutemail.be', '10minutemail.cf', '10minutemail.co.uk',
        '10minutemail.co.za', '10minutemail.de', '10minutemail.ga',
        '10minutemail.gq', '10minutemail.ml', '10minutemail.net',
        '10minutemail.nl', '10minutemail.org', '10minutemail.ru',
        '10minutemail.xyz', '10minutemail.pro', '10minutenemail.de',
        '10minutesmail.com', '10x9.com', '123-m.com', '12hosting.net',
        '14net.net', '1ce.us', '1chuan.com', '1clrk.com',
        '1mail.ml', '1pad.de', '1s.fr', '1st-forms.com',
        '2-temp-mail.com', '2.0-00.usa.cc', '2.safe-mail.net',
        '2anom.com', '2ch.2y.net', '2ch.com', '2ch.hk',
        '2ether.net', '2iij.com', '2nd-mail.com', '2prong.com',
        '2t8.de', '2wc.info', '3.forta.cf', '3.forta.ga',
        '3.forta.gq', '3.forta.ml', '3.forta.tk', '3d-painting.com',
        '3mail.com', '3trtretgfrferytfgdfjg.usa.cc', '4-n.us',
        '4057.com', '418a.com', '4gfdsa.ddns.net',
        '4mail.cf', '4mail.ga', '4mail.gq', '4mail.ml',
        '4mail.tk', '4nextmail.com',
    ];

    public function validarSintaxis(string $correo): bool
    {
        $correo = trim($correo);
        if ($correo === '') {
            return false;
        }

        if (preg_match('/[\x80-\xFF#\$%\/\^\*&\|\(\)\{\}\[\]\;\:\<\>\,\?\\\`\'\"]/', $correo)) {
            return false;
        }

        if (str_contains($correo, ' ') || str_contains($correo, "\t") || str_contains($correo, "\n")) {
            return false;
        }

        return (bool) filter_var($correo, FILTER_VALIDATE_EMAIL);
    }

    public function esDesechable(string $correo): bool
    {
        $dominio = $this->extraerDominio($correo);
        if ($dominio === null) {
            return false;
        }

        $dominio = strtolower($dominio);

        if (in_array($dominio, $this->desechables, true)) {
            return true;
        }

        $partes = explode('.', $dominio);
        if (count($partes) >= 2) {
            $base = $partes[count($partes) - 2] . '.' . $partes[count($partes) - 1];
            if (in_array($base, $this->desechables, true)) {
                return true;
            }
        }

        foreach ($this->desechables as $desechable) {
            if (str_ends_with($dominio, '.' . $desechable)) {
                return true;
            }
        }

        return false;
    }

    public function verificarMX(string $correo): bool
    {
        $dominio = $this->extraerDominio($correo);
        if ($dominio === null) {
            return false;
        }

        $registros = @dns_get_record($dominio, DNS_MX);
        if ($registros === false || count($registros) === 0) {
            $registros = @dns_get_record($dominio, DNS_A);
            if ($registros === false || count($registros) === 0) {
                return false;
            }
        }

        return true;
    }

    public function extraerDominio(string $correo): ?string
    {
        $correo = trim($correo);
        $partes = explode('@', $correo);
        if (count($partes) !== 2) {
            return null;
        }
        return $partes[1];
    }

    public function validarCompleto(string $correo, bool $incluirMX = false): array
    {
        $correo = trim($correo);

        if ($correo === '') {
            return ['valido' => false, 'error' => null, 'errores' => []];
        }

        if (!$this->validarSintaxis($correo)) {
            return ['valido' => false, 'error' => 'Formato de correo inválido', 'errores' => ['sintaxis']];
        }

        if ($this->esDesechable($correo)) {
            return ['valido' => false, 'error' => 'No se permiten correos temporales', 'errores' => ['desechable']];
        }

        if ($incluirMX && !$this->verificarMX($correo)) {
            return ['valido' => false, 'error' => 'El dominio del correo no existe', 'errores' => ['mx']];
        }

        return ['valido' => true, 'error' => null, 'errores' => []];
    }
}
