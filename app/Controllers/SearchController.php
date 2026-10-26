<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\ReportRepository;

final class SearchController extends BaseController
{
    public function index(Request $request): Response
    {
        $q = trim($request->string('q'));
        $category = $request->string('category');
        $results = [];
        $total = 0;

        if (mb_strlen($q) >= 2) {
            $filters = ['q' => $q, 'sort' => 'relevancia'];
            if ($category !== '') {
                $filters['category'] = $category;
            }
            $cases = (new CaseRepository())->search($filters, 1, 8);
            $reports = (new ReportRepository())->search($filters, 1, 8);
            $total = $cases['total'] + $reports['total'];

            foreach ($cases['items'] as $item) {
                $results[] = ['type' => 'caso', 'item' => $item];
            }
            foreach ($reports['items'] as $item) {
                $results[] = ['type' => 'reporte', 'item' => $item];
            }
        }

        $catalog = new CatalogRepository();
        return $this->view('search/index', [
            'title' => ($q !== '' ? 'Buscar: ' . $q : 'Buscar') . ' · VitroLab MX',
            'metaDescription' => 'Busca casos, reportes, especies y protocolos de cultivo in vitro en México.',
            'q' => $q,
            'category' => $category,
            'results' => $results,
            'total' => $total,
            'categories' => $catalog->categories(),
            'robots' => 'noindex,follow',
        ]);
    }
}
