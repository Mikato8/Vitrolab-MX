<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CatalogRepository;

/**
 * Verificación de miembros de la comunidad sin cédula profesional.
 *
 * Reglas:
 *  - Correo con dominio de una institución registrada → verificación automática.
 *  - Correo .edu.mx → verificación automática.
 *  - Cualquier otro correo → revisión manual; la persona puede subir una
 *    constancia o credencial institucional y un editor decide.
 */
final class MemberVerifier
{
    /** @return array{status: string, method: string, institution_id: ?int, domain: string, message: string} */
    public function evaluate(string $email, ?int $selectedInstitutionId = null): array
    {
        $domain = $this->domain($email);

        if ($domain === '') {
            return [
                'status' => 'pendiente',
                'method' => 'manual',
                'institution_id' => $selectedInstitutionId,
                'domain' => '',
                'message' => 'Usa un correo válido para que podamos verificar tu cuenta.',
            ];
        }

        $institution = (new CatalogRepository())->institutionByEmailDomain($domain);

        if ($institution !== null) {
            return [
                'status' => 'verificado',
                'method' => 'dominio_institucional',
                'institution_id' => $selectedInstitutionId ?: (int) $institution['id'],
                'domain' => $domain,
                'message' => 'Tu correo institucional coincide con ' . $institution['name'] . '. La cuenta quedó verificada.',
            ];
        }

        if (str_ends_with($domain, '.edu.mx')) {
            return [
                'status' => 'verificado',
                'method' => 'dominio_institucional',
                'institution_id' => $selectedInstitutionId,
                'domain' => $domain,
                'message' => 'Tu correo .edu.mx confirma tu pertenencia institucional. La cuenta quedó verificada.',
            ];
        }

        if ((bool) config('app.auto_verify_users', false)) {
            return [
                'status' => 'verificado',
                'method' => 'admin',
                'institution_id' => $selectedInstitutionId,
                'domain' => $domain,
                'message' => 'La verificación automática está activa en este entorno.',
            ];
        }

        return [
            'status' => 'pendiente',
            'method' => 'manual',
            'institution_id' => $selectedInstitutionId,
            'domain' => $domain,
            'message' => 'Tu correo no es institucional. Puedes subir una constancia o credencial; un editor revisará tu cuenta.',
        ];
    }

    public function isInstitutionalDomain(string $email): bool
    {
        $domain = $this->domain($email);
        if ($domain === '') {
            return false;
        }
        if (str_ends_with($domain, '.edu.mx')) {
            return true;
        }
        return (new CatalogRepository())->institutionByEmailDomain($domain) !== null;
    }

    public function domain(string $email): string
    {
        $email = mb_strtolower(trim($email));
        $position = mb_strrpos($email, '@');
        if ($position === false) {
            return '';
        }
        return trim(substr($email, $position + 1), " \t\n\r\0\x0B.");
    }
}
