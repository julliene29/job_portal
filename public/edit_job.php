
<?php

session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/jobss.php';

// Allow Admin only
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$db = $database->connect();

$jobModel = new Jobs($db);

$message = '';
$job = false;

// Get job ID from URL
$jobId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Update job when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jobId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $salaryInput = $_POST['salary'] ?? '';
    $type = $_POST['type'] ?? '';

    if (
        !$jobId ||
        $jobId <= 0 ||
        $title === '' ||
        $description === '' ||
        !is_numeric($salaryInput) ||
        !in_array($type, ['on-site', 'remote'], true)
    ) {
        $message = 'Please enter valid job information.';
    } elseif ((float) $salaryInput < 0) {
        $message = 'Salary cannot be negative.';
    } else {
        $updated = $jobModel->update(
            $jobId,
            $title,
            $description,
            (float) $salaryInput,
            $type
        );

        if ($updated) {
            header('Location: manage_jobs.php?updated=1');
            exit;
        } else {
            $message = 'Failed to update the job.';
        }
    }
}

// Load job details
if ($jobId && $jobId > 0) {
    $job = $jobModel->getById($jobId);
}

if (!$job) {
    http_response_code(404);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Job - Job Portal</title>
</head>

<body>

    <h1>Edit Job</h1>

    <p>
        <a href="manage_jobs.php">Back to Manage Jobs</a>
    </p>

    <?php if ($message !== ''): ?>
        <p>
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if (!$job): ?>

        <p>Job not found. Please select a valid job.</p>

    <?php else: ?>

        <form method="POST" action="edit_job.php">

            <input
                type="hidden"
                name="id"
                value="<?= (int) $job['id'] ?>"
            >

            <p>
                <label for="title">Job Title:</label><br>

                <input
                    type="text"
                    id="title"
                    name="title"
                    required
                    value="<?= htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8') ?>"
                >
            </p>

            <p>
                <label for="description">Description:</label><br>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    required
                ><?= htmlspecialchars($job['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </p>

            <p>
                <label for="salary">Salary:</label><br>

                <input
                    type="number"
                    id="salary"
                    name="salary"
                    min="0"
                    step="any"
                    required
                    value="<?= htmlspecialchars((string) $job['salary'], ENT_QUOTES, 'UTF-8') ?>"
                >
            </p>

            <p>
                <label for="type">Job Type:</label><br>

                <select id="type" name="type" required>

                    <option
                        value="on-site"
                        <?= $job['type'] === 'on-site' ? 'selected' : '' ?>
                    >
                        On-site
                    </option>

                    <option
                        value="remote"
                        <?= $job['type'] === 'remote' ? 'selected' : '' ?>
                    >
                        Remote
                    </option>

                </select>
            </p>

            <button type="submit">Save Changes</button>

        </form>

    <?php endif; ?>

</body>
</html>
