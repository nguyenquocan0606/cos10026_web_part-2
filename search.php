<?php
require_once './settings.php';
require_once './functions.php';

$conn = new mysqli($host, $user, $pwd, $sql_db);
if ($conn->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error, ENT_QUOTES, 'UTF-8'));
}
$conn->set_charset('utf8mb4');

// Store user's input
if (isset($_GET['q']) && is_string($_GET['q'])) {
    $searchTerm = trim($_GET['q']);
} else {
    $searchTerm = '';
}

// Retreive all jobs if search bar empty
if ($searchTerm === '') {
    $result = $conn->query('SELECT * FROM jobs ORDER BY job_reference');
    if (!$result) {
        die('Search failed: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
    }
} else {
    // Treat apostrophes, percent signs and underscores as ordinary search characters
    // The ! character is used as the LIKE escape character
    $literalTerm = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $searchTerm);
    $searchValue = '%' . $literalTerm . '%';

    $sql = "SELECT * FROM jobs
            WHERE job_reference LIKE ? ESCAPE '!'
               OR title LIKE ? ESCAPE '!'
               OR description LIKE ? ESCAPE '!'
            ORDER BY job_reference";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Search query could not be prepared: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
    }

    $stmt->bind_param('sss', $searchValue, $searchValue, $searchValue);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Search current careers at Edbridge.">
    <title>Search Jobs | Edbridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Fraunces:opsz,wght@9..144,600&display=swap">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
<?php include __DIR__ . '/header.inc'; ?>

<main class="jobs-page">
    <section class="jobs-intro" aria-labelledby="search-heading">
        <p class="jobs-eyebrow" style="font-weight: 700;">CAREERS AT EDBRIDGE</p>
        <h1 id="search-heading">Search job opportunities</h1>
        <?php if ($searchTerm === ''): ?>
            <p>Showing all available positions. Enter a job reference, title or description in the search box above.</p>
        <?php else: ?>
            <p>Search results for: <strong><?php echo jobEscape($searchTerm); ?></strong></p>
        <?php endif; ?>
    </section>

    <!-- Display job listings dynamically -->
    <?php displayJobListings($result); ?>
</main>

<?php include __DIR__ . '/footer.inc'; ?>

<?php
$result->free();
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
$conn->close();
?>
</body>
</html>
