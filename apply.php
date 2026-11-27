<?php
session_start();

$errors = $_SESSION['eoi_errors'] ?? [];
$old = $_SESSION['eoi_old'] ?? [];
unset($_SESSION['eoi_errors'], $_SESSION['eoi_old']);

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$fieldValue = static function ($name) use ($old, $escape) {
    return $escape($old[$name] ?? '');
};
$fieldError = static function ($name) use ($errors, $escape) {
    if (empty($errors[$name])) {
        return '';
    }

    return '<span class="field-error" role="alert">' .
        $escape(implode(' ', $errors[$name])) . '</span>';
};

$selectedJob = $old['job_reference'] ?? ($_GET['job'] ?? '');
if (!is_string($selectedJob) || !preg_match('/^[A-Za-z0-9]{5}$/', $selectedJob)) {
    $selectedJob = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Apply for a role at Edbridge. Tell us about your skills and experience using our job application form.">
  <meta name="author" content="Do Dinh Phu | W3bb">
  <title>Apply for a Job | Edbridge</title>
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&amp;family=Fraunces:opsz,wght@9..144,600&amp;display=swap">

  <link rel="stylesheet" href="styles/styles.css">
  <!-- Small embedded CSS example for the application instructions. -->
  <style>
    .application-intro { max-width: 65ch; }
    .form-errors {
      max-width: 65ch;
      margin: 1rem 0;
      padding: 1rem;
      border: 2px solid var(--accent);
      background: var(--paper-raised);
    }
    .field-error {
      display: block;
      margin-top: 0.25rem;
      color: var(--accent-text);
      font-weight: 700;
    }
  </style>
</head>

<body class="apply-page">
<?php include __DIR__ . '/header.inc'; ?>

  <!-- Main content -->
  <main>
    <h1>Job Application Form</h1>

    <p class="application-intro">Please complete all required fields to apply for a position at Edbridge.</p>

    <?php if ($errors): ?>
      <div class="form-errors" role="alert">
        <p><strong>Please correct the following:</strong></p>
        <ul>
          <?php foreach ($errors as $fieldErrors): ?>
            <?php foreach ($fieldErrors as $error): ?>
              <li><?= $escape($error) ?></li>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form id="application-form" action="process_eoi.php" method="post" novalidate>

      <!-- Job reference number -->
      <div class="form-row">
        <label for="job_reference">Job reference number:</label>
        <input
          type="text"
          id="job_reference"
          name="job_reference"
          value="<?= $escape($selectedJob) ?>"
          title="Enter exactly 5 alphanumeric characters."
          >
        <?= $fieldError('job_reference') ?>
      </div>

      <!-- First name -->
      <div class="form-row">
        <label for="first_name">First name:</label>
        <input
          type="text"
          id="first_name"
          name="first_name"
          value="<?= $fieldValue('first_name') ?>"
          title="Enter 1 to 20 letters without spaces."
          autocomplete="given-name"
          >
        <?= $fieldError('first_name') ?>
      </div>

      <!-- Last name -->
      <div class="form-row">
        <label for="last_name">Last name:</label>
        <input
          type="text"
          id="last_name"
          name="last_name"
          value="<?= $fieldValue('last_name') ?>"
          title="Enter 1 to 20 letters without spaces."
          autocomplete="family-name"
          >
        <?= $fieldError('last_name') ?>
      </div>

      <!-- Native pattern checks day/month lengths andleap years. -->
      <div class="form-row">
        <label for="date_of_birth">Date of birth:</label>
        <input
          type="text"
          id="date_of_birth"
          name="date_of_birth"
          placeholder="dd/mm/yyyy"
          value="<?= $fieldValue('date_of_birth') ?>"
          aria-describedby="dob-hint"
          title="Enter a real calendar date in dd/mm/yyyy format."
          autocomplete="bday"
          >
        <?= $fieldError('date_of_birth') ?>
      </div>

      <p id="dob-hint">Use dd/mm/yyyy and enter a real calendar date.</p>

      <!-- Gender -->
      <fieldset class="form-row">
        <legend>Gender:</legend>

        <div class="choice-group">
          <label for="gender_male">
            <input type="radio" id="gender_male" name="gender" value="Male" <?= (($old['gender'] ?? '') === 'Male') ? 'checked' : '' ?>>
            Male
          </label>

          <label for="gender_female">
            <input type="radio" id="gender_female" name="gender" value="Female" <?= (($old['gender'] ?? '') === 'Female') ? 'checked' : '' ?>>
            Female
          </label>

          <label for="gender_other">
            <input type="radio" id="gender_other" name="gender" value="Other" <?= (($old['gender'] ?? '') === 'Other') ? 'checked' : '' ?>>
            Other
          </label>

          <label for="gender_prefer_not">
            <input type="radio" id="gender_prefer_not" name="gender" value="Prefer not to say" <?= (($old['gender'] ?? '') === 'Prefer not to say') ? 'checked' : '' ?>>
            Prefer not to say
          </label>
        </div>
        <?= $fieldError('gender') ?>
      </fieldset>

      <!-- Street address -->
      <div class="form-row">
        <label for="street_address">Street address:</label>
        <input
          type="text"
          id="street_address"
          name="street_address"
          value="<?= $fieldValue('street_address') ?>"
          autocomplete="street-address"
          >
        <?= $fieldError('street_address') ?>
      </div>

      <!-- Suburb or town -->
      <div class="form-row">
        <label for="suburb">Suburb/Town:</label>
        <input
          type="text"
          id="suburb"
          name="suburb"
          value="<?= $fieldValue('suburb') ?>"
          autocomplete="address-level2"
          >
        <?= $fieldError('suburb') ?>
      </div>

      <!-- State -->
      <div class="form-row">
        <label for="state">State:</label>
        <select id="state" name="state" autocomplete="address-level1">
          <option value="">Select a state or territory</option>
          <?php foreach (['VIC', 'NSW', 'QLD', 'NT', 'WA', 'SA', 'TAS', 'ACT'] as $stateOption): ?>
            <option value="<?= $stateOption ?>" <?= (($old['state'] ?? '') === $stateOption) ? 'selected' : '' ?>><?= $stateOption ?></option>
          <?php endforeach; ?>
        </select>
        <?= $fieldError('state') ?>
      </div>

      <!-- Postcode -->
      <div class="form-row">
        <label for="postcode">Postcode:</label>
        <input
          type="text"
          id="postcode"
          name="postcode"
          inputmode="numeric"
          value="<?= $fieldValue('postcode') ?>"
          title="Enter exactly 4 digits."
          autocomplete="postal-code"
          >
        <?= $fieldError('postcode') ?>
      </div>

      <!-- Email -->
      <div class="form-row">
        <label for="email">Email:</label>
        <input
          type="email"
          id="email"
          name="email"
          value="<?= $fieldValue('email') ?>"
          autocomplete="email"
          >
        <?= $fieldError('email') ?>
      </div>

      <!-- Phone number -->
      <div class="form-row">
        <label for="phone">Phone number:</label>
        <input
          type="tel"
          id="phone"
          name="phone"
          inputmode="numeric"
          value="<?= $fieldValue('phone') ?>"
          title="Enter 8 to 12 digits without spaces."
          autocomplete="tel"
          >
        <?= $fieldError('phone') ?>
      </div>

      <!-- Skill list -->
      <fieldset class="form-row" id="skills" aria-describedby="skills-hint">
        <legend>Skill list:</legend>

        <p id="skills-hint" style="font-size: 0.95rem;">Select at least one skill.</p>

        <div class="choice-group">
          <label for="skill_html">
            <input type="checkbox" id="skill_html" name="skills[]" value="HTML" <?= in_array('HTML', $old['skills'] ?? [], true) ? 'checked' : '' ?>>
            HTML
          </label>

          <label for="skill_css">
            <input type="checkbox" id="skill_css" name="skills[]" value="CSS" <?= in_array('CSS', $old['skills'] ?? [], true) ? 'checked' : '' ?>>
            CSS
          </label>

          <label for="skill_javascript">
            <input type="checkbox" id="skill_javascript" name="skills[]" value="JavaScript" <?= in_array('JavaScript', $old['skills'] ?? [], true) ? 'checked' : '' ?>>
            JavaScript
          </label>

          <label for="skill_php">
            <input type="checkbox" id="skill_php" name="skills[]" value="PHP" <?= in_array('PHP', $old['skills'] ?? [], true) ? 'checked' : '' ?>>
            PHP
          </label>

          <label for="skill_mysql">
            <input type="checkbox" id="skill_mysql" name="skills[]" value="MySQL" <?= in_array('MySQL', $old['skills'] ?? [], true) ? 'checked' : '' ?>>
            MySQL
          </label>
        </div>
        <?= $fieldError('skills') ?>
      </fieldset>

      <!-- Other skills -->
      <div class="form-row">
        <label for="other_skills">Other skills:</label>
        <textarea
          id="other_skills"
          name="other_skills"
          rows="5"
          cols="40"><?= $fieldValue('other_skills') ?></textarea>
      </div>

      <!-- Form buttons -->
      <div class="form-actions">
        <button type="submit">Submit Application</button>
        <button type="reset">Clear Form</button>
      </div>

    </form>
  </main>

<?php include __DIR__ . '/footer.inc'; ?>
</body>
</html>
