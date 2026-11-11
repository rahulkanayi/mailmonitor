<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');
start_app_session();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

$query = trim((string) ($_GET['q'] ?? ''));
if ($query === '') {
    echo json_encode([]);
    exit;
}

$prefix = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query) . '%';
$statement = database()->prepare(
    "SELECT DISTINCT server_name
     FROM mail_stats
     WHERE server_name LIKE :prefix ESCAPE '!'
     ORDER BY server_name ASC
     LIMIT 10"
);
$statement->execute(['prefix' => $prefix]);

echo json_encode($statement->fetchAll(PDO::FETCH_COLUMN), JSON_THROW_ON_ERROR);
