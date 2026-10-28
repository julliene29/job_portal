
<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/applications.php';

// Allow logged-in job seekers only
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'job_seeker'
) {
    header('Location: index.php');
    exit;
}

// Connect to database
$database = new Database();
$db = $database->connect();

$applicationModel = new Application($db);

// Get applications belonging to the logged-in user
$applications = $applicationModel->getByUser(
    (int) $_SESSION['user_id']
);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Applications | JobPortal</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f6fc;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Navigation */
        .navbar {
            background: #ffffff;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        }

        .brand {
            color: #2563eb;
            font-size: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        .back-link {
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        .back-link:hover {
            color: #1d4ed8;
        }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white;
            text-align: center;
            padding: 45px 20px;
        }

        .hero h1 {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .hero p {
            color: #eaf1ff;
            font-size: 16px;
            line-height: 1.6;
        }

        /* Main content */
        .container {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 5px 25px rgba(15, 23, 42, 0.06);
        }

        .panel h2 {
            font-size: 23px;
            margin-bottom: 10px;
        }

        .intro {
            color: #64748b;
            line-height: 1.7;
        }

        .divider {
            margin: 22px 0;
            border: none;
            border-top: 1px solid #e5e7eb;
        }

        /* Table */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background: #eff6ff;
        }

        th {
            color: #1d4ed8;
            text-align: left;
            padding: 16px 14px;
            font-size: 13px;
            white-space: nowrap;
        }

        td {
            padding: 16px 14px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status badges */
        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status.accepted {
            background: #dcfce7;
            color: #166534;
        }

        .status.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Empty state */
        .empty {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-icon {
            font-size: 42px;
            margin-bottom: 16px;
        }

        .empty h3 {
            font-size: 21px;
            margin-bottom: 12px;
        }

        .empty p {
            color: #64748b;
            margin-bottom: 22px;
            line-height: 1.6;
        }

        .browse-btn {
            display: inline-block;
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.2s;
        }

        .browse-btn:hover {
            background: #1d4ed8;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 25px 15px;
            color: #64748b;
            font-size: 13px;
            margin-top: 30px;
        }

        /* Responsive */
        @media (max-width: 700px) {
            .navbar {
                padding: 16px 5%;
                gap: 12px;
                flex-wrap: wrap;
            }

            .brand {
                font-size: 22px;
            }

            .hero {
                padding: 35px 15px;
            }

            .hero h1 {
                font-size: 26px;
            }

            .hero p {
                font-size: 14px;
            }

            .container {
                margin: 22px auto;
                padding: 0 12px;
            }

            .panel {
                padding: 18px 14px;
            }

            .panel h2 {
                font-size: 20px;
            }

            th,
            td {
                padding: 12px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="brand">JobPortal</a>
        <a href="index.php" class="back-link">← Back to Available Jobs</a>
    </nav>

    <header class="hero">
        <h1>My Applications</h1>
        <p>Track your job applications and check their current status.</p>
    </header>

    <main class="container">
        <section class="panel">

            <h2>
                Welcome,
                <?= htmlspecialchars(
                    $_SESSION['name'] ?? 'Job Seeker',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>!
            </h2>

            <p class="intro">
                Here you can review the jobs you have applied for
                and see whether your applications are pending, accepted, or rejected.
            </p>

            <hr class="divider">

            <?php if (empty($applications)): ?>

                <div class="empty">
                    <div class="empty-icon">📋</div>

                    <h3>You Have No Applications Yet</h3>

                    <p>
                        You have not applied for any jobs.
                        Browse available opportunities and submit your first application.
                    </p>

                    <a href="index.php" class="browse-btn">
                        Browse Available Jobs
                    </a>
                </div>

            <?php else: ?>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Salary</th>
                                <th>Job Type</th>
                                <th>Application Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($applications as $application): ?>

                                <?php
                                    $currentStatus = $application['status'] ?? 'pending';

                                    $statusClass = in_array(
                                        $currentStatus,
                                        ['pending', 'accepted', 'rejected'],
                                        true
                                    ) ? $currentStatus : 'pending';
                                ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars(
                                            $application['title'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            number_format(
                                                (float) ($application['salary'] ?? 0)
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                        RWF
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $application['type'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $application['applied_at'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="status <?= $statusClass ?>">
                                            <?= htmlspecialchars(
                                                $currentStatus,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </section>
    </main>

    <footer>
        &copy; <?= date('Y') ?> JobPortal. All rights reserved.
    </footer>

</body>
</html>
