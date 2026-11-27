<?php
/**
 * HR manager page: list, filter, sort, update and delete EOIs.
 * Only logged-in managers may open it (login.php / auth.php).
 */
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/functions.php';

// Fields the manager may sort by: column name => label.
// Only these keys ever reach the ORDER BY clause.
$sortFields = [
    'EOInumber'     => 'EOI number',
    'job_reference' => 'Job reference',
    'first_name'    => 'First name',
    'last_name'     => 'Last name',
    'status'        => 'Status',
];
$statuses = ['New', 'Current', 'Final'];

/** A job reference is exactly 5 letters or digits, as on the apply form. */
function validJobReference(string $ref): bool
{
    return preg_match('/^[A-Za-z0-9]{5}$/', $ref) === 1;
}

$message = '';
$messageType = 'success';
$confirmRef = '';
$confirmCount = 0;

$conn = dbConnect();
if ($conn === null) {
    $message = 'Cannot connect to the database. Check settings.php.';
    $messageType = 'error';
}

// ---------- Filter and sort values (GET) ----------
$filterRef = strtoupper(cleanInput($_GET['job_reference'] ?? ''));
$filterFirst = cleanInput($_GET['first_name'] ?? '');
$filterLast = cleanInput($_GET['last_name'] ?? '');
$sort = (isset($_GET['sort']) && is_string($_GET['sort']) && array_key_exists($_GET['sort'], $sortFields))
    ? $_GET['sort'] : 'EOInumber';
$order = (($_GET['order'] ?? '') === 'desc') ? 'desc' : 'asc';

// Keeps the current filters when a POST form sends the manager back here.
$pageUrl = 'manage.php?' . http_build_query([
    'job_reference' => $filterRef,
    'first_name'    => $filterFirst,
    'last_name'     => $filterLast,
    'sort'          => $sort,
    'order'         => $order,
]);

// ---------- Actions (POST) ----------
if ($conn !== null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $token = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if (!csrf_valid($token)) {
        $message = 'Your session has expired. Please try again.';
        $messageType = 'error';

    } elseif ($action === 'update_status') {
        $eoiNumber = (int)($_POST['eoi_number'] ?? 0);
        $newStatus = (string)($_POST['status'] ?? '');

        if ($eoiNumber > 0 && in_array($newStatus, $statuses, true)) {
            $stmt = $conn->prepare('UPDATE eoi SET status = ? WHERE EOInumber = ?');
            $stmt->bind_param('si', $newStatus, $eoiNumber);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $message = "EOI #$eoiNumber is now $newStatus.";
            } else {
                $message = "No change: EOI #$eoiNumber was not found or is already $newStatus.";
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Choose a valid EOI and status.';
            $messageType = 'error';
        }

    } elseif ($action === 'delete_request' || $action === 'delete_confirm') {
        $ref = strtoupper(cleanInput($_POST['delete_reference'] ?? ''));

        if (!validJobReference($ref)) {
            $message = 'Enter a job reference of exactly 5 letters or digits.';
            $messageType = 'error';

        } elseif ($action === 'delete_request') {
            // Step 1: count what would be deleted and ask for confirmation.
            $stmt = $conn->prepare('SELECT COUNT(*) FROM eoi WHERE job_reference = ?');
            $stmt->bind_param('s', $ref);
            $stmt->execute();
            $stmt->bind_result($confirmCount);
            $stmt->fetch();
            $stmt->close();

            if ($confirmCount > 0) {
                $confirmRef = $ref;
            } else {
                $message = "There are no EOIs for job $ref, so nothing was deleted.";
                $messageType = 'error';
            }

        } else {
            // Step 2: the manager confirmed, so delete and report the count.
            $stmt = $conn->prepare('DELETE FROM eoi WHERE job_reference = ?');
            $stmt->bind_param('s', $ref);
            $stmt->execute();
            $deleted = $stmt->affected_rows;
            $stmt->close();
            $message = "Deleted $deleted EOI" . ($deleted === 1 ? '' : 's') . " for job $ref.";
        }
    }
}

