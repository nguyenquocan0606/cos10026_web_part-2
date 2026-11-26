<?php
session_start();
require_once __DIR__ . '/functions.php';

if (!isPost()) {
    header('Location: apply.php');
    exit;
}

$fields = [
    'job_reference',
    'first_name',
    'last_name',
    'date_of_birth',
    'gender',
    'street_address',
    'suburb',
    'state',
    'postcode',
    'email',
    'phone',
    'other_skills',
];

$old = [];
foreach ($fields as $field) {
    $old[$field] = cleanInput($_POST[$field] ?? '');
}
$postedSkills = $_POST['skills'] ?? [];
$old['skills'] = is_array($postedSkills)
    ? array_values(array_filter(array_map('cleanInput', $postedSkills)))
    : [];

$errors = [];
$addError = static function ($field, $message) use (&$errors) {
    $errors[$field][] = $message;
};

if (!preg_match('/^[A-Za-z0-9]{5}$/', $old['job_reference'])) {
    $addError('job_reference', 'Job reference must contain exactly 5 alphanumeric characters.');
} else {
    $old['job_reference'] = strtoupper($old['job_reference']);
}

if (!preg_match('/^[A-Za-z]{1,20}$/', $old['first_name'])) {
    $addError('first_name', 'First name must contain 1 to 20 letters.');
}
if (!preg_match('/^[A-Za-z]{1,20}$/', $old['last_name'])) {
    $addError('last_name', 'Last name must contain 1 to 20 letters.');
}

$dob = null;
if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $old['date_of_birth'], $dobParts)) {
    $addError('date_of_birth', 'Date of birth must use dd/mm/yyyy.');
} else {
    $day = (int) $dobParts[1];
    $month = (int) $dobParts[2];
    $year = (int) $dobParts[3];
    if (!checkdate($month, $day, $year)) {
        $addError('date_of_birth', 'Date of birth is not a valid calendar date.');
    } else {
        $dob = sprintf('%04d-%02d-%02d', $year, $month, $day);
        if (new DateTimeImmutable($dob) >= new DateTimeImmutable('today')) {
            $addError('date_of_birth', 'Date of birth must be in the past.');
        }
    }
}

$allowedGenders = ['Male', 'Female', 'Other', 'Prefer not to say'];
if (!in_array($old['gender'], $allowedGenders, true)) {
    $addError('gender', 'Please select a gender.');
}
if ($old['street_address'] === '' || strlen($old['street_address']) > 40) {
    $addError('street_address', 'Street address is required and must be at most 40 characters.');
}
if ($old['suburb'] === '' || strlen($old['suburb']) > 40) {
    $addError('suburb', 'Suburb/town is required and must be at most 40 characters.');
}

$allowedStates = ['VIC', 'NSW', 'QLD', 'NT', 'WA', 'SA', 'TAS', 'ACT'];
if (!in_array($old['state'], $allowedStates, true)) {
    $addError('state', 'Please select a valid state or territory.');
}
if (!preg_match('/^[0-9]{4}$/', $old['postcode'])) {
    $addError('postcode', 'Postcode must contain exactly 4 digits.');
}
if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 255) {
    $addError('email', 'Please enter a valid email address.');
}
if (!preg_match('/^[0-9]{8,12}$/', $old['phone'])) {
    $addError('phone', 'Phone number must contain 8 to 12 digits.');
}

$allowedSkills = ['HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL'];
$validSkills = [];
foreach ($old['skills'] as $skill) {
    if (in_array($skill, $allowedSkills, true) && !in_array($skill, $validSkills, true)) {
        $validSkills[] = $skill;
    }
}
$old['skills'] = $validSkills;
if (!$validSkills) {
    $addError('skills', 'Please select at least one skill.');
}

