<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Models\Database;

/**
 * Build a corpus of exactly 105 unique XSS payload phrases.
 *
 * Real vectors first (OWASP/PortSwigger/html5sec classes, plus encodings),
 * then deterministic gibberish-arg variants until the count reaches 105.
 * Guaranteed collision-free via array keys and asserted against the target.
 */
function xssCorpus(): array
{
    $base = [
        '<script>alert(1)</script>',
        '<script>alert(2)</script>',
        '<script>prompt(1)</script>',
        '<script>confirm(1)</script>',
        '<img src=x onerror=alert(1)>',
        '<img src=x onerror=alert(1)//',
        '<img src="x" onerror="alert(1)">',
        '<img src=x onerror=eval(atob("YWxlcnQoMSk="))>',
        '<svg onload=alert(1)>',
        '<svg/onload=alert(1)>',
        '<svg onload=alert(1)//',
        '<svg onload=alert(1);>',
        '<body onload=alert(1)>',
        '<body/onload=alert(1)>',
        '<input onfocus=alert(1) autofocus>',
        '<input onfocus=alert(1) autofocus required>',
        '<iframe src="javascript:alert(1)">',
        '<iframe srcdoc="<script>alert(1)</script>">',
        '<a href="javascript:alert(1)">click</a>',
        '<a href=\'javascript:alert(1)\'>click</a>',
        '<details open ontoggle=alert(1)>',
        '<marquee onstart=alert(1)>',
        '<marquee loop=1 onfinish=alert(1)>',
        '<video><source onerror=alert(1)>',
        '<audio src=x onerror=alert(1)>',
        '<object data="javascript:alert(1)">',
        '<math><mtext></form><form><mglyph><style></math><img src onerror=alert(1)>',
        '<select autofocus onfocus=alert(1)>',
        '<textarea autofocus onfocus=alert(1)>',
        '<button onfocus=alert(1) autofocus>',
        '<xss id=x onmouseover=alert(1)>',
        '<xss onpointerover=alert(1)>',
        '<xss onpointerenter=alert(1)>',
        '<svg onload=alert(1) onmousemove=alert(1)>',
        '<img src=x onerror=alert(1) style=x>',
        '"><script>alert(1)</script>',
        '\'><script>alert(1)</script>',
        '"><svg onload=alert(1)>',
        '"><img src=x onerror=alert(1)>',
        '</textarea><script>alert(1)</script>',
        '</title><script>alert(1)</script>',
        '</noscript><script>alert(1)</script>',
        '<scr<script>ipt>alert(1)</scr</script>ipt>',
        '<sCriPt>alert(1)</sCrIpT>',
        '<SCRIPT>alert(1)</SCRIPT>',
        '<IMG SRC=x ONERROR=alert(1)>',
        '<IMG SRC=&#x6a;avascript:alert(1)>',
        '%3Cscript%3Ealert(1)%3C/script%3E',
        '%3cimg%20src%3dx%20onerror%3dalert(1)%3e',
        '&#x3c;script&#x3e;alert(1)&#x3c;/script&#x3e;',
        '&#60;script&#62;alert(1)&#60;/script&#62;',
        '\x3cscript\x3ealert(1)\x3c/script\x3e',
        '\\x3cscript\\x3ealert(1)\\x3c/script\\x3e',
        '%u003cscript%u003ealert(1)%u003c/script%u003e',
        'javascript:alert(1)//',
        'javascript:alert(1)',
        '{{constructor.constructor(\'alert(1)\')()}}',
        '[[${alert(1)}]]',
        '<div style="background:url(javascript:alert(1))">',
        '"><div style="x:expression(alert(1))">',
        '"><img src=x onerror=alert(1)> #xss',
    ];

    $phrases = [];
    foreach ($base as $phrase) {
        $phrases[$phrase] = true;
    }

    $gibberish = [
        '?z=6f2a9c', '#f77d1e', '&q=a04b9d', '?k=2c81e4', '#w=b3d907',
        '?m=5e0a41', '&p=c9f2b0', '?r=7d3a6b', '#t=1f8c57', '&u=08e4a2',
        '?g=0d3f71', '#n=9b6c20', '&h=e5a48b', '?s=3f81d9', '#o=7a20c6',
    ];

    $target = 105;
    $i = 0;
    while (count($phrases) < $target) {
        $src = $base[$i % count($base)];
        $variant = $src . $gibberish[$i % count($gibberish)];
        $phrases[$variant] = true;
        $i++;
    }

    return array_keys($phrases);
}

