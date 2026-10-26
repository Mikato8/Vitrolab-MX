<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Str;

$options = getopt('', ['demo', 'force', 'no-demo']);
$withDemo = !isset($options['no-demo']);
$force = isset($options['force']);

$catalog = require BASE_PATH . '/database/seeds/catalog.php';

echo "→ Sembrando catálogos…\n";
foreach ($catalog['states'] as [$code, $name, $region]) {
    Database::execute(
        'insert into states (code, name, region) values ($1, $2, $3)
         on conflict (code) do update set name = excluded.name, region = excluded.region',
        [$code, $name, $region]
    );
}

foreach ($catalog['categories'] as $index => [$code, $name, $emoji, $description]) {
    Database::execute(
        'insert into categories (code, name, emoji, description, sort_order) values ($1, $2, $3, $4, $5)
         on conflict (code) do update set name = excluded.name, emoji = excluded.emoji,
             description = excluded.description, sort_order = excluded.sort_order',
        [$code, $name, $emoji, $description, ($index + 1) * 10]
    );
}

$speciesBySlug = [];
foreach ($catalog['species'] as [$slug, $scientific, $common, $family, $group, $description]) {
    Database::execute(
        'insert into species (slug, scientific_name, common_name, family, group_name, description, is_demo)
         values ($1, $2, $3, $4, $5, $6, false)
         on conflict (slug) do update set scientific_name = excluded.scientific_name,
             common_name = excluded.common_name, family = excluded.family,
             group_name = excluded.group_name, description = excluded.description',
        [$slug, $scientific, $common, $family, $group, $description]
    );
    $speciesBySlug[$slug] = (int) Database::value('select id from species where slug = $1', [$slug]);
}

foreach ($catalog['institutions'] as [$name, $acronym, $state, $type, $website, $domains, $description]) {
    $slug = Str::slug($name, 80);
    $arrayLiteral = '{' . implode(',', array_map(
        static fn (string $item): string => '"' . str_replace('"', '\\"', $item) . '"',
        $domains
    )) . '}';
    Database::execute(
        'insert into institutions (name, acronym, slug, state_code, type, website, email_domains, description, is_demo)
         values ($1, $2, $3, $4, $5, $6, $7::text[], $8, false)
         on conflict (slug) do update set name = excluded.name, acronym = excluded.acronym,
             state_code = excluded.state_code, type = excluded.type, website = excluded.website,
             email_domains = excluded.email_domains, description = excluded.description',
        [$name, $acronym, $slug, $state, $type, $website, $arrayLiteral, $description]
    );
}

echo '  ✓ ' . count($catalog['states']) . ' estados, ' . count($catalog['categories']) . ' categorías, '
    . count($catalog['species']) . ' especies, ' . count($catalog['institutions']) . " instituciones\n";

if (!$withDemo) {
    echo "Listo. No se sembró contenido demostrativo.\n";
    exit(0);
}

$existing = (int) Database::value('select count(*) from cases where is_demo = true');
if ($existing > 0 && !$force) {
    echo "· El contenido demostrativo ya existe ({$existing} casos). Usa --force para recrearlo.\n";
    exit(0);
}

if ($force) {
    echo "→ Eliminando contenido demostrativo anterior…\n";
    $demoCaseIds = 'select id from cases where is_demo = true';
    $demoReportIds = 'select id from reports where is_demo = true';
    Database::execute("delete from comments where (target_type = 'caso' and target_id in ({$demoCaseIds})) or (target_type = 'reporte' and target_id in ({$demoReportIds}))");
    Database::execute("delete from reactions where (target_type = 'caso' and target_id in ({$demoCaseIds})) or (target_type = 'reporte' and target_id in ({$demoReportIds}))");
    Database::execute("delete from bookmarks where (target_type = 'caso' and target_id in ({$demoCaseIds})) or (target_type = 'reporte' and target_id in ({$demoReportIds}))");
    Database::execute('delete from reports where is_demo = true');
    Database::execute('delete from cases where is_demo = true');
}

