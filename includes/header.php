<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
start_app_session();

$pageTitle = isset($pageTitle) ? $pageTitle . ' | Mail Delivery Monitor' : 'Mail Delivery Monitor';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape_html($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<header class="border-bottom">
    <nav class="navbar navbar-expand navbar-light">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="dashboard.php">Mail Delivery Monitor</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="logout.php" method="post" class="ms-auto">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                    <button class="btn btn-outline-secondary btn-sm" type="submit">Logout</button>
                </form>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main class="container py-4">
