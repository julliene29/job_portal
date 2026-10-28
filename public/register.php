
<?php

session_start();

require_once __DIR__ . '/../config/Database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $message = 'Please fill in all fields.';
        $messageType = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';

    } elseif (strlen($password) < 8) {
        $message = 'Password must contain at least 8 characters.';
        $messageType = 'error';

    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'error';

    } else {
        try {
            $database = new Database();
            $db = $database->connect();

            $sql = "SELECT id FROM users WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $message = 'This email is already registered.';
                $messageType = 'error';

            } else {
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $sql = "INSERT INTO users (name, email, password, role)
                        VALUES (:name, :email, :password, :role)";

                $stmt = $db->prepare($sql);

                $saved = $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role' => 'job_seeker'
                ]);

                if ($saved) {
                    $message = 'Registration successful! You can now log in.';
                    $messageType = 'success';
                } else {
                    $message = 'Registration failed. Please try again.';
                    $messageType = 'error';
                }
            }

        } catch (PDOException $e) {
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

    <title>Create Account | JobPortal</title>

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

        .navbar {
            background: white;
            padding: 20px 7%;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        }

        .brand {
            color: #2563eb;
            font-size: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 45px 20px;
        }

        .register-card {
            background: white;
            width: 100%;
            max-width: 480px;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
        }

        .register-card h1 {
            font-size: 28px;
            margin-bottom: 10px;
            text-align: center;
        }

        .subtitle {
            color: #64748b;
            text-align: center;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        button {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 5px;
        }

        button:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .links {
            text-align: center;
            margin-top: 23px;
            font-size: 14px;
            line-height: 2;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 500px) {
            .register-card {
                padding: 25px 20px;
            }

            .register-card h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="brand">JobPortal</a>
    </nav>

    <main class="main">
        <section class="register-card">

            <h1>Create Your Account</h1>

            <p class="subtitle">
                Join JobPortal and take the next step in your career.
            </p>

            <?php if ($message !== ''): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="At least 8 characters"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Enter your password again"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit">Create Account</button>

            </form>

            <div class="links">
                <p>Already have an account?
                    <a href="login.php">Login here</a>
                </p>

                <p>
                    <a href="index.php">← Back to Available Jobs</a>
                </p>
            </div>

        </section>
    </main>

    <footer>
        &copy; <?= date('Y') ?> JobPortal. All rights reserved.
    </footer>

</body>
</html>
