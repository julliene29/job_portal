
<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/applications.php';

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

$applicationModel = new Application($db);

$message = '';
$messageType = '';

// Update application status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $applicationId = filter_input(
        INPUT_POST,
        'application_id',
        FILTER_VALIDATE_INT
    );

    $status = $_POST['status'] ?? '';

    $allowedStatuses = ['pending', 'accepted', 'rejected'];

    if (
        $applicationId &&
        $applicationId > 0 &&
        in_array($status, $allowedStatuses, true)
    ) {
        try {
            $updated = $applicationModel->updateStatus(
                $applicationId,
                $status
            );

            if ($updated) {
                $message = 'Application status updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to update application status.';
                $messageType = 'error';
            }

        } catch (PDOException $e) {
            $message = 'Failed to update application status.';
            $messageType = 'error';
        }

    } else {
        $message = 'Invalid application or status.';
        $messageType = 'error';
    }
}

// Get all applications
$applications = $applicationModel->getAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>All Applications | JobPortal</title>

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
            font-weight: bold;
            font-size: 14px;
        }

        .back-link:hover {
            color: #1d4ed8;
        }

        /* Hero section */
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
            max-width: 1250px;
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
            line-height: 1.6;
        }

        .divider {
            margin: 22px 0;
            border: none;
            border-top: 1px solid #e5e7eb;
        }

        /* Messages */
        .message {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Applications table */
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
            font-size: 13px;
            font-weight: bold;
            text-align: left;
            padding: 16px 14px;
            white-space: nowrap;
        }

        td {
            padding: 16px 14px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            vertical-align: middle;
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

        /* Update form */
        .update-form {
            min-width: 120px;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            color: #334155;
            font-size: 13px;
            outline: none;
        }

        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .update-btn {
            width: 100%;
            margin-top: 8px;
            padding: 10px 14px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .update-btn:hover {
            background: #1d4ed8;
        }

        /* Empty state */
        .empty {
            text-align: center;
            padding: 55px 20px;
        }

        .empty h3 {
            font-size: 21px;
            margin-bottom: 12px;
            color: #334155;
        }

        .empty p {
            color: #64748b;
            line-height: 1.6;
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 25px 15px;
            color: #64748b;
            font-size: 13px;
            margin-top: 30px;
        }

        /* Responsive design */
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
        <a href="index.php" class="back-link">← Back to Home</a>
    </nav>

    <header class="hero">
        <h1>All Job Applications</h1>
        <p>Manage applicants and update their application status.</p>
    </header>

    <main class="container">
        <section class="panel">

            <h2>Applications Management</h2>

            <p class="intro">
                Welcome, Admin. Review applicants and make decisions.
            </p>

            <hr class="divider">

            <?php if ($message !== ''): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($applications)): ?>

                <div class="empty">
                    <div class="empty-icon">📋</div>
                    <h3>No Applications Yet</h3>
                    <p>
                        Applications submitted by job seekers
                        will appear here.
                    </p>
                </div>

            <?php else: ?>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Applicant Name</th>
                                <th>Email</th>
                                <th>Job Title</th>
                                <th>Application Date</th>
                                <th>Current Status</th>
                                <th>Update Status</th>
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
                                            $application['name'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $application['email'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $application['title'] ?? '',
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

                                    <td>
                                        <form
                                            class="update-form"
                                            method="POST"
                                            action="view_applications.php"
                                        >

                                            <input
                                                type="hidden"
                                                name="application_id"
                                                value="<?= (int) $application['id'] ?>"
                                            >

                                            <select name="status" required>
                                                <option
                                                    value="pending"
                                                    <?= $currentStatus === 'pending' ? 'selected' : '' ?>
                                                >
                                                    Pending
                                                </option>

                                                <option
                                                    value="accepted"
                                                    <?= $currentStatus === 'accepted' ? 'selected' : '' ?>
                                                >
                                                    Accepted
                                                </option>

                                                <option
                                                    value="rejected"
                                                    <?= $currentStatus === 'rejected' ? 'selected' : '' ?>
                                                >
                                                    Rejected
                                                </option>
                                            </select>

                                            <button
                                                type="submit"
                                                class="update-btn"
                                            >
                                                Update
                                            </button>

                                        </form>
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
