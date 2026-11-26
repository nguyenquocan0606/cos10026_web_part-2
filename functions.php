<?php

function dbConnect()
{
    require __DIR__ . '/settings.php';
    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = new mysqli($host, $username, $password, $database);
    if ($mysqli->connect_error) {
        error_log('Database connection failed: ' . $mysqli->connect_error);
        return false;
    }
    if (!$mysqli->set_charset('utf8mb4')) {
        error_log('Database charset setup failed: ' . $mysqli->error);
        $mysqli->close();
        return false;
    }
    return $mysqli;
}

function cleanInput($value)
{
    if (!is_scalar($value)) {
        return '';
    }
    $value = trim((string) $value);
    $value = stripslashes($value);
    return $value;
}

function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function isPost()
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
