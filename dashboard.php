<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_authentication();

$search = trim((string) ($_GET['search'] ?? ''));

$sql = <<<'SQL'
SELECT current.server_name, current.total, current.qp, current.dirdel,
       current.pending, current.deferred, current.bounced, current.recorded_at
FROM mail_stats AS current
WHERE NOT EXISTS (
    SELECT 1
    FROM mail_stats AS newer
    WHERE newer.server_name = current.server_name
      AND (
          newer.recorded_at > current.recorded_at
          OR (newer.recorded_at = current.recorded_at AND newer.id > current.id)
      )
)
AND current.server_name LIKE :server_name ESCAPE '!'
ORDER BY current.server_name ASC
SQL;

$searchPattern = '%' . str_replace(
    ['!', '%', '_'],
    ['!!', '!%', '!_'],
    $search
) . '%';
$statement = database()->prepare($sql);
$statement->execute(['server_name' => $searchPattern]);
$records = $statement->fetchAll();
$totalColumns = ['total', 'qp', 'dirdel', 'pending', 'deferred', 'bounced'];
$totals = array_fill_keys($totalColumns, 0);

foreach ($records as $record) {
    foreach ($totalColumns as $column) {
        $totals[$column] += (int) $record[$column];
    }
}

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Dashboard</h1>
        <p class="text-secondary mb-0">Latest delivery statistics for each server.</p>
    </div>
    <p class="mb-0"><?= count($records) ?> VPS<?= count($records) === 1 ? '' : 's' ?> displayed</p>
</div>

<form class="row g-2 mb-3" method="get" action="dashboard.php" role="search">
    <div class="col-12 col-sm-8 col-md-6 col-lg-4 server-search-container">
        <label class="visually-hidden" for="server-search">Search servers</label>
        <input
            class="form-control"
            id="server-search"
            name="search"
            type="search"
            role="combobox"
            aria-autocomplete="list"
            aria-expanded="false"
            aria-controls="server-suggestions"
            data-suggestions-url="server_suggestions.php"
            placeholder="Search by server name"
            value="<?= escape_html($search) ?>"
        >
        <div class="server-suggestions list-group" id="server-suggestions" role="listbox" hidden></div>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary" type="submit">Search</button>
    </div>
    <?php if ($search !== ''): ?>
        <div class="col-auto">
            <a class="btn btn-outline-secondary" href="dashboard.php">Clear</a>
        </div>
    <?php endif; ?>
</form>

<div class="table-responsive content-panel p-0">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr>
            <th scope="col">Server</th>
            <th scope="col">Total</th>
            <th scope="col">QP</th>
            <th scope="col">DIRDEL</th>
            <th scope="col">Pending</th>
            <th scope="col">Deferred</th>
            <th scope="col">Bounced</th>
            <th scope="col">Last Update</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($records === []): ?>
            <tr><td colspan="8" class="text-center py-4">No server records available.</td></tr>
        <?php else: ?>
            <?php foreach ($records as $record): ?>
                <tr>
                    <th scope="row">
                        <a href="server.php?name=<?= rawurlencode($record['server_name']) ?>">
                            <?= escape_html($record['server_name']) ?>
                        </a>
                    </th>
                    <td><?= number_format((int) $record['total']) ?></td>
                    <td><?= number_format((int) $record['qp']) ?></td>
                    <td><?= number_format((int) $record['dirdel']) ?></td>
                    <td><?= number_format((int) $record['pending']) ?></td>
                    <td><?= number_format((int) $record['deferred']) ?></td>
                    <td><?= number_format((int) $record['bounced']) ?></td>
                    <td><?= escape_html(date('H:i', strtotime($record['recorded_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        <?php if ($records !== []): ?>
            <tfoot>
            <tr class="fw-semibold">
                <th scope="row">Total</th>
                <?php foreach ($totalColumns as $column): ?>
                    <td><?= number_format($totals[$column]) ?></td>
                <?php endforeach; ?>
                <td>&mdash;</td>
            </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
