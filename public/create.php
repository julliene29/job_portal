
<?php

session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/jobss.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $salary = $_POST['salary'] ?? '';
    $type = $_POST['type'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $postedDate = $_POST['posted_date'] ?? '';

    if (
        $title === '' ||
        $description === '' ||
        $salary === '' ||
        !is_numeric($salary) ||
        !in_array($type, ['on-site', 'remote'], true) ||
        $deadline === '' ||
        $postedDate === ''
    ) {
        $message = 'Please enter valid information in all fields.';
        $messageType = 'error';

    } elseif ((float) $salary < 0) {
        $message = 'Salary cannot be negative.';
        $messageType = 'error';

    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $postedDate) ||
        !checkdate(
            (int) substr($deadline, 5, 2),
            (int) substr($deadline, 8, 2),
            (int) substr($deadline, 0, 4)
        ) ||
        !checkdate(
            (int) substr($postedDate, 5, 2),
            (int) substr($postedDate, 8, 2),
            (int) substr($postedDate, 0, 4)
        )
    ) {
        $message = 'Please enter valid dates.';
        $messageType = 'error';

    } elseif ($deadline < date('Y-m-d')) {
        $message = 'Application deadline cannot be in the past.';
        $messageType = 'error';

    } elseif ($postedDate > date('Y-m-d')) {
        $message = 'Posted date cannot be in the future.';
        $messageType = 'error';

    } elseif ($deadline < $postedDate) {
        $message = 'Application deadline cannot be before the posted date.';
        $messageType = 'error';

    } else {
        try {
            $database = new Database();
            $db = $database->connect();

            $jobModel = new Jobs($db);

            $createdBy = (int) $_SESSION['user_id'];

            $success = $jobModel->create(
                $title,
                $description,
                (float) $salary,
                $type,
                $createdBy,
                $deadline
            );

            if ($success) {
                $message = 'Job created successfully!';
                $messageType = 'success';
            } else {
                $message = 'Unable to create the job.';
                $messageType = 'error';
            }

        } catch (PDOException $e) {
            // Log technical details privately rather than showing them to visitors.
            error_log($e->getMessage());

            $message = 'A database error occurred. Please try again.';
            $messageType = 'error';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Job | JobPortal</title>

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

        .nav-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        .nav-link:hover {
            color: #1d4ed8;
        }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white;
            text-align: center;
            padding: 42px 20px;
        }

        .hero h1 {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .hero p {
            color: #eaf1ff;
            font-size: 15px;
            line-height: 1.6;
        }

        /* Form container */
        .container {
            max-width: 800px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .form-card {
            background: white;
            padding: 32px;
            border-radius: 14px;
            box-shadow: 0 5px 25px rgba(15, 23, 42, 0.06);
        }

        .form-card h2 {
            font-size: 23px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        /* Form fields */
        .form-group {
            margin-bottom: 21px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 9px;
            color: #334155;
        }

        input,
        textarea,
        select {
            display: block;
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #1e293b;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
            line-height: 1.6;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .help-text {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-top: 7px;
        }

        /* Messages */
        .message {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 22px;
            font-size: 14px;
            line-height: 1.6;
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

        /* Buttons */
        .button-row {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .save-btn {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .save-btn:hover {
            background: #1d4ed8;
        }

        .cancel-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 14px 20px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        .cancel-btn:hover {
            background: #f8fafc;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 25px 15px;
            color: #64748b;
            font-size: 13px;
            margin-top: 25px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .navbar {
                padding: 16px 5%;
                gap: 12px;
                flex-wrap: wrap;
            }

            .brand {
                font-size: 22px;
            }

            .hero {
                padding: 32px 15px;
            }

            .hero h1 {
                font-size: 26px;
            }

            .container {
                margin: 22px auto;
                padding: 0 12px;
            }

            .form-card {
                padding: 22px 18px;
            }

            .form-card h2 {
                font-size: 21px;
            }

            .field-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .button-row {
                flex-direction: column;
            }

            .save-btn,
            .cancel-btn {
                width: 100%;
                min-height: 46px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="brand">JobPortal</a>
        <a href="index.php" class="nav-link">← Back to Jobs</a>
    </nav>

    <header class="hero">
        <h1>Create a New Job</h1>
        <p>Publish a job opportunity and help candidates find their next career.</p>
    </header>

    <main class="container">
        <section class="form-card">

            <h2>Job Information</h2>

            <p class="subtitle">
                Fill in the details below to publish a new job opportunity.
                Fields marked with * are required.
            </p>

            <?php if ($message !== ''): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-group">
                    <label for="title">Job Title *</label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="e.g. IT Support Engineer"
                        value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">Job Description *</label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Describe the responsibilities, qualifications and requirements..."
                        required
                    ><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

                    <span class="help-text">
                        Include the main duties and qualifications required for this position.
                    </span>
                </div>

                <div class="field-row">

                    <div class="form-group">
                        <label for="salary">Salary (RWF) *</label>

                        <input
                            type="number"
                            id="salary"
                            name="salary"
                            placeholder="e.g. 250000"
                            min="0"
                            step="0.01"
                            value="<?= htmlspecialchars($_POST['salary'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="type">Job Type *</label>

                        <select id="type" name="type" required>
                            <option value="">Select job type</option>

                            <option
                                value="on-site"
                                <?= ($_POST['type'] ?? '') === 'on-site' ? 'selected' : '' ?>
                            >
                                On-site
                            </option>

                            <option
                                value="remote"
                                <?= ($_POST['type'] ?? '') === 'remote' ? 'selected' : '' ?>
                            >
                                Remote
                            </option>
                        </select>
                    </div>

                </div>

                <div class="field-row">

                    <div class="form-group">
                        <label for="posted_date">Posted Date *</label>

                        <input
                            type="date"
                            id="posted_date"
                            name="posted_date"
                            value="<?= htmlspecialchars(
                                $_POST['posted_date'] ?? date('Y-m-d'),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            max="<?= date('Y-m-d') ?>"
                            required
                        >

                        <span class="help-text">
                            The date this job is published.
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Application Deadline *</label>

                        <input
                            type="date"
                            id="deadline"
                            name="deadline"
                            value="<?= htmlspecialchars(
                                $_POST['deadline'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            min="<?= date('Y-m-d') ?>"
                            required
                        >

                        <span class="help-text">
                            The last date candidates can apply.
                        </span>
                    </div>

                </div>

                <div class="button-row">
                    <button type="submit" class="save-btn">
                        Save Job
                    </button>

                    <a href="index.php" class="cancel-btn">
                        Cancel
                    </a>
                </div>

            </form>

        </section>
    </main>

    <footer>
        &copy; <?= date('Y') ?> JobPortal. All rights reserved.
    </footer>

</body>
</html>
