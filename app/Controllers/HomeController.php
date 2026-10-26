<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\ReportRepository;

final class HomeController extends BaseController
{
    public function index(Request $request): Response
    {
        $catalog = new CatalogRepository();
        $cases = new CaseRepository();
        $reports = new ReportRepository();

        $species = $catalog->speciesList();
        usort($species, static fn (array $a, array $b): int => ((int) $b['cases_count'] + (int) $b['reports_count']) <=> ((int) $a['cases_count'] + (int) $a['reports_count']));

        return $this->view('home/index', [
            'title' => 'VitroLab MX · Casos y reportes de cultivo in vitro de material vegetal',
            'metaDescription' => 'Comunidad en español para documentar casos, protocolos y reportes de cultivo in vitro de plantas: micropropagación, organogénesis, conservación y aclimatación.',
            'stats' => $catalog->stats(),
            'featuredCases' => $cases->featured(3),
            'featuredReports' => $reports->featured(2),
            'latestCases' => $cases->latest(6),
            'categories' => $catalog->categories(),
            'species' => array_slice($species, 0, 8),
            'institutions' => $catalog->topInstitutions(6),
            'states' => $catalog->popularStates(6),
        ]);
    }
}