$mysqli = dbConnect();
if (!$mysqli) {
    $addError('_form', 'The application database is unavailable. Please try again later.');
} elseif (preg_match('/^[A-Z0-9]{5}$/', $old['job_reference'])) {
    $jobQuery = $mysqli->prepare('SELECT job_reference FROM jobs WHERE job_reference = ?');
    if (!$jobQuery) {
        error_log('Could not prepare job-reference lookup: ' . $mysqli->error);
        $addError('job_reference', 'The job reference could not be verified. Please try again later.');
    } else {
        $jobQuery->bind_param('s', $old['job_reference']);
        if (!$jobQuery->execute()) {
            error_log('Could not execute job-reference lookup: ' . $jobQuery->error);
            $addError('job_reference', 'The job reference could not be verified. Please try again later.');
        } else {
            $jobQuery->store_result();
            if ($jobQuery->num_rows === 0) {
                $addError('job_reference', 'Please enter a job reference that exists in the jobs list.');
            }
        }
        $jobQuery->close();
    }
}

if ($errors) {
    $_SESSION['eoi_errors'] = $errors;
    $_SESSION['eoi_old'] = $old;
    if ($mysqli) {
        $mysqli->close();
    }
    header('Location: apply.php');
    exit;
}

$createSql = "
CREATE TABLE IF NOT EXISTS eoi (
  EOInumber INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_reference VARCHAR(5) NOT NULL,
  first_name VARCHAR(20) NOT NULL,
  last_name VARCHAR(20) NOT NULL,
  date_of_birth DATE NOT NULL,
  gender VARCHAR(20) NOT NULL,
  street_address VARCHAR(40) NOT NULL,
  suburb VARCHAR(40) NOT NULL,
  state VARCHAR(3) NOT NULL,
  postcode VARCHAR(4) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(12) NOT NULL,
  skill1 VARCHAR(50) NULL,
  skill2 VARCHAR(50) NULL,
  skill3 VARCHAR(50) NULL,
  skill4 VARCHAR(50) NULL,
  skill5 VARCHAR(50) NULL,
  other_skills TEXT NULL,
  status ENUM('New', 'Current', 'Final') NOT NULL DEFAULT 'New',
  PRIMARY KEY (EOInumber)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$mysqli->query($createSql)) {
    error_log('Could not ensure EOI table exists: ' . $mysqli->error);
    $_SESSION['eoi_errors'] = [
        '_form' => ['The application could not be saved. Please try again later.'],
    ];
    $_SESSION['eoi_old'] = $old;
    $mysqli->close();
    header('Location: apply.php');
    exit;
}

$skillColumns = array_pad($validSkills, 5, null);
$insert = $mysqli->prepare(
    'INSERT INTO eoi
     (job_reference, first_name, last_name, date_of_birth, gender, street_address, suburb, state, postcode, email, phone, skill1, skill2, skill3, skill4, skill5, other_skills)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

if (!$insert) {
    error_log('Could not prepare EOI insert: ' . $mysqli->error);
    $_SESSION['eoi_errors'] = [
        '_form' => ['The application could not be saved. Please try again later.'],
    ];
    $_SESSION['eoi_old'] = $old;
    $mysqli->close();
    header('Location: apply.php');
    exit;
}

$insert->bind_param(
    'sssssssssssssssss',
    $old['job_reference'],
    $old['first_name'],
    $old['last_name'],
    $dob,
    $old['gender'],
    $old['street_address'],
    $old['suburb'],
    $old['state'],
    $old['postcode'],
    $old['email'],
    $old['phone'],
    $skillColumns[0],
    $skillColumns[1],
    $skillColumns[2],
    $skillColumns[3],
    $skillColumns[4],
    $old['other_skills']
);

if (!$insert->execute()) {
    error_log('Could not save EOI: ' . $insert->error);
    $_SESSION['eoi_errors'] = [
        '_form' => ['The application could not be saved. Please try again later.'],
    ];
    $_SESSION['eoi_old'] = $old;
    $insert->close();
    $mysqli->close();
    header('Location: apply.php');
    exit;
}

$eoiNumber = $insert->insert_id;
$insert->close();
$mysqli->close();

unset($_SESSION['eoi_errors'], $_SESSION['eoi_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Application Received | Edbridge</title>
  <link rel="stylesheet" href="styles/styles.css">
</head>
<body class="apply-page">
  <main>
    <h1>Application received</h1>
    <p>Your Expression of Interest was submitted successfully.</p>
    <p><strong>EOInumber:</strong> <?= escapeHtml($eoiNumber) ?></p>
    <p><strong>Street address:</strong> <?= escapeHtml($old['street_address']) ?></p>
    <p><a href="apply.php">Submit another application</a></p>
  </main>
</body>
</html>
