
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

// Connect to database
$database = new Database();
$db = $database->connect();

$applicationModel = new Application($db);

$message = '';

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

            $message = $updated
                ? 'Application status updated successfully!'
                : 'Failed to update application status.';

        } catch (PDOException $e) {
            $message = 'Failed to update application status.';
        }

    } else {
        $message = 'Invalid application or status.';
    }
}

// Get all applications
$applications = $applicationModel->getAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>View All Applications - Job Portal</title>
</head>

<body>

    <h1>All Job Applications</h1>

    <p>
        Welcome, Admin
    </p>

    <p>
        <a href="index.php">Back to Home</a>
    </p>

    <hr>

    <?php if ($message !== ''): ?>
        <p>
            <?= htmlspecialchars(
                $message,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($applications)): ?>

        <p>No applications have been submitted yet.</p>

    <?php else: ?>

        <table border="1" cellpadding="10" cellspacing="0">

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

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $application['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $application['email'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $application['title'],
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
                            <?= htmlspecialchars(
                                $application['status'] ?? 'pending',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>

                            <form
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
                                        <?= ($application['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>
                                    >
                                        Pending
                                    </option>

                                    <option
                                        value="accepted"
                                        <?= ($application['status'] ?? '') === 'accepted' ? 'selected' : '' ?>
                                    >
                                        Accepted
                                    </option>

                                    <option
                                        value="rejected"
                                        <?= ($application['status'] ?? '') === 'rejected' ? 'selected' : '' ?>
                                    >
                                        Rejected
                                    </option>

                                </select>

                                <button type="submit">
                                    Update
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

    <hr>

    <p>
        <a href="index.php">Back to Job Portal</a>
    </p>

</body>
</html>
