<?php
/**
 * Shared job-listing functions for jobs.php, search.php and index.php.
 */

/**
 * Convert the three text-list database fields into individual HTML list items.
 * Both newline-separated and semicolon-separated values are supported.
 */
function displayJobListItems($value): void
{
    $value = trim((string)$value);

    if ($value === '') {
        echo '<li>Not specified.</li>';
        return;
    }

    // Also support values that were saved as a JSON array.
    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $items = $decoded;
    } else {
        $items = preg_split('/\r\n|\r|\n|;/', $value);
    }

    foreach ($items as $item) {
        if (is_scalar($item)) {
            $item = trim((string)$item);
            if ($item !== '') {
                echo '<li>' . safeOutput($item) . '</li>';
            }
        }
    }
}

/**
 * Display jobs from a mysqli result.
 * Set $compact to true for the smaller job cards on the homepage.
 * The detailed listing is shared by jobs.php and search.php.
 */
function displayJobListings(mysqli_result $jobs, bool $compact = false): void
{
    if ($jobs->num_rows === 0) {
        if ($compact) {
            echo '<p class="no-jobs">No current positions are available.</p>';
        } else {
            echo '<p class="job-listing">No jobs found. Please try another search.</p>';
        }
        return;
    }

    while ($job = $jobs->fetch_assoc()) {
        $reference = (string)($job['job_reference'] ?? '');
        $title = (string)($job['title'] ?? 'Untitled position');
        $category = (string)($job['category'] ?? '');
        $tagline = (string)($job['tagline'] ?? '');
        $description = (string)($job['description'] ?? '');
        $safeReference = safeOutput($reference);
        $safeTitle = safeOutput($title);
        $safeCategory = safeOutput($category);
        $safeTagline = safeOutput($tagline);
        $safeDescription = safeOutput($description);
        $jobId = 'job-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $reference);
        $safeJobId = safeOutput($jobId);
        $applyUrl = 'apply.php?job=' . rawurlencode($reference);
        $safeApplyUrl = safeOutput($applyUrl);

        if ($compact) {
            echo '<article class="job-card" id="' . $safeJobId . '">';
            echo '<p class="job-reference">JOB REFERENCE: ' . $safeReference . '</p>';
            if ($category !== '') {
                echo '<p class="job-category">' . $safeCategory . '</p>';
            }
            echo '<h3><a href="jobs.php#' . $safeJobId . '">' . $safeTitle . '</a></h3>';
            if ($tagline !== '') {
                echo '<p>' . $safeTagline . '</p>';
            } elseif ($description !== '') {
                echo '<p>' . $safeDescription . '</p>';
            }
            echo '<p class="job-card-action"><a href="' . $safeApplyUrl . '">Apply for this role</a></p>';
            echo '</article>';
            continue;
        }

        $salaryMin = (float)($job['salary_min'] ?? 0);
        $salaryMax = (float)($job['salary_max'] ?? 0);

        echo '<section class="job-listing" id="' . $safeJobId . '" aria-labelledby="' . $safeJobId . '-title">';
        echo '<p class="job-reference">JOB REFERENCE: ' . $safeReference . '</p>';
        if ($category !== '') {
            echo '<p class="job-category">' . $safeCategory . '</p>';
        }
        echo '<h2 id="' . $safeJobId . '-title">' . $safeTitle . '</h2>';

        if ($tagline !== '') {
            echo '<p>' . $safeTagline . '</p>';
        }
        if ($description !== '') {
            echo '<p>' . $safeDescription . '</p>';
        }

        echo '<section aria-labelledby="' . $safeJobId . '-details">';
        echo '<h3 id="' . $safeJobId . '-details">Role details</h3>';
        echo '<dl class="job-details">';
        echo '<dt>Salary</dt><dd>AUD $' . number_format($salaryMin) . '–$' . number_format($salaryMax) . ' per year</dd>';
        echo '<dt>Experience level</dt><dd>' . safeOutput($job['experience_level'] ?? '') . '</dd>';
        echo '<dt>Reports to</dt><dd>' . safeOutput($job['reports_to'] ?? '') . '</dd>';
        echo '<dt>Employment type</dt><dd>' . safeOutput($job['employment_type'] ?? '') . '</dd>';
        echo '<dt>Work arrangement</dt><dd>' . safeOutput($job['work_arrangement'] ?? '') . '</dd>';
        echo '<dt>Location</dt><dd>' . safeOutput($job['location'] ?? '') . '</dd>';
        echo '</dl></section>';

        echo '<section aria-labelledby="' . $safeJobId . '-duties">';
        echo '<h3 id="' . $safeJobId . '-duties">Key responsibilities</h3><ul>';
        displayJobListItems($job['responsibilities'] ?? '');
        echo '</ul></section>';

        echo '<section aria-labelledby="' . $safeJobId . '-essential">';
        echo '<h3 id="' . $safeJobId . '-essential">Essential requirements</h3><ul>';
        displayJobListItems($job['essential_requirements'] ?? '');
        echo '</ul></section>';

        echo '<section aria-labelledby="' . $safeJobId . '-preferred">';
        echo '<h3 id="' . $safeJobId . '-preferred">Preferable requirements</h3><ul>';
        displayJobListItems($job['preferable_requirements'] ?? '');
        echo '</ul></section>';

        echo '<p class="job-action">Interested in this role? ';
        echo '<a href="' . $safeApplyUrl . '">Apply using reference ' . $safeReference . '</a>.</p>';
        echo '</section>';
    }
}

/*
 * Shared helpers for every page (settings, input cleaning, safe output).
 */

/**
 * Open a database connection using the values in settings.php.
 * Returns null when the database cannot be reached.
 */
function dbConnect(): ?mysqli
{
    require __DIR__ . '/settings.php';

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($host, $user, $pwd, $sql_db);
    if ($conn->connect_error) {
        error_log('Database connection failed: ' . $conn->connect_error);
        return null;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

/**
 * Clean one value typed by the user: trim and stripslashes.
 * Use this before validating or saving; escape later with safeOutput().
 */
function cleanText($value): string
{
    if (!is_scalar($value)) {
        return '';
    }
    return stripslashes(trim((string)$value));
}

/** Clean one value typed by the user and make it HTML-safe: trim, stripslashes, htmlspecialchars. */
function cleanInput($value): string
{
    return safeOutput(cleanText($value));
}

/** Make any value safe to print inside HTML. */
function safeOutput($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
