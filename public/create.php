
<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/Database.php';
require_once '../classes/jobss.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $salary = $_POST['salary'] ?? '';
    $type = $_POST['type'] ?? '';

    if (
        $title === '' ||
        $description === '' ||
        $salary === '' ||
        !is_numeric($salary) ||
        !in_array($type, ['on-site', 'remote'], true)
    ) {
        $message = 'Please enter valid information in all fields.';
    } 
    
    elseif ((float) $salary < 0) {
        $message = 'Salary cannot be negative.';
    } else {
        $database = new Database();
        $db = $database->connect();

        $jobModel = new Jobs($db);

        // Temporary admin ID for testing.
        $createdBy = $_SESSION['user_id'] ?? 1;

        try {
            $success = $jobModel->create(
                $title,
                $description,
                (float) $salary,
                $type,
                (int) $createdBy
            );

            $message = $success
                ? 'Job created successfully!'
                : 'Unable to create the job.';
       } catch (PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
}
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job - Job Portal</title>
</head>
<body>

    <h1>Create a New Job</h1>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="title">Job Title</label><br>
        <input
            type="text"
            id="title"
            name="title"
            required
        ><br><br>

        <label for="description">Job Description</label><br>
        <textarea
            id="description"
            name="description"
            required
        ></textarea><br><br>

        <label for="salary">Salary</label><br>
        <input
            type="number"
            id="salary"
            name="salary"
            min="0"
            step="0.01"
            required
        ><br><br>

        <label for="type">Job Type</label><br>
        <select id="type" name="type" required>
            <option value="">Select job type</option>
            <option value="on-site">On-site</option>
            <option value="remote">Remote</option>
        </select><br><br>

        <button type="submit">Save Job</button>

    </form>

    <p><a href="index.php">View Available Jobs</a></p>

</body>
</html>