// ---------- Results ----------
$rows = [];
if ($conn !== null) {
    $conditions = [];
    $types = '';
    $params = [];

    if ($filterRef !== '') {
        $conditions[] = 'job_reference = ?';
        $types .= 's';
        $params[] = $filterRef;
    }
    if ($filterFirst !== '') {
        $conditions[] = 'first_name = ?';
        $types .= 's';
        $params[] = $filterFirst;
    }
    if ($filterLast !== '') {
        $conditions[] = 'last_name = ?';
        $types .= 's';
        $params[] = $filterLast;
    }

    $sql = "SELECT EOInumber, job_reference, first_name, last_name, email, phone,
                   CONCAT_WS(', ', skill1, skill2, skill3, skill4, skill5) AS skills, status
              FROM eoi";
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    // $sort and $order come from the allow-lists above, never straight from the user.
    $sql .= " ORDER BY $sort " . strtoupper($order) . ', EOInumber ASC';

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    } else {
        error_log('Manage query failed: ' . $conn->error);
        $message = 'The eoi table was not found. Import the SQL file first.';
        $messageType = 'error';
    }
    $conn->close();
}

$isFiltered = ($filterRef !== '' || $filterFirst !== '' || $filterLast !== '');
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Edbridge HR manager page for reviewing expressions of interest.">
  <meta name="author" content="Nguyen Quoc An | W3bb">
  <meta name="robots" content="noindex">
  <title>Manage EOIs | Edbridge</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&amp;family=Fraunces:opsz,wght@9..144,600&amp;display=swap">
  <link rel="stylesheet" href="styles/styles.css">
</head>
<body class="manage-page">
<?php include __DIR__ . '/header.inc'; ?>

  <main id="main">
    <div class="manage-title">
      <h1>Manage EOIs</h1>
      <p>Logged in as <strong><?php echo safeOutput($_SESSION[SESSION_USERNAME] ?? ''); ?></strong> &middot; <a href="logout.php">Log out</a></p>
    </div>

<?php if ($message !== ''): ?>
    <p class="manage-message manage-message-<?php echo $messageType; ?>" role="status"><?php echo safeOutput($message); ?></p>
<?php endif; ?>

<?php if ($confirmRef !== ''): ?>
    <!-- Delete step 2: confirmation without JavaScript -->
    <section class="manage-confirm" aria-labelledby="confirm-heading">
      <h2 id="confirm-heading">Confirm delete</h2>
      <p>This will permanently delete <strong><?php echo safeOutput($confirmCount); ?></strong> EOI<?php echo $confirmCount === 1 ? '' : 's'; ?> for job <strong><?php echo safeOutput($confirmRef); ?></strong>. This cannot be undone.</p>
      <form action="<?php echo safeOutput($pageUrl); ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo safeOutput($csrfToken); ?>">
        <input type="hidden" name="action" value="delete_confirm">
        <input type="hidden" name="delete_reference" value="<?php echo safeOutput($confirmRef); ?>">
        <button type="submit" class="manage-danger">Yes, delete them</button>
        <a class="manage-cancel" href="<?php echo safeOutput($pageUrl); ?>">Cancel</a>
      </form>
    </section>
<?php endif; ?>

    <div class="manage-tools">
      <!-- Filter and sort -->
      <section class="manage-panel" aria-labelledby="filter-heading">
        <h2 id="filter-heading">Find EOIs</h2>
        <form action="manage.php" method="get">
          <div class="manage-fields">
            <div>
              <label for="filter_job_reference">Job reference</label>
              <input type="text" id="filter_job_reference" name="job_reference" maxlength="5" value="<?php echo safeOutput($filterRef); ?>">
            </div>
            <div>
              <label for="filter_first_name">First name</label>
              <input type="text" id="filter_first_name" name="first_name" maxlength="20" value="<?php echo safeOutput($filterFirst); ?>">
            </div>
            <div>
              <label for="filter_last_name">Last name</label>
              <input type="text" id="filter_last_name" name="last_name" maxlength="20" value="<?php echo safeOutput($filterLast); ?>">
            </div>
            <div>
              <label for="sort">Sort by</label>
              <select id="sort" name="sort">
