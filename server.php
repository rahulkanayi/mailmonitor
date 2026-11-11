<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_authentication();

$serverName = trim((string) ($_GET['name'] ?? ''));
if ($serverName === '') {
    http_response_code(400);
    $pageTitle = 'Invalid server';
    require __DIR__ . '/includes/header.php';
    echo '<h1 class="h3">Server name is required.</h1>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$statement = database()->prepare(
    'SELECT server_name, total, qp, dirdel, pending, deferred, bounced, recorded_at
     FROM mail_stats
     WHERE server_name = :server_name
     ORDER BY recorded_at DESC, id DESC
     LIMIT 24'
);
$statement->execute(['server_name' => $serverName]);
$records = $statement->fetchAll();

$pageTitle = 'Server ' . $serverName;
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Server: <?= escape_html($serverName) ?></h1>
    <a class="btn btn-outline-secondary" href="dashboard.php">Back to Dashboard</a>
</div>

<div class="table-responsive content-panel p-0">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr>
            <th scope="col">Time</th>
            <th scope="col">Total</th>
            <th scope="col">QP</th>
            <th scope="col">DIRDEL</th>
            <th scope="col">Pending</th>
            <th scope="col">Deferred</th>
            <th scope="col">Bounced</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($records === []): ?>
            <tr><td colspan="7" class="text-center py-4">No records found for this server.</td></tr>
        <?php else: ?>
            <?php foreach ($records as $record): ?>
                <tr>
                    <th scope="row"><?= escape_html(date('H:i', strtotime($record['recorded_at']))) ?></th>
                    <td><?= number_format((int) $record['total']) ?></td>
                    <td><?= number_format((int) $record['qp']) ?></td>
                    <td><?= number_format((int) $record['dirdel']) ?></td>
                    <td><?= number_format((int) $record['pending']) ?></td>
                    <td><?= number_format((int) $record['deferred']) ?></td>
                    <td><?= number_format((int) $record['bounced']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
