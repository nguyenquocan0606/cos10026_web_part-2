<?php
require_once './settings.php';
require_once './functions.php';

$conn = new mysqli($host, $user, $pwd, $sql_db);
if ($conn->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error, ENT_QUOTES, 'UTF-8'));
}
$conn->set_charset('utf8mb4');

$result = $conn->query('SELECT * FROM jobs ORDER BY job_reference');
if (!$result) {
    die('Could not retrieve jobs: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Explore careers at Edbridge in accessible digital learning and educational technology.">
    <meta name="author" content="Edbridge Web Project Team">
    <title>Careers at Edbridge | Job Opportunities</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Fraunces:opsz,wght@9..144,600&display=swap">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
<?php include 'header.inc'; ?>

<main class="jobs-page">
    <!-- Introduction. -->
    <section class="jobs-intro" aria-labelledby="jobs-heading">
        <p class="jobs-eyebrow" style="font-weight: 700;">CAREERS AT EDBRIDGE</p>
        <h1 id="jobs-heading">Build a more inclusive future for learning</h1>
        <p>At Edbridge, we develop digital learning tools that help people access education in flexible and inclusive ways. Join our team to create online experiences that support learners and educators.</p>
        <p>Explore our current opportunities below and review the responsibilities and requirements before applying.</p>
        <a class="jobs-apply-link" href="apply.html">View application form</a>
    </section>

    <!-- Sidebar -->
    <aside class="jobs-aside" aria-labelledby="inclusive-workplace">
        <h2 id="inclusive-workplace">Learning should include everyone</h2>
        <p>Edbridge welcomes applications from Aboriginal and Torres Strait Islander peoples and people from diverse backgrounds. We aim to create inclusive digital learning experiences and a respectful workplace.</p>
        <h3>Before you apply</h3>
        <ol>
            <li>Read the job description.</li>
            <li>Check the essential requirements.</li>
            <li>Note the five-character job reference.</li>
            <li>Complete the application form.</li>
        </ol>
        <p><a href="apply.html">Continue to apply</a></p>
    </aside>

    <!-- Displaying job listings dynamically -->
    <?php displayJobListings($result); ?>

    <!-- Research and References -->
    <section class="job-sources" aria-labelledby="sources-heading">
        <h2 id="sources-heading">Research and References</h2>
        <ul>
            <li>
                <a href="https://au.seek.com/web-developer-jobs?jobId=95536663&amp;type=standard">Web Developer Job Advertisement</a>
                <p>Publisher: SEEK Australia.</p>
            </li>
            <li>
                <a href="https://au.seek.com/ux-designer-jobs?jobId=95229078&amp;type=standard">UX Designer Job Advertisement</a>
                <p>Publisher: SEEK Australia.</p>
            </li>
        </ul>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-columns">
            <div>
                <a href="index.php" class="Edbridge"><span class="brand-logo"><img src="img/logo.webp" alt="" width="1024" height="1024" loading="lazy"></span><span>Edbridge</span></a>
                <p class="footer-tagline">Learn a language. Open a world.</p>
            </div>
            <section aria-labelledby="project-links-heading">
                <h3 id="project-links-heading">Project</h3>
                <ul>
                    <li><a href="https://soulknightprequel15.atlassian.net/jira/software/projects/CGW/boards/3?filter=&amp;groupBy=none">Jira board</a></li>
                    <li><a href="https://github.com/nguyenquocan0606/cos10026_web">GitHub repository</a></li>
                    <li><a href="https://nguyenquocan0606.github.io/cos10026_web/">Live site</a></li>
                </ul>
            </section>
            <section aria-labelledby="quick-links-heading">
                <h3 id="quick-links-heading">Quick links</h3>
                <ul>
                    <li><a href="jobs.php">Explore jobs</a></li>
                    <li><a href="apply.html">Apply for a role</a></li>
                    <li><a href="about.html#members-heading">Meet the team</a></li>
                </ul>
            </section>
        </div>
        <section aria-labelledby="credits-heading">
            <h3 id="credits-heading">GenAI sources and credits</h3>
            <p><a href="https://openai.com/codex/">OpenAI Codex</a> assisted with drafting and editing webpage content and CSS logo cropping. Team details and task information were supplied by W3bb. The <a href="img/logo.webp">Edbridge logo and brand intro video</a> was generated using Gemini Argon and supplied by the team.</p>
        </section>
        <p class="footer-copyright">&copy; 2026 Edbridge &mdash; a fictional company created by W3bb for the COS10026 Web Technology Project, Swinburne University of Technology.</p>
    </div>
</footer>

<?php
$result->free();
$conn->close();
?>
</body>
</html>