<?php foreach ($sortFields as $field => $label): ?>
                <option value="<?php echo $field; ?>"<?php if ($field === $sort) echo ' selected'; ?>><?php echo $label; ?></option>
<?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="order">Order</label>
              <select id="order" name="order">
                <option value="asc"<?php if ($order === 'asc') echo ' selected'; ?>>Ascending</option>
                <option value="desc"<?php if ($order === 'desc') echo ' selected'; ?>>Descending</option>
              </select>
            </div>
          </div>
          <div class="manage-actions">
            <button type="submit">Search</button>
            <a href="manage.php">Show all EOIs</a>
          </div>
        </form>
      </section>

      <!-- Delete step 1: choose a job reference -->
      <section class="manage-panel" aria-labelledby="delete-heading">
        <h2 id="delete-heading">Delete EOIs by job</h2>
        <p>Removes every EOI for one job reference. You will be asked to confirm first.</p>
        <form action="<?php echo safeOutput($pageUrl); ?>" method="post">
          <input type="hidden" name="csrf_token" value="<?php echo safeOutput($csrfToken); ?>">
          <input type="hidden" name="action" value="delete_request">
          <label for="delete_reference">Job reference</label>
          <div class="manage-actions">
            <input type="text" id="delete_reference" name="delete_reference" maxlength="5" required>
            <button type="submit" class="manage-danger">Delete&hellip;</button>
          </div>
        </form>
      </section>
    </div>

    <!-- Results -->
    <section aria-labelledby="results-heading">
      <h2 id="results-heading"><?php echo $isFiltered ? 'Matching EOIs' : 'All EOIs'; ?></h2>
      <p><?php echo count($rows); ?> EOI<?php echo count($rows) === 1 ? '' : 's'; ?> found, sorted by <?php echo safeOutput(strtolower($sortFields[$sort])); ?> (<?php echo $order === 'asc' ? 'ascending' : 'descending'; ?>).</p>

<?php if ($rows): ?>
      <div class="manage-table-scroll" role="region" aria-labelledby="results-heading" tabindex="0">
        <table class="manage-table">
          <thead>
            <tr>
              <th scope="col">EOI #</th>
              <th scope="col">Job ref</th>
              <th scope="col">First name</th>
              <th scope="col">Last name</th>
              <th scope="col">Email</th>
              <th scope="col">Phone</th>
              <th scope="col">Skills</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
<?php foreach ($rows as $row): ?>
<?php $id = safeOutput($row['EOInumber']); ?>
            <tr>
              <th scope="row"><?php echo $id; ?></th>
              <td><?php echo safeOutput($row['job_reference']); ?></td>
              <td><?php echo safeOutput($row['first_name']); ?></td>
              <td><?php echo safeOutput($row['last_name']); ?></td>
              <td><?php echo safeOutput($row['email']); ?></td>
              <td><?php echo safeOutput($row['phone']); ?></td>
              <td><?php echo safeOutput($row['skills']); ?></td>
              <td>
                <form class="manage-status" action="<?php echo safeOutput($pageUrl); ?>" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo safeOutput($csrfToken); ?>">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="eoi_number" value="<?php echo $id; ?>">
                  <label class="visually-hidden" for="status_<?php echo $id; ?>">Status for EOI <?php echo $id; ?></label>
                  <select id="status_<?php echo $id; ?>" name="status">
<?php foreach ($statuses as $status): ?>
                    <option value="<?php echo $status; ?>"<?php if ($status === $row['status']) echo ' selected'; ?>><?php echo $status; ?></option>
<?php endforeach; ?>
                  </select>
                  <button type="submit">Update</button>
                </form>
              </td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
<?php else: ?>
      <p class="manage-empty">No EOIs match these filters.</p>
<?php endif; ?>
    </section>
  </main>

<?php include __DIR__ . '/footer.inc'; ?>
</body>
</html>
