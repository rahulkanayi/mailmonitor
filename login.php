<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

start_app_session();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } elseif ($username === '' || $password === '') {
        $error = 'Enter both your username and password.';
    } else {
        $statement = database()->prepare(
            'SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            unset($_SESSION['csrf_token']);

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Invalid username or password.';
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <section class="content-panel">
            <h1 class="h3 mb-4">Login</h1>
            <?php if ($error !== ''): ?>
                <p class="form-error" role="status"><?= escape_html($error) ?></p>
            <?php endif; ?>
            <form method="post" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input
                        class="form-control"
                        id="username"
                        name="username"
                        type="text"
                        autocomplete="username"
                        maxlength="191"
                        required
                        value="<?= escape_html($username) ?>"
                    >
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <input
                        class="form-control"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                </div>
                <button class="btn btn-primary w-100" type="submit">Login</button>
            </form>
        </section>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
