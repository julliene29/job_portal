
<?php

session_start();

require_once '../config/database.php';
require_once '../classes/applications.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Check whether a job was selected
    if (
        !isset($_POST['job_id']) ||
        !is_numeric($_POST['job_id'])
    ) {
        $message = 'Invalid job selected.';

    // 2. Check whether the user is logged in
    } elseif (!isset($_SESSION['user_id'])) {
        $message = 'Please log in before applying for a job.';

    // 3. Only job seekers can apply
    } elseif (
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== 'job_seeker'
    ) {
        $message = 'Only job seekers can apply for jobs.';

    } else {

        $jobId = (int) $_POST['job_id'];
        $userId = (int) $_SESSION['user_id'];

        if ($jobId <= 0) {
            $message = 'Invalid job selected.';

        } else {

            try {
                // 4. Connect to the database
                $database = new Database();
                $db = $database->connect();

                // 5. Check whether the job exists
                $checkJob = $db->prepare(
                    "SELECT status, deadline
                     FROM jobss
                     WHERE id = :id"
                );

                $checkJob->execute(['id' => $jobId]);

                $job = $checkJob->fetch(PDO::FETCH_ASSOC);

                if (!$job) {
                    $message = 'This job does not exist.';

                // 6. Check job status and deadline
                } elseif (
                    $job['status'] !== 'open' ||
                    (
                        !empty($job['deadline']) &&
                        strtotime($job['deadline']) <= time()
                    )
                ) {
                    $message = 'Sorry, this job is closed.';

                } else {

                    // 7. Submit the application
                    $application = new Application($db);

                    $saved = $application->apply(
                        $userId,
                        $jobId
                    );

                    if ($saved) {
                        $message =
                            'Your application was submitted successfully!';
                    } else {
                        $message =
                            'You have already applied for this job.';
                    }
                }

            } catch (PDOException $e) {
                $message =
                    'A database error occurred. Please try again.';
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
        <p>
            <?= htmlspecialchars(
                $message,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>
    <?php endif; ?>

    <p>
        Choose a job from the available jobs page to submit your application.
    </p>

    <p>
        <a href="index.php">Back to Available Jobs</a>
    </p>

</body>

</html>