$demo = require BASE_PATH . '/database/seeds/demo.php';

$institutionByDomain = [];
foreach (Database::select('select id, email_domains from institutions') as $row) {
    foreach (pg_array($row['email_domains']) as $domain) {
        $institutionByDomain[mb_strtolower($domain)] = (int) $row['id'];
    }
}

$categoryByCode = [];
foreach (Database::select('select id, code from categories') as $row) {
    $categoryByCode[$row['code']] = (int) $row['id'];
}

$userIds = [];
echo "→ Creando cuentas demostrativas…\n";
foreach ($demo['users'] as $user) {
    $email = Str::normalizeEmail($user['email']);
    $institutionId = $institutionByDomain[$user['institution']] ?? null;
    $existingId = Database::value('select id from users where email = $1', [$email]);
    if ($existingId !== null) {
        $userIds[$user['key']] = (int) $existingId;
        continue;
    }
    $row = Database::insert(
        'insert into users (name, email, password_hash, role, status, institution_id, career, semester, state_code,
                            bio, verification_method, email_verified, terms_accepted_at, created_at)
         values ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, true, now(), now() - interval \'140 days\')
         returning id',
        [
            $user['name'],
            $email,
            password_hash($demo['password'], PASSWORD_DEFAULT),
            $user['role'],
            'verificado',
            $institutionId,
            $user['career'],
            $user['semester'],
            $user['state'],
            $user['bio'],
            'dominio_institucional',
        ]
    );
    $userIds[$user['key']] = (int) ($row['id'] ?? 0);
}

$admin = (int) Database::value("select id from users where role in ('admin', 'editor') order by role desc, id limit 1");
$reviewer = $userIds['ricardo'] ?? $admin;

$caseIds = [];
echo "→ Publicando casos demostrativos…\n";
foreach ($demo['cases'] as $case) {
    $slug = $case['slug'];
    $existingId = Database::value('select id from cases where slug = $1', [$slug]);
    if ($existingId !== null) {
        $caseIds[$slug] = (int) $existingId;
        continue;
    }
    $folio = 'VIT-CAS-' . date('Y') . '-' . str_pad((string) (count($caseIds) + 1), 3, '0', STR_PAD_LEFT);
    $publishedAt = date('c', strtotime('-' . (int) $case['published_days_ago'] . ' days'));
    $row = Database::insert(
        'insert into cases (slug, folio, author_id, title, summary, category_id, species_id, species_text,
            state_code, municipality, year, objective, context, explant, disinfection, medium, pgr, conditions,
            protocol, results, challenges, lessons, contamination_pct, survival_pct, multiplication_rate,
            rooting_pct, acclimatization_pct, duration_weeks, references_text, license, status, featured, is_demo,
            published_at, created_at)
         values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,
                 $26,$27,$28,$29,$30,\'publicado\',$31,true,$32,$32)
         returning id',
        [
            $slug,
            $folio,
            $userIds[$case['author']],
            $case['title'],
            $case['summary'],
            $categoryByCode[$case['category']] ?? null,
            $speciesBySlug[$case['species']] ?? null,
            (string) Database::value('select scientific_name from species where slug = $1', [$case['species']]),
            $case['state'],
            $case['municipality'],
            (int) $case['year'],
            $case['objective'],
            $case['context'],
            $case['explant'],
            $case['disinfection'],
            $case['medium'],
            $case['pgr'],
            $case['conditions'],
            $case['protocol'],
            $case['results'],
            $case['challenges'],
            $case['lessons'],
            $case['contamination_pct'],
            $case['survival_pct'],
            $case['multiplication_rate'],
            $case['rooting_pct'],
            $case['acclimatization_pct'],
            $case['duration_weeks'],
            $case['references_text'],
            $case['license'],
            !empty($case['featured']),
            $publishedAt,
        ]
    );
    $caseId = (int) ($row['id'] ?? 0);
    $caseIds[$slug] = $caseId;

    $imagePath = 'demo/' . $case['image'];
    if (is_file(BASE_PATH . '/public/uploads/casos/' . $imagePath)) {
        Database::execute(
            'insert into case_images (case_id, path, caption, alt_text, width, height, sort_order)
             values ($1, $2, $3, $4, 1600, 1000, 0)',
            [$caseId, $imagePath, $case['image_caption'], $case['image_caption']]
        );
    }

    if ($reviewer > 0) {
        Database::execute(
            "insert into reviews (target_type, target_id, reviewer_id, decision, notes, created_at)
             values ('caso', $1, $2, 'publicar', $3, now() - interval '2 days')",
            [$caseId, $reviewer, 'Caso con protocolo completo, métricas explícitas y aprendizajes verificables. Aprobado para publicación demostrativa.']
        );
    }
}

