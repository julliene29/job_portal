
<?php

session_start();

require_once '../config/Database.php';
require_once '../classes/Jobss.php';

$database = new Database();
$db = $database->connect();

$jobModel = new Jobs($db);
$jobs = $jobModel->getAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
</head>
<body>

    <h1>
        <?php if (isset($_SESSION['name'])): ?>
            Welcome, <?= htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8') ?>!
        <?php else: ?>
            Welcome to Job Portal
        <?php endif; ?>
    </h1>

    <p>Find your next career opportunity.</p>

    <?php if (isset($_SESSION['user_id'])): ?>

        <p>
            <a href="logout.php">Logout</a>
        </p>

        <?php if (
            isset($_SESSION['role']) &&
            $_SESSION['role'] === 'admin'
        ): ?>

            <p>
                <a href="create.php">Post a Job</a>

                 <p>
        <a href="view_applications.php">View All Applications</a>
    </p>
        <?php endif; ?>

    <?php else: ?>

        <p>
            <a href="login.php">Login</a> |
            <a href="register.php">Register</a>
        </p>

    <?php endif; ?>

    <h2>Available Jobs</h2>

    <?php if (empty($jobs)): ?>

        <p>No jobs available yet.</p>

    <?php else: ?>

        <?php foreach ($jobs as $job): ?>

            <div>

                <h3>
                    <?= htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8') ?>
                </h3>

                <p>
                    <?= nl2br(htmlspecialchars(
                        $job['description'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) ?>
                </p>

                <p>
                    <strong>Salary:</strong>
                    <?= htmlspecialchars(
                        (string) $job['salary'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <p>
                    <strong>Job Type:</strong>
                    <?= htmlspecialchars(
                        $job['type'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <?php if ($_SESSION['role'] !== 'admin'): ?>

                        <form action="apply.php" method="POST">

                            <input
                                type="hidden"
                                name="job_id"
                                value="<?= (int) $job['id'] ?>"
                            >

                            <button type="submit">
                                Apply for Job
                            </button>

                        </form>

                    <?php endif; ?>

                <?php else: ?>

                    <p>
                        <a href="login.php">Login to Apply</a>
                    </p>

                <?php endif; ?>

                <hr>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</body>
</html>
