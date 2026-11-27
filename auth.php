<?php
require_once __DIR__ . '/functions.php';

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
    $conn = dbConnect();
    if ($conn === null) {
        return 'error';
    }

    // One transaction per login. SELECT ... FOR UPDATE locks this user's row,
    // so if several logins for the same user arrive at once, each one waits
    // until the previous one has finished updating the counter. Without this,
    // they could all pass the "is it locked?" check before any of them locks it.
    $conn->begin_transaction();
    $outcome = check_credentials($conn, $username, $password);
    if ($outcome['result'] === 'error') {
        $conn->rollback();
    } elseif (!$conn->commit()) {
        error_log('Login transaction could not be committed: ' . $conn->error);
        $outcome['result'] = 'error';
    }
    $conn->close();

    if ($outcome['result'] === 'ok') {
        // New session id after login stops session fixation attacks.
        session_regenerate_id(true);
        $_SESSION[SESSION_USER_ID] = $outcome['user_id'];
        $_SESSION[SESSION_USERNAME] = $outcome['username'];
    }
    return $outcome['result'];
}

/**
 * The database part of attempt_login(); runs inside its transaction.
 * Returns ['result' => 'ok'|'invalid'|'locked'|'error', 'user_id' => int, 'username' => string].
 */
function check_credentials(mysqli $conn, string $username, string $password): array
{
    $outcome = ['result' => 'error', 'user_id' => 0, 'username' => ''];

    // NOW() is used throughout so the lock time follows the database clock.
    $stmt = $conn->prepare(
        'SELECT user_id, username, password_hash,
                (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
           FROM users
          WHERE username = ?
            FOR UPDATE'
    );
    if (!$stmt) {
        error_log('Login query failed: ' . $conn->error);
        return $outcome;
    }
    $stmt->bind_param('s', $username);
    if (!$stmt->execute()) {
        error_log('Login query failed: ' . $stmt->error);
        $stmt->close();
        return $outcome;
    }
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$account) {
        // Unknown username: still run a hash check so the response time
        // does not reveal which usernames exist.
        password_verify($password, '$2y$12$hYMI04iGQkJBzKD3vx.DaOvKqlXciuXiv/Bj2vrAF.RC4V3CFLWo.');
        $outcome['result'] = 'invalid';
        return $outcome;
    }

    if ((int)$account['is_locked'] === 1) {
        // Locked: do not even check the password.
        $outcome['result'] = 'locked';
        return $outcome;
    }

    $userId = (int)$account['user_id'];

    if (password_verify($password, $account['password_hash'])) {
        $reset = update_user($conn, 'UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE user_id = ?', $userId);
        if ($reset !== null) {
            $outcome = ['result' => 'ok', 'user_id' => $userId, 'username' => $account['username']];
        }
        return $outcome;
    }

    // Wrong password: add 1 to the count in the database itself.
    $counted = update_user($conn, 'UPDATE users SET failed_attempts = failed_attempts + 1 WHERE user_id = ?', $userId);

    // Lock only if the count has reached the limit (checked inside the same
    // UPDATE). The counter starts again from 0 once the lock ends.
    $locked = $counted === null ? null : update_user(
        $conn,
        'UPDATE users
            SET failed_attempts = 0,
                locked_until = NOW() + INTERVAL ' . LOGIN_LOCK_MINUTES . ' MINUTE
          WHERE user_id = ? AND failed_attempts >= ' . LOGIN_MAX_ATTEMPTS,
        $userId
    );
    if ($locked !== null) {
        $outcome['result'] = $locked > 0 ? 'locked' : 'invalid';
    }
    return $outcome;
}

/**
 * Run an UPDATE on one user (the only placeholder is user_id).
 * Returns the number of rows changed, or null if the query failed.
 */
function update_user(mysqli $conn, string $sql, int $userId): ?int
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Login update could not be prepared: ' . $conn->error);
        return null;
    }
    $stmt->bind_param('i', $userId);
    if (!$stmt->execute()) {
        error_log('Login update failed: ' . $stmt->error);
        $stmt->close();
        return null;
    }
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed;
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