$reportIds = [];
echo "→ Publicando reportes de laboratorio…\n";
foreach ($demo['reports'] as $report) {
    $slug = $report['slug'];
    $existingId = Database::value('select id from reports where slug = $1', [$slug]);
    if ($existingId !== null) {
        $reportIds[$slug] = (int) $existingId;
        continue;
    }
    $folio = 'VIT-REP-' . date('Y') . '-' . str_pad((string) (count($reportIds) + 1), 3, '0', STR_PAD_LEFT);
    $publishedAt = date('c', strtotime('-' . (int) $report['published_days_ago'] . ' days'));
    $row = Database::insert(
        'insert into reports (slug, folio, author_id, case_id, title, summary, report_type, category_id,
            species_id, species_text, state_code, period_start, period_end, objective, hypothesis, design,
            treatments_text, replicates, variables, results, analysis, conclusions, recommendations, limitations,
            references_text, license, status, featured, is_demo, published_at, created_at)
         values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,
                 $25,$26,\'publicado\',$27,true,$28,$28)
         returning id',
        [
            $slug,
            $folio,
            $userIds[$report['author']],
            $report['case'] !== null ? ($caseIds[$report['case']] ?? null) : null,
            $report['title'],
            $report['summary'],
            $report['report_type'],
            $categoryByCode[$report['category']] ?? null,
            $speciesBySlug[$report['species']] ?? null,
            (string) Database::value('select scientific_name from species where slug = $1', [$report['species']]),
            $report['state'],
            $report['period_start'],
            $report['period_end'],
            $report['objective'],
            $report['hypothesis'],
            $report['design'],
            $report['treatments_text'],
            (int) $report['replicates'],
            $report['variables'],
            $report['results'],
            $report['analysis'],
            $report['conclusions'],
            $report['recommendations'],
            $report['limitations'],
            $report['references_text'],
            $report['license'],
            !empty($report['featured']),
            $publishedAt,
        ]
    );
    $reportId = (int) ($row['id'] ?? 0);
    $reportIds[$slug] = $reportId;

    foreach ($report['treatments'] as $index => $treatment) {
        Database::execute(
            'insert into report_treatments (report_id, label, n, response, unit, notes, sort_order)
             values ($1, $2, $3, $4, $5, $6, $7)',
            [
                $reportId,
                $treatment['label'],
                $treatment['n'],
                $treatment['response'],
                $treatment['unit'],
                $treatment['notes'],
                $index,
            ]
        );
    }

    $figure = 'demo/figura-' . $slug . '.png';
    $dataFile = 'demo/datos-' . $slug . '.csv';
    if (is_file(BASE_PATH . '/public/uploads/archivos/' . $figure)) {
        Database::execute(
            "insert into report_files (report_id, path, original_name, mime, size_bytes, kind)
             values ($1, $2, $3, 'image/png', $4, 'figura')",
            [$reportId, $figure, 'figura-' . $slug . '.png', (int) filesize(BASE_PATH . '/public/uploads/archivos/' . $figure)]
        );
    }
    if (is_file(BASE_PATH . '/public/uploads/archivos/' . $dataFile)) {
        Database::execute(
            "insert into report_files (report_id, path, original_name, mime, size_bytes, kind)
             values ($1, $2, $3, 'text/csv', $4, 'datos')",
            [$reportId, $dataFile, 'datos-' . $slug . '.csv', (int) filesize(BASE_PATH . '/public/uploads/archivos/' . $dataFile)]
        );
    }

    if ($reviewer > 0) {
        Database::execute(
            "insert into reviews (target_type, target_id, reviewer_id, decision, notes, created_at)
             values ('reporte', $1, $2, 'publicar', $3, now() - interval '1 day')",
            [$reportId, $reviewer, 'Diseño, variables y conclusiones consistentes con los datos presentados. Se recomienda repetir el ensayo en otra temporada.']
        );
    }
}

