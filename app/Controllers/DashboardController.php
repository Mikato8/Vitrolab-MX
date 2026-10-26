<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\InteractionRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;

final class DashboardController extends BaseController
{
    public function index(Request $request): Response
    {
        $user = Auth::requireLogin();
        $id = (int) $user['id'];
        $users = new UserRepository();
        $cases = new CaseRepository();
        $reports = new ReportRepository();
        $interactions = new InteractionRepository();

        $myCases = $cases->mine($id);
        $myReports = $reports->mine($id);

        return $this->view('dashboard/index', [
            'title' => 'Mi panel · VitroLab MX',
            'metaDescription' => 'Tus casos, reportes de laboratorio y actividad en VitroLab MX.',
            'user' => $user,
            'activity' => $users->activity($id),
            'myCases' => array_slice($myCases, 0, 4),
            'myReports' => array_slice($myReports, 0, 4),
            'bookmarks' => array_slice($interactions->bookmarks($id), 0, 6),
            'feedback' => $interactions->commentsOnAuthorContent($id, 6),
            'totals' => [
                'cases' => count($myCases),
                'reports' => count($myReports),
            ],
            'completeness' => $this->completeness($user),
            'robots' => 'noindex,nofollow',
        ]);
    }

    /** @param array<string, mixed> $user */
    private function completeness(array $user): int
    {
        $checks = [
            trim((string) ($user['name'] ?? '')) !== '',
            trim((string) ($user['career'] ?? '')) !== '',
            (int) ($user['institution_id'] ?? 0) > 0 || trim((string) ($user['institution_other'] ?? '')) !== '',
            trim((string) ($user['bio'] ?? '')) !== '',
            $user['status'] === 'verificado',
        ];
        return (int) round(count(array_filter($checks)) / count($checks) * 100);
    }
}
