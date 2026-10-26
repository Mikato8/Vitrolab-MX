<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;

final class InstitutionController extends BaseController
{
    public function index(Request $request): Response
    {
        $catalog = new CatalogRepository();
        $institutions = $catalog->institutions();
        $grouped = $catalog->institutionsByState();

        return $this->view('institutions/index', [
            'title' => 'Instituciones · VitroLab MX',
            'metaDescription' => 'Directorio de universidades, centros de investigación y laboratorios que documentan cultivo in vitro en México.',
            'institutions' => $institutions,
            'grouped' => $grouped,
            'total' => count($institutions),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $catalog = new CatalogRepository();
        $institution = $catalog->institutionBySlug($slug);
        if ($institution === null) {
            $this->notFound('No encontramos esa institución.');
        }

        $members = (new UserRepository())->byInstitution((int) $institution['id']);
        $cases = (new CaseRepository())->search(['institution_id' => (int) $institution['id']], 1, 6);
        $reports = (new ReportRepository())->search(['institution_id' => (int) $institution['id']], 1, 6);

        return $this->view('institutions/show', [
            'title' => $institution['name'] . ' · VitroLab MX',
            'metaDescription' => 'Miembros, casos y reportes de ' . $institution['name'] . ' en VitroLab MX.',
            'institution' => $institution,
            'members' => $members,
            'cases' => $cases['items'],
            'reports' => $reports['items'],
            'casesTotal' => $cases['total'],
            'reportsTotal' => $reports['total'],
        ]);
    }
}