echo "→ Sembrando comentarios, reacciones y favoritos…\n";
$targetIds = ['caso' => $caseIds, 'reporte' => $reportIds];

foreach ($demo['comments'] as $comment) {
    $targetId = $targetIds[$comment['type']][$comment['slug']] ?? null;
    $userId = $userIds[$comment['author']] ?? null;
    if ($targetId === null || $userId === null) {
        continue;
    }
    $exists = Database::value(
        'select 1 from comments where target_type = $1 and target_id = $2 and user_id = $3 limit 1',
        [$comment['type'], $targetId, $userId]
    );
    if ($exists !== null) {
        continue;
    }
    Database::execute(
        'insert into comments (target_type, target_id, user_id, body, created_at) values ($1, $2, $3, $4, now() - interval \'2 days\')',
        [$comment['type'], $targetId, $userId, $comment['body']]
    );
}

$reactionAuthors = ['ana', 'carlos', 'lucia', 'diego', 'mariana', 'emilio', 'ximena'];
$reactionTargets = [
    ['caso', $caseIds['agave-azul-micropropagacion-jalisco'] ?? null],
    ['caso', $caseIds['vainilla-planifolia-segmentos-nodales-veracruz'] ?? null],
    ['caso', $caseIds['stevia-aclimatacion-escalonada-puebla'] ?? null],
    ['reporte', $reportIds['bap-iba-proliferacion-agave'] ?? null],
    ['reporte', $reportIds['aclimatacion-stevia-sustratos'] ?? null],
    ['reporte', $reportIds['conservacion-laelia-crecimiento-minimo'] ?? null],
];
foreach ($reactionTargets as $index => [$type, $targetId]) {
    if (!$targetId) {
        continue;
    }
    foreach (array_slice($reactionAuthors, 0, 3 + ($index % 3)) as $authorKey) {
        $userId = $userIds[$authorKey] ?? null;
        if (!$userId) {
            continue;
        }
        Database::execute(
            'insert into reactions (target_type, target_id, user_id) values ($1, $2, $3) on conflict do nothing',
            [$type, $targetId, $userId]
        );
    }
}

foreach ([
    ['caso', $caseIds['agave-azul-micropropagacion-jalisco'] ?? null, 'ana'],
    ['caso', $caseIds['laelia-anceps-conservacion-crecimiento-minimo'] ?? null, 'carlos'],
    ['reporte', $reportIds['bap-iba-proliferacion-agave'] ?? null, 'lucia'],
] as [$type, $targetId, $authorKey]) {
    $userId = $userIds[$authorKey] ?? null;
    if ($targetId && $userId) {
        Database::execute(
            'insert into bookmarks (target_type, target_id, user_id) values ($1, $2, $3) on conflict do nothing',
            [$type, $targetId, $userId]
        );
    }
}

echo "  ✓ {$demo['password']} es la contraseña de las cuentas demostrativas.\n";
echo 'Listo. Cuentas: ' . count($userIds) . ', casos: ' . count($caseIds)
    . ', reportes: ' . count($reportIds) . ".\n";