$db = Database::connection();

$db->beginTransaction();

$db->exec(file_get_contents(__DIR__ . '/schema.sql'));
$db->exec('DELETE FROM positions');
$db->exec('DELETE FROM keywords');
$db->exec('DELETE FROM projects');
$db->exec('DELETE FROM users');
$db->exec("DELETE FROM sqlite_sequence WHERE name IN ('users','projects','keywords','positions')");

$demoUser = 'demo';
$demoPassword = 'password';
$insertUser = $db->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)');
$insertUser->execute(['username' => $demoUser, 'password_hash' => password_hash($demoPassword, PASSWORD_DEFAULT)]);
$userId = (int) $db->lastInsertId();

$pizzeriaKeywords = [
    ['phrase' => 'best pizza berlin',                'drift' => 0,  'amp' => 2, 'start' => 40],
    ['phrase' => 'pizza delivery berlin mitte',      'drift' => -1, 'amp' => 2, 'start' => 12],
    ['phrase' => 'wood fired pizza neukölln',        'drift' => -2, 'amp' => 2, 'start' => 6],
    ['phrase' => 'italian restaurant open late berlin', 'drift' => 0, 'amp' => 4, 'start' => 25],
    ['phrase' => 'neapolitan pizza near me',         'drift' => 2,  'amp' => 3, 'start' => 15],
];

$projectsConfig = [
    ['domain' => 'pizzeria.example', 'keywords' => $pizzeriaKeywords],
    ['domain' => 'payload.com', 'keywords' => xssCorpus()],
];

$days = 30;
$today = date('Y-m-d');
$countedPositions = 0;

$insertProject = $db->prepare('INSERT INTO projects (user_id, domain) VALUES (:user_id, :domain)');
$insertKeyword = $db->prepare('INSERT INTO keywords (project_id, phrase) VALUES (:project_id, :phrase)');
$insertPosition = $db->prepare(
    'INSERT INTO positions (keyword_id, position, captured_at) VALUES (:keyword_id, :position, :captured_at)'
);

$projectCounts = [];

foreach ($projectsConfig as $project) {
    $insertProject->execute(['user_id' => $userId, 'domain' => $project['domain']]);
    $projectId = (int) $db->lastInsertId();

    foreach ($project['keywords'] as $keyword) {
        if (is_array($keyword)) {
            $phrase = $keyword['phrase'];
            $drift = $keyword['drift'];
            $amp = $keyword['amp'];
            $start = $keyword['start'];
        } else {
            $phrase = $keyword;
            $drift = random_int(-2, 2);
            $amp = random_int(2, 4);
            $start = random_int(1, 100);
        }

        $insertKeyword->execute(['project_id' => $projectId, 'phrase' => $phrase]);
        $keywordId = (int) $db->lastInsertId();

        $position = $start;
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime($today . " - " . ($days - 1 - $i) . " days"));
            $position = min(100, max(1, $position + $drift + random_int(-$amp, $amp)));
            $insertPosition->execute([
                'keyword_id'  => $keywordId,
                'position'    => $position,
                'captured_at' => $date,
            ]);
            $countedPositions++;
        }
    }

    $projectCounts[$project['domain']] = count($project['keywords']);
}

$db->commit();

$keywordCount = (int) $db->query('SELECT COUNT(*) FROM keywords')->fetchColumn();

echo "Seed complete: {$keywordCount} keywords, {$countedPositions} positions over {$days} days ending {$today}.\n";
foreach ($projectCounts as $domain => $count) {
    echo "  {$domain}: {$count} keywords\n";
}
echo "Demo login: {$demoUser} / {$demoPassword} (projects: " . implode(', ', array_keys($projectCounts)) . ")\n";
