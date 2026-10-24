
<?php

session_start();

require_once '../config/Database.php';
require_once '../classes/Applications.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['job_id']) || !is_numeric($_POST['job_id'])) {
        $message = 'Invalid job selected.';
    } elseif (!isset($_SESSION['user_id'])) {
        $message = 'Please log in before applying for a job.';
    } else {
        $jobId = (int) $_POST['job_id'];
        $userId = (int) $_SESSION['user_id'];

        if ($jobId <= 0) {
            $message = 'Invalid job selected.';
        } else {
            $database = new Database();
            $db = $database->connect();

            $application = new Application($db);

            try {
                $saved = $application->apply($userId, $jobId);

                $message = $saved
                    ? 'Your application was submitted successfully!'
                    : 'You have already applied for this job.';
            } catch (PDOException $e) {
               $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job - Job Portal</title>
</head>
<body>

    <h1>Apply for Job</h1>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <p>Choose a job from the available jobs page to submit your application.</p>

    <p><a href="index.php">Back to Available Jobs</a></p>

</body>
</html>
