<?php

declare(strict_types=1);

namespace App\Support;

final class ReportPdf
{
    /**
     * @param array<string, mixed> $report
     * @param array<int, array<string, mixed>> $treatments
     */
    public static function render(array $report, array $treatments = []): string
    {
        $pdf = new SimplePdf((string) $report['title']);
        $pdf->title((string) $report['title']);
        $pdf->label(report_type_label($report['report_type'] ?? null));
        $pdf->paragraph((string) $report['summary'], 12, 17);
        $pdf->small('Folio ' . $report['folio'] . ' · ' . format_date($report['published_at'] ?? null, 'd/m/Y') . ' · ' . ($report['author_name'] ?? ''));
        if (!empty($report['institution_name'])) {
            $pdf->small('Institución: ' . $report['institution_name']);
        }
        $pdf->space(6);

        $pdf->heading('Ficha del reporte');
        $pdf->paragraph('Especie: ' . ($report['scientific_name'] ?? $report['species_text'] ?? '—') . ' · Línea: ' . ($report['category_name'] ?? '—'));
        $pdf->paragraph('Estado: ' . ($report['state_name'] ?? 'No especificado') . ' · Periodo: ' . format_date($report['period_start'] ?? null, 'd/m/Y') . ' – ' . format_date($report['period_end'] ?? null, 'd/m/Y'));
        $pdf->paragraph('Unidad experimental: ' . (!empty($report['replicates']) ? 'n = ' . (int) $report['replicates'] : 'No indicada'));
        if (!empty($report['linked_case_title'])) {
            $pdf->paragraph('Caso vinculado: ' . $report['linked_case_title']);
        }

        self::section($pdf, 'Objetivo', (string) $report['objective']);
        self::section($pdf, 'Hipótesis', (string) $report['hypothesis']);
        self::section($pdf, 'Diseño experimental', (string) $report['design']);
        self::section($pdf, 'Descripción de tratamientos', (string) $report['treatments_text']);
        self::section($pdf, 'Variables medidas', (string) $report['variables']);

        if ($treatments !== []) {
            $pdf->heading('Tabla de tratamientos');
            $rows = [];
            foreach ($treatments as $treatment) {
                $rows[] = [
                    (string) $treatment['label'],
                    $treatment['n'] !== null ? (string) (int) $treatment['n'] : '—',
                    $treatment['response'] !== null ? rtrim(rtrim(number_format((float) $treatment['response'], 2, '.', ','), '0'), '.') : '—',
                    (string) $treatment['unit'],
                    (string) $treatment['notes'],
                ];
            }
            $pdf->table(['Tratamiento', 'n', 'Respuesta', 'Unidad', 'Notas'], $rows, [2.2, .5, .8, 1.1, 1.4]);
        }

        self::section($pdf, 'Resultados', (string) $report['results']);
        self::section($pdf, 'Análisis', (string) $report['analysis']);
        self::section($pdf, 'Conclusiones', (string) $report['conclusions']);
        self::section($pdf, 'Recomendaciones', (string) $report['recommendations']);
        self::section($pdf, 'Limitaciones', (string) $report['limitations']);
        self::section($pdf, 'Referencias', (string) $report['references_text']);

        $pdf->heading('Cómo citar este reporte');
        $year = !empty($report['published_at']) ? date('Y', strtotime((string) $report['published_at'])) : date('Y');
        $pdf->paragraph(($report['author_name'] ?? 'Autor') . ' (' . $year . '). ' . $report['title'] . '. VitroLab MX. ' . url('/reportes/' . $report['slug']));
        $pdf->paragraph('Licencia: ' . $report['license']);
        $pdf->space(8);
        $pdf->small('Documento generado desde VitroLab MX. El contenido es educativo y no sustituye la asesoría técnica, sanitaria ni regulatoria. Los datos son responsabilidad del autor.');
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
