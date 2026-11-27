<?php
/*
 * about.php - team page (ABT-2, ABT-7)
 * Owner: Tran Trung Tin
 *
 * The member cards and the fun facts table are both built from the
 * `about` table (sql/about.sql), so a change in the database shows up
 * in both places. Everything else on the page is static.
 */
require_once __DIR__ . '/functions.php';

$members = [];
$loadError = 'Team details are currently unavailable. Please try again later.';

$conn = dbConnect();
if ($conn) {
    $result = $conn->query(
        'SELECT full_name, student_id, part1_contribution, part2_contribution,
                quote, dream_job, dream_job_icon, coding_snack, hometown
         FROM about
         ORDER BY display_order'
    );
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
        $result->free();
        $loadError = '';
    } else {
        error_log('about.php could not load team members: ' . $conn->error);
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Meet W3bb, the four students behind the Edbridge education careers website.">
  <meta name="author" content="W3bb">
  <title>About W3bb | Edbridge</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&amp;family=Fraunces:opsz,wght@9..144,600&amp;display=swap">
  <link rel="stylesheet" href="styles/styles.css">
  <style>
    .about-intro { max-width: 65ch; }
  </style>
</head>
<body class="about-site">
  <?php include __DIR__ . '/header.inc'; ?>

  <main id="main-content" class="about-page" tabindex="-1">
    <div class="about-overview">
      <div class="about-overview-content">
        <div class="about-intro">
          <p class="eyebrow" style="letter-spacing: 0.08em;">THE PEOPLE BEHIND EDBRIDGE</p>
          <h1>Four people. One shared project.</h1>
          <p>We are W3bb, a team of COS10026 students in Hanoi building Edbridge, an education careers website.</p>
        </div>

        <section aria-labelledby="group-heading">
          <h2 id="group-heading">Our group</h2>
          <!-- Nested list: the class belongs to the group. -->
          <ul class="group-details">
            <li><strong>W3bb</strong>
              <ul>
                <li>Unit: COS10026</li>
                <li>Class: Hanoi Class 2</li>
                <li>Class day and time: Friday Morning</li>
              </ul>
            </li>
          </ul>
        </section>
      </div>

      <div class="about-video">
        <video id="brand-intro" autoplay muted playsinline preload="metadata" poster="img/logo.webp" aria-label="Edbridge brand introduction">
          <source src="media/video.mp4" type="video/mp4" media="(prefers-reduced-motion: no-preference)">
          Your browser does not support embedded video.
          <a href="media/video.mp4">Download the video</a> to watch it.
        </video>
        <img class="brand-intro-still" src="img/logo.webp" alt="Edbridge brand introduction" width="1024" height="1024">
      </div>
    </div>

    <section aria-labelledby="members-heading">
      <h2 id="members-heading">Meet the team</h2>
      <p>Each member brings a different contribution to our shared website.</p>
      <?php if ($loadError !== ''): ?>
        <p><?= safeOutput($loadError) ?></p>
      <?php else: ?>
        <!-- Each definition pairs a member with their contributions and quote, loaded from the about table. -->
        <dl class="member-list">
          <?php foreach ($members as $member): ?>
            <div class="member-card">
              <dt><?= safeOutput($member['full_name']) ?></dt>
              <dd>
                <p class="student-id">Student ID: <?= safeOutput($member['student_id']) ?></p>
                <p><strong>Part 1 contributions:</strong> <?= safeOutput($member['part1_contribution']) ?></p>
                <p><strong>Part 2 contributions:</strong> <?= safeOutput($member['part2_contribution']) ?></p>
                <p class="quote-content"><?= safeOutput($member['quote']) ?></p>
              </dd>
            </div>
          <?php endforeach; ?>
        </dl>
      <?php endif; ?>
    </section>

    <section aria-labelledby="photo-heading">
      <h2 id="photo-heading">Together as W3bb</h2>
      <figure class="group-photo">
        <img src="img/group_photo.webp" alt="The four members of W3bb" width="1200" height="900" loading="lazy">
        <figcaption>The W3bb team at work</figcaption>
      </figure>
    </section>

    <section aria-labelledby="facts-heading">
      <h2 id="facts-heading">Beyond the code</h2>
      <p id="facts-help">Our personal fun facts are presented below.</p>
      <?php if ($loadError !== ''): ?>
        <p><?= safeOutput($loadError) ?></p>
      <?php else: ?>
        <div class="table-scroll" role="region" aria-labelledby="facts-heading" aria-describedby="facts-help" tabindex="0">
          <table class="fun-facts">
            <caption>W3bb fun facts</caption>
            <thead>
              <tr>
                <th scope="col">Member</th>
                <th scope="col">Dream job</th>
                <th scope="col">Coding snack</th>
                <th scope="col">Hometown</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($members as $member): ?>
                <tr>
                  <th scope="row"><?= safeOutput($member['full_name']) ?></th>
                  <td><?= safeOutput(trim($member['dream_job_icon'] . ' ' . $member['dream_job'])) ?></td>
                  <td><?= safeOutput($member['coding_snack']) ?></td>
                  <td><?= safeOutput($member['hometown']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <section aria-labelledby="schedule-heading">
      <h2 id="schedule-heading">Our Part 2 task schedule</h2>
      <p id="schedule-help">Part 2 turns the static site into a PHP and MySQL application. Each row groups the Jira tasks for one part of the work; statuses reflect the work merged on GitHub by 27 November 2026.</p>
      <div class="table-scroll" role="region" aria-labelledby="schedule-heading" aria-describedby="schedule-help" tabindex="0">
        <table class="task-schedule">
          <caption>W3bb Part 2 task plan</caption>
          <thead>
            <tr>
              <th scope="col">Phase</th>
              <th scope="col">Task</th>
              <th scope="col">Team member</th>
              <th scope="col">Status on 27 Nov</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row">0. Setup</th>
              <td>Put the Part 2 tasks on Jira; one Git branch and pull request per task (CB-5)</td>
              <td>Tran Trung Tin</td>
              <td>Done</td>
            </tr>
            <tr>
              <th scope="row">0. Setup</th>
              <td>Agree on design decisions and set up a local PHP and MySQL server (CB-3, CB-4)</td>
              <td>Whole team</td>
              <td>Done</td>
            </tr>
            <tr>
              <th scope="row">1. Shared parts</th>
              <td>settings.php, shared functions, header, navigation and footer includes; convert pages to PHP (KH-1 to KH-7)</td>
              <td>Nguyen Quoc An</td>
              <td>Done</td>
            </tr>
            <tr>
              <th scope="row">2. Apply and EOI</th>
              <td>eoi table, apply form posting to process_eoi.php, server-side validation and saving (EOI-1 to EOI-9)</td>
              <td>Do Dinh Phu</td>
              <td>Done</td>
            </tr>
            <tr>
              <th scope="row">3. Jobs and search</th>
              <td>jobs table, Jobs and Home pages from the database, job search (JOB-1 to JOB-6)</td>
              <td>Nguyen Chi Hien</td>
              <td>Done</td>
            </tr>
            <tr>
              <th scope="row">4. About and login</th>
              <td>about and users tables, login and logout with account lockout (ABT-1, ABT-3 to ABT-6)</td>
              <td>Tran Trung Tin</td>
              <td>Done</td>
            </tr>
            <tr>
              <th scope="row">4. About and login</th>
              <td>About page from the database and this Part 2 schedule (ABT-2, ABT-7)</td>
              <td>Tran Trung Tin</td>
              <td>Done</td>
            </tr>
            <tr>
              <th scope="row">5. Manage page</th>
              <td>Login-protected manage.php: list, filter, sort, change status and delete EOIs (MNG-1 to MNG-7)</td>
              <td>Nguyen Quoc An</td>
              <td>Done</td>
            </tr>
            <tr>
              <th scope="row">6. Test and submit</th>
              <td>Final SQL file, full test on an empty database, link and validator check, styling review (NOP-2 to NOP-5)</td>
              <td>Whole team</td>
              <td>In progress</td>
            </tr>
            <tr>
              <th scope="row">6. Test and submit</th>
              <td>README, GenAI credits and final Part 2 contributions (NOP-6, ABT-8)</td>
              <td>Tran Trung Tin</td>
              <td>To do</td>
            </tr>
            <tr>
              <th scope="row">6. Test and submit</th>
              <td>Group slides, demo practice and submission (NOP-7 to NOP-10)</td>
              <td>Whole team; Nguyen Quoc An to prepare the ZIP</td>
              <td>To do</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="country-acknowledgement" aria-labelledby="country-heading">
      <h2 id="country-heading">Acknowledgement of Country</h2>
      <p>We acknowledge Aboriginal and Torres Strait Islander peoples as the Traditional Owners of lands across Australia and pay our respects to Elders past and present. We recognise their continuing connections to Country and the knowledge carried through their languages and cultures.</p>
      <p>As students in Hanoi creating an education careers website, we want our work to support respect and inclusion. We welcome Aboriginal and Torres Strait Islander applicants.</p>
    </section>
  </main>

  <?php include __DIR__ . '/footer.inc'; ?>
</body>
</html>
