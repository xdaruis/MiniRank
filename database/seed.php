<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Models\Database;

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

$insertProject = $db->prepare('INSERT INTO projects (user_id, domain) VALUES (:user_id, :domain)');
$insertProject->execute(['user_id' => $userId, 'domain' => 'pizzeria.example']);
$projectId = (int) $db->lastInsertId();

$insertKeyword = $db->prepare('INSERT INTO keywords (project_id, phrase) VALUES (:project_id, :phrase)');
$insertPosition = $db->prepare(
    'INSERT INTO positions (keyword_id, position, captured_at) VALUES (:keyword_id, :position, :captured_at)'
);

$keywordsConfig = [
    ['phrase' => 'best pizza berlin',                'drift' => 0,  'amp' => 2, 'start' => 40],
    ['phrase' => 'pizza delivery berlin mitte',      'drift' => -1, 'amp' => 2, 'start' => 12],
    ['phrase' => 'wood fired pizza neukölln',        'drift' => -2, 'amp' => 2, 'start' => 6],
    ['phrase' => 'italian restaurant open late berlin', 'drift' => 0, 'amp' => 4, 'start' => 25],
    ['phrase' => 'neapolitan pizza near me',         'drift' => 2,  'amp' => 3, 'start' => 15],
];

$days = 30;
$today = date('Y-m-d');
$countedPositions = 0;

foreach ($keywordsConfig as $config) {
    $insertKeyword->execute(['project_id' => $projectId, 'phrase' => $config['phrase']]);
    $keywordId = (int) $db->lastInsertId();

    $position = $config['start'];
    for ($i = 0; $i < $days; $i++) {
        $date = date('Y-m-d', strtotime($today . " - " . ($days - 1 - $i) . " days"));
        $position = min(100, max(1, $position + $config['drift'] + random_int(-$config['amp'], $config['amp'])));
        $insertPosition->execute([
            'keyword_id'   => $keywordId,
            'position'     => $position,
            'captured_at'  => $date,
        ]);
        $countedPositions++;
    }
}

$db->commit();

$keywordCount = (int) $db->query('SELECT COUNT(*) FROM keywords')->fetchColumn();

echo "Seed complete: {$keywordCount} keywords, {$countedPositions} positions over {$days} days ending {$today}.\n";
echo "Demo login: {$demoUser} / {$demoPassword} (project: pizzeria.example)\n";