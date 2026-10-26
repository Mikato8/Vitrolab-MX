<?php

declare(strict_types=1);

namespace App\Support;

final class CasePdf
{
    /** @param array<string, mixed> $case */
    public static function render(array $case): string
    {
        $pdf = new SimplePdf((string) $case['title']);
        $pdf->title((string) $case['title']);
        $pdf->label('Resumen');
        $pdf->paragraph((string) $case['summary'], 12, 17);
        $pdf->small('Folio ' . $case['folio'] . ' · ' . format_date($case['published_at'] ?? null, 'd/m/Y') . ' · ' . ($case['author_name'] ?? ''));
        if (!empty($case['institution_name'])) {
            $pdf->small('Institución: ' . $case['institution_name']);
        }
        $pdf->space(6);

        $pdf->heading('Ficha del caso');
        $pdf->paragraph('Especie: ' . ($case['scientific_name'] ?? $case['species_text'] ?? '—') . ' · Familia: ' . ($case['family'] ?? '—'));
        $pdf->paragraph('Línea: ' . ($case['category_name'] ?? '—') . ' · Estado: ' . ($case['state_name'] ?? 'No especificado') . ' · Año: ' . ($case['year'] ?? '—'));
        $pdf->paragraph('Explante: ' . ($case['explant'] ?? '—'));
        $pdf->paragraph('Medio: ' . ($case['medium'] ?? '—'));
        $pdf->paragraph('Reguladores: ' . ($case['pgr'] ?? 'No especificados'));
        $pdf->paragraph('Condiciones: ' . ($case['conditions'] ?? '—'));
        if (!empty($case['duration_weeks'])) {
            $pdf->paragraph('Duración: ' . (int) $case['duration_weeks'] . ' semanas');
        }

        $metrics = [];
        if ($case['contamination_pct'] !== null) {
            $metrics[] = 'Contaminación ' . pct($case['contamination_pct']);
        }
        if ($case['survival_pct'] !== null) {
            $metrics[] = 'Supervivencia ' . pct($case['survival_pct']);
        }
        if ($case['multiplication_rate'] !== null) {
            $metrics[] = 'Multiplicación ' . ratio($case['multiplication_rate']);
        }
        if ($case['rooting_pct'] !== null) {
            $metrics[] = 'Enraizamiento ' . pct($case['rooting_pct']);
        }
        if ($case['acclimatization_pct'] !== null) {
            $metrics[] = 'Aclimatación ' . pct($case['acclimatization_pct']);
        }
        if ($metrics !== []) {
            $pdf->heading('Métricas reportadas');
            $pdf->paragraph(implode(' · ', $metrics));
        }

        if (!empty($case['images'])) {
            $pdf->heading('Imágenes del caso');
            foreach ($case['images'] as $image) {
                $path = BASE_PATH . '/public/uploads/casos/' . ltrim((string) $image['path'], '/');
                $pdf->image($path, (string) ($image['caption'] ?? ''));
            }
        }

        self::section($pdf, 'Objetivo', (string) $case['objective']);
        self::section($pdf, 'Contexto y material de partida', (string) $case['context']);
        self::section($pdf, 'Desinfección', (string) $case['disinfection']);
        self::section($pdf, 'Protocolo paso a paso', (string) $case['protocol']);
        self::section($pdf, 'Resultados', (string) $case['results']);
        self::section($pdf, 'Dificultades y ajustes', (string) $case['challenges']);
        self::section($pdf, 'Aprendizajes', (string) $case['lessons']);
        self::section($pdf, 'Referencias', (string) $case['references_text']);

        $pdf->heading('Cómo citar este caso');
        $year = !empty($case['published_at']) ? date('Y', strtotime((string) $case['published_at'])) : date('Y');
        $pdf->paragraph(($case['author_name'] ?? 'Autor') . ' (' . $year . '). ' . $case['title'] . '. VitroLab MX. ' . url('/casos/' . $case['slug']));
        $pdf->paragraph('Licencia: ' . $case['license']);
        $pdf->space(8);
        $pdf->small('Documento generado desde VitroLab MX. El contenido es educativo y no sustituye la asesoría técnica, sanitaria ni regulatoria. Verifica los protocolos con tu comité de bioseguridad.');
        $pdf->small('Generado el ' . date('d/m/Y H:i') . ' · ' . config('app.url'));

        return $pdf->output();
    }

    private static function section(SimplePdf $pdf, string $title, string $content): void
    {
        $content = trim($content);
        if ($content === '') {
            return;
        }
        $pdf->heading($title);
        $pdf->paragraph($content);
    }
}
