<?php
require_once __DIR__ . '/auth.php';

// Already logged in: go straight to the manager page.
if (is_logged_in()) {
    header('Location: manage.php');
    exit;
}

$message = '';
$messageClass = 'login-error';
$usernameValue = '';

if (isset($_GET['logged_out'])) {
    $message = 'You have been logged out.';
    $messageClass = 'login-notice';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = trim(stripslashes((string)($_POST['username'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $token = (string)($_POST['csrf_token'] ?? '');
    $usernameValue = $username;
    $messageClass = 'login-error';

    if (!csrf_valid($token)) {
        $message = 'Your session expired. Please try again.';
    } elseif ($username === '' || $password === '') {
        $message = 'Please enter your username and password.';
    } else {
        switch (attempt_login($username, $password)) {
            case 'ok':
                header('Location: manage.php');
                exit;
            case 'locked':
                $message = 'Too many failed attempts. Please wait ' . LOGIN_LOCK_MINUTES . ' minutes and try again.';
                break;
            case 'error':
                $message = 'Login is unavailable right now. Please try again later.';
                break;
            default:
                // One general message, so it does not reveal which part was wrong.
                $message = 'Incorrect username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Manager login for the Edbridge recruitment system.">
  <meta name="author" content="Tran Trung Tin | W3bb">
  <meta name="robots" content="noindex">
  <title>Manager Login | Edbridge</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&amp;family=Fraunces:opsz,wght@9..144,600&amp;display=swap">
  <link rel="stylesheet" href="styles/styles.css">
  <!-- Login-only styles; the form layout reuses the apply page styles. -->
  <style>
    .login-page > main { max-width: 480px; }
    .apply-page input[type="password"] {
      width: 100%;
      min-height: 44px;
      padding: 0.7rem 0.85rem;
      border: 1px solid var(--line-strong);
      border-radius: 8px;
      background: var(--paper-raised);
      color: var(--ink);
      font-family: var(--font-sans);
      font-size: 1rem;
    }
    .login-error, .login-notice {
      margin: 0 0 1.25rem;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid var(--line-strong);
      font-weight: 700;
    }
    .login-error { border-left: 5px solid #b3261e; }
    .login-notice { border-left: 5px solid var(--brand); }
  </style>
</head>

<body class="apply-page login-page">
  <!-- Replace this header with the shared header.inc once it is merged. -->
  <header>
    <div class="container">
      <a href="index.php" class="Edbridge">
        <span class="brand-logo"><img src="img/logo.webp" alt="" width="1024" height="1024"></span>
        <span>Edbridge</span>
      </a>
      <label class="theme-switch">
        <input type="checkbox" id="theme-toggle">
        <span class="slider"></span>
          Light Mode
      </label>
      <nav aria-label="Main">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="jobs.php">Jobs</a></li>
          <li><a href="apply.php">Apply</a></li>
          <li><a href="about.php">About us</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <h1>Manager login</h1>
    <p>Sign in to view and manage job applications.</p>

    <?php if ($message !== ''): ?>
      <p class="<?php echo $messageClass; ?>" role="<?php echo $messageClass === 'login-error' ? 'alert' : 'status'; ?>">
        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
      </p>
    <?php endif; ?>

    <form action="login.php" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

      <div class="form-row">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" autocomplete="username"
               value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES, 'UTF-8'); ?>">
      </div>

      <div class="form-row">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" autocomplete="current-password">
      </div>

      <div class="form-actions">
        <button type="submit">Log in</button>
      </div>
    </form>
  </main>
</body>
</html>
