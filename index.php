
<?php
require_once __DIR__ . '/functions.php';

$jobs = [];
$loadError = 'Job listings are currently unavailable.';

$conn = dbConnect();
if ($conn) {
    // Retrieve every job from the database.
    $result = $conn->query(
        'SELECT job_reference, category, title, tagline
         FROM jobs
         ORDER BY job_reference ASC'
    );
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $jobs[] = $row;
        }
        $result->free();
        $loadError = '';
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Edbridge careers: join the team building accessible language-learning apps for schools and adult learners.">
  <meta name="author" content="W3bb">
  <title>Edbridge</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&amp;family=Fraunces:opsz,wght@9..144,600&amp;display=swap">
  <link rel="stylesheet" href="styles/styles.css">

  <style>
    .a1_table_job_stage {
      width: 100%;
      border: 1px solid var(--line-strong);
      border-collapse: collapse;
    }

    .a1_table_job_stage caption {
      text-align: left;
      border: 1px solid var(--line-strong);
      border-bottom: none;
      padding: 0.5rem 1rem;
      background-color: var(--paper-raised);
    }

    .a1_table_job_stage th,
    .a1_table_job_stage td {
      border: 1px solid var(--line-strong);
      padding: 0.5rem 1rem;
    }

    .a1_table_job_stage th {
      text-align: left;
      background-color: var(--col-strong);
    }

    .a1_table_highlight {
      background-color: var(--paper-raised);
    }

    .a1_table_job_stage tr:hover td {
      background-color: var(--brand-tint);
    }

    .a1_table_job_stage tbody tr:last-child th,
    .a1_table_job_stage tbody tr:last-child td {
      background-color: var(--paper-raised);
    }
  </style>
</head>
<body>

  <?php require_once __DIR__ . '/header.inc'; ?>

  <main id="main">
    <!-- INTRODUCTION AND JOB LISTINGS -->
    <section class="intro">
      <div class="container">
        <div class="hero_content">
          <img
            class="a1_cover_img"
            src="img/at_work.webp"
            alt="A team of people discussing together"
            width="720"
            height="481"
          >

          <span class="a1_first_line">
            EDBRIDGE CAREERS &middot; NOW HIRING
          </span>
          <h1>Every language is a bridge</h1>
          <p class="a1_slogan">Learn a language. Open a world</p>
          <p>
            Edbridge is an EdTech company that builds accessible
            language-learning apps for schools and adult learners.
            Our web team designs the pages people use to practise every day,
            and we are growing.
          </p>
          <div class="roles_header">
            <p class="roles">See open roles</p>

            <!-- Dynamic number of jobs -->
            <p class="number_of_job">
              <?= count($jobs) ?>
              <?= count($jobs) === 1 ? 'open position' : 'open positions' ?>
            </p>
          </div>

          <!-- DYNAMIC JOB CARDS -->
          <div class="a1_job_container">
            <?php if ($loadError !== ''): ?>
              <p><?= safeOutput($loadError) ?></p>
            <?php elseif (count($jobs) > 0): ?>
              <?php foreach ($jobs as $index => $job): ?>
                <?php
                  // Alternate between the two existing card styles.
                  $cardClass = ($index % 2 === 0)
                      ? 'a1_fe_job'
                      : 'a1_ui_ux_job';

                  $category = !empty($job['category'])
                      ? strtoupper($job['category'])
                      : 'GENERAL';
                ?>

                <div class="<?= $cardClass ?>">
                  <span class="a1_type_job">
                    <?= sprintf('%02d', $index + 1) ?>
                    / <?= safeOutput($category) ?>
                  </span>
                  <p class="a1_name_job">
                    <?= safeOutput($job['title']) ?>
                  </p>
                  <p class="a1_detail">
                    <?= safeOutput($job['tagline']) ?>
                  </p>
                  <a href="jobs.php" class="a1_explore">
                    Explore role
                  </a>

                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>There are currently no open positions.</p>
            <?php endif; ?>

          </div>
          <a class="a1_meet_team" href="about.php">
            Meet the team
          </a>
        </div>
      </div>
    </section>

    <!-- WHY WORK HERE -->
    <section class="why_work_here">
      <div class="container">
        <div class="a1_why">

          <h2>Why work at Edbridge</h2>

          <div class="a1_why_container">

            <div class="a1_box">
              <h3>Lessons for every learner</h3>
              <p>
                Our apps follow WCAG 2.2 so learners who use screen readers,
                keyboards or small phones can study too. You will help keep
                it that way.
              </p>
            </div>

            <div class="a1_box">
              <h3>12 languages and counting</h3>
              <p>
                From Vietnamese to Spanish, every course is written with
                native-speaker teachers. Staff get free lessons in any of them.
              </p>
            </div>

            <div class="a1_box">
              <h3>Flexible, hybrid work</h3>
              <p>
                Two studio days a week in Melbourne, the rest from wherever
                you learn best, plus a yearly learning budget.
              </p>
            </div>

          </div>
        </div>
      </div>
    </section>

    <!-- HOW WE HIRE -->
    <section class="a1_how">
      <div class="container">

        <h2>How we hire</h2>

        <p>
          Our process is short and transparent. Tell us in your application
          if you need any adjustments at any stage.
        </p>

        <div class="hiring-table-scroll"
             role="region"
             aria-label="Hiring process table"
             tabindex="0">

          <table class="a1_table_job_stage">

            <caption style="font-weight: bold;">
              Edbridge hiring process, step by step
            </caption>

            <thead>
              <tr>
                <th scope="col">Stage</th>
                <th scope="col">Step</th>
                <th scope="col">What happens</th>
                <th scope="col">Typical time</th>
              </tr>
            </thead>

            <tbody>

              <tr class="a1_table_highlight">
                <th scope="row">Apply</th>
                <td>Online form</td>
                <td>
                  Choose a job reference number and tell us about your skills.
                </td>
                <td>10 minutes</td>
              </tr>

              <tr>
                <th scope="row" rowspan="2">Interviews</th>
                <td>Team chat</td>
                <td>
                  A relaxed video call with two people from the team you
                  would join.
                </td>
                <td>30 minutes</td>
              </tr>

              <tr class="a1_table_highlight">
                <td>Practical task</td>
                <td>
                  A small, paid take-home task based on a real lesson page.
                </td>
                <td>2 hours</td>
              </tr>

              <tr>
                <th scope="row">Offer</th>
                <td>Decision</td>
                <td>
                  We call you with a decision and feedback, whatever
                  the outcome.
                </td>
                <td>Within 1 week</td>
              </tr>

              <tr>
                <th class="a1_table_highlight" scope="row" colspan="3">
                  From applicant to offer
                </th>
                <td>3–4 weeks</td>
              </tr>

            </tbody>
          </table>
        </div>

      </div>
    </section>

  </main>

  <?php include __DIR__ . '/footer.inc'; ?>

</body>
</html>
