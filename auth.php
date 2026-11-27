<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

const LOGIN_MAX_ATTEMPTS = 3;
const LOGIN_LOCK_MINUTES = 5;

const SESSION_USER_ID  = 'manager_id';
const SESSION_USERNAME = 'manager_username';


function auth_db(): ?mysqli
{
    require __DIR__ . '/settings.php';

    $dbHost = $host ?? 'localhost';
    $dbUser = $user ?? $username ?? 'root';
    $dbPass = $pwd ?? $password ?? '';
    $dbName = $sql_db ?? $database ?? '';

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        error_log('Login database connection failed: ' . $conn->connect_error);
        return null;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

/** True when a manager is logged in. */
function is_logged_in(): bool
{
    return isset($_SESSION[SESSION_USER_ID]);
}

/** Send visitors who are not logged in to the login page. */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** A per-session token that the login form must send back (blocks cross-site form posts). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_valid(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check a username and password against the users table.
 *
 * Returns one of:
 *   'ok'      logged in
 *   'invalid' wrong username or password
 *   'locked'  too many failed attempts; try again later
 *   'error'   database problem
 */
function attempt_login(string $username, string $password): string
{
    $conn = auth_db();
    if ($conn === null) {
        return 'error';
    }

    // NOW() is used throughout so the lock time follows the database clock.
    $stmt = $conn->prepare(
        'SELECT user_id, username, password_hash, failed_attempts,
                (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
           FROM users
          WHERE username = ?'
    );
    if (!$stmt) {
        error_log('Login query failed: ' . $conn->error);
        $conn->close();
        return 'error';
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$account) {
        // Unknown username: still run a hash check so the response time
        // does not reveal which usernames exist.
        password_verify($password, '$2y$12$hYMI04iGQkJBzKD3vx.DaOvKqlXciuXiv/Bj2vrAF.RC4V3CFLWo.');
        $conn->close();
        return 'invalid';
    }

    if ((int)$account['is_locked'] === 1) {
        // Locked: do not even check the password.
        $conn->close();
        return 'locked';
    }

    $userId = (int)$account['user_id'];

    if (password_verify($password, $account['password_hash'])) {
        $stmt = $conn->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        // New session id after login stops session fixation attacks.
        session_regenerate_id(true);
        $_SESSION[SESSION_USER_ID] = $userId;
        $_SESSION[SESSION_USERNAME] = $account['username'];
        return 'ok';
    }

    $attempts = (int)$account['failed_attempts'] + 1;
    if ($attempts >= LOGIN_MAX_ATTEMPTS) {
        // Lock the account and start counting again once the lock ends.
        $stmt = $conn->prepare(
            'UPDATE users
                SET failed_attempts = 0,
                    locked_until = NOW() + INTERVAL ' . LOGIN_LOCK_MINUTES . ' MINUTE
              WHERE user_id = ?'
        );
        $stmt->bind_param('i', $userId);
        $result = 'locked';
    } else {
        $stmt = $conn->prepare('UPDATE users SET failed_attempts = ? WHERE user_id = ?');
        $stmt->bind_param('ii', $attempts, $userId);
        $result = 'invalid';
    }
    $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

/** log out and remove cookies*/
function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
