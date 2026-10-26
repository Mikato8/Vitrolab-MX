<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\ReportRepository;

final class SpeciesController extends BaseController
{
    public function index(Request $request): Response
    {
        $catalog = new CatalogRepository();
        $q = trim($request->string('q'));
        $species = $catalog->speciesList();
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $species = array_values(array_filter($species, static fn (array $item): bool => str_contains(mb_strtolower((string) $item['scientific_name'] . ' ' . $item['common_name'] . ' ' . $item['family']), $needle)));
        }
        $grouped = [];
        foreach ($species as $item) { $grouped[$item['group_name']][] = $item; }
        ksort($grouped);

        return $this->view('species/index', [
            'title' => 'Especies · VitroLab MX',
            'metaDescription' => 'Directorio de especies documentadas en casos y reportes de cultivo in vitro: agave, vainilla, plátano, café, cactáceas y más.',
            'species' => $species,
            'grouped' => $grouped,
            'total' => count($species),
            'q' => $q,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $catalog = new CatalogRepository();
        $species = $catalog->speciesBySlug($slug);
        if ($species === null) {
            $this->notFound('No encontramos esa especie.');
        }

        $cases = (new CaseRepository())->search(['species' => $slug], 1, 6);
        $reports = (new ReportRepository())->search(['species' => $slug], 1, 6);

        return $this->view('species/show', [
            'title' => $species['scientific_name'] . ' · VitroLab MX',
            'metaDescription' => 'Casos y reportes de cultivo in vitro de ' . $species['scientific_name'] . ' (' . $species['common_name'] . ').',
            'species' => $species,
            'cases' => $cases['items'],
            'reports' => $reports['items'],
            'casesTotal' => $cases['total'],
            'reportsTotal' => $reports['total'],
        ]);
    }
}
