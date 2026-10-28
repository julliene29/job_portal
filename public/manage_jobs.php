
<?php
session_start();

require_once __DIR__ . '/../config/database.php';
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

// Connect to database
$database = new Database();
$db = $database->connect();

$jobModel = new Jobs($db);
$message = '';
$messageType = '';

// Delete a job
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jobId = filter_input(
        INPUT_POST,
        'job_id',
        FILTER_VALIDATE_INT
    );

    if ($jobId && $jobId > 0) {

        try {
            $deleted = $jobModel->delete($jobId);

            if ($deleted) {
                $message = 'Job deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Job could not be deleted.';
                $messageType = 'error';
            }

        } catch (PDOException $e) {
            $message = 'Unable to delete this job. It may have applications linked to it.';
            $messageType = 'error';
        }

    } else {
        $message = 'Invalid job selected.';
        $messageType = 'error';
    }
}

// Get all jobs
$jobs = $jobModel->getAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Jobs - Job Portal</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #243047;
            line-height: 1.6;
        }

        .navbar {
            background: #ffffff;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .logo {
            color: #1456c0;
            font-size: 23px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #344563;
            text-decoration: none;
            font-weight: 600;
        }

        .nav-links a:hover {
            color: #1456c0;
        }

        .hero {
            background: linear-gradient(135deg, #1456c0, #3478ed);
            color: white;
            padding: 35px 7%;
        }

        .hero h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .hero p {
            color: #e4edff;
        }

        .container {
            width: 86%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .top-actions h2 {
            color: #183b70;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border: none;
            border-radius: 7px;
            padding: 10px 15px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #1456c0;
            color: white;
        }

        .btn-primary:hover {
            background: #0d4198;
        }

        .btn-edit {
            background: #e6efff;
            color: #1456c0;
        }

        .btn-edit:hover {
            background: #cddfff;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #b82030;
        }

        .message {
            padding: 13px 16px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .success {
            background: #dff7e8;
            color: #176c3a;
            border: 1px solid #b6e8c8;
        }

        .error {
            background: #ffe4e6;
            color: #a61b29;
            border: 1px solid #ffc4ca;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 18px rgba(25, 50, 90, 0.07);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        thead {
            background: #eaf1ff;
        }

        th,
        td {
            padding: 15px 13px;
            text-align: left;
            border-bottom: 1px solid #e8edf5;
        }

        th {
            color: #214b88;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8faff;
        }

        .job-title {
            font-weight: bold;
            color: #183b70;
        }

        .job-type {
            display: inline-block;
            background: #edf3ff;
            color: #1456c0;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 35px 15px;
            color: #667085;
        }

        .empty-state h3 {
            color: #183b70;
            margin-bottom: 8px;
        }

        footer {
            text-align: center;
            color: #758198;
            padding: 25px 10px;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .container {
                width: 92%;
            }

            .hero h1 {
                font-size: 25px;
            }

            .navbar {
                padding: 16px 5%;
            }

            .table-card {
                padding: 12px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="logo">Job Portal</div>

        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="create.php">Post a Job</a>
            <a href="view_applications.php">Applications</a>
        </div>
    </nav>

    <section class="hero">
        <h1>Manage Jobs</h1>
        <p>View, edit and manage job opportunities posted on the platform.</p>
    </section>

    <main class="container">

        <div class="top-actions">
            <h2>All Job Listings</h2>

            <a href="create.php" class="btn btn-primary">
                + Post a New Job
            </a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="message <?= htmlspecialchars($messageType) ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($jobs)): ?>

            <div class="table-card empty-state">
                <h3>No Jobs Available</h3>
                <p>You have not posted any jobs yet.</p>

                <br>

                <a href="create.php" class="btn btn-primary">
                    Post Your First Job
                </a>
            </div>

        <?php else: ?>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Salary</th>
                            <th>Job Type</th>
                            <th>Posted By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td class="job-title">
                                    <?= htmlspecialchars(
                                        $job['title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        (string) $job['salary'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="job-type">
                                        <?= htmlspecialchars(
                                            $job['type'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $job['admin_name'] ?? 'Admin',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <div class="actions">
                                        <a
                                            class="btn btn-edit"
                                            href="edit_job.php?id=<?= (int) $job['id'] ?>"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="manage_jobs.php"
                                            onsubmit="return confirm('Are you sure you want to delete this job? If applications are linked to it, deletion may fail.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="job_id"
                                                value="<?= (int) $job['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-delete"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </main>

    <footer>
        &copy; <?= date('Y') ?> Job Portal. All rights reserved.
    </footer>

</body>
</html>
