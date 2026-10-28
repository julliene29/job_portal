
<?php

session_start();

require_once __DIR__ . '/../config/Database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = 'Please enter your email and password.';
    } else {

        try {
            $database = new Database();
            $db = $database->connect();

            $sql = "SELECT id, name, email, password, role
                    FROM users
                    WHERE email = :email
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                $user &&
                password_verify($password, $user['password'])
            ) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                header('Location: index.php');
                exit;

            } else {
                $message = 'Invalid email or password.';
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = 'Something went wrong. Please try again later.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Job Portal</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: #f3f6fb;
            color: #243047;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: #ffffff;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .logo {
            color: #1456c0;
            font-size: 23px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-link {
            color: #1456c0;
            text-decoration: none;
            font-weight: bold;
        }

        .nav-link:hover {
            text-decoration: underline;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(25, 50, 90, 0.09);
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #e8f0ff;
            color: #1456c0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .login-header h1 {
            color: #183b70;
            font-size: 27px;
            margin-bottom: 9px;
        }

        .login-header p {
            color: #758198;
            font-size: 14px;
        }

        .message {
            background: #ffe4e6;
            color: #a61b29;
            border: 1px solid #ffc4ca;
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #344563;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d7dfeb;
            border-radius: 7px;
            outline: none;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: #1456c0;
            box-shadow: 0 0 0 3px rgba(20, 86, 192, 0.12);
        }

        .login-button {
            width: 100%;
            padding: 14px;
            background: #1456c0;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .login-button:hover {
            background: #0d4198;
        }

        .register-link {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            color: #667085;
        }

        .register-link a {
            color: #1456c0;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #758198;
            font-size: 13px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 25px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .navbar {
                padding: 16px 5%;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="logo">Job Portal</a>
        <a href="index.php" class="nav-link">Browse Jobs</a>
    </nav>

    <main class="main-content">

        <section class="login-card">

            <div class="login-header">
                <div class="login-icon">&#128274;</div>

                <h1>Welcome Back!</h1>

                <p>Login to your account to continue.</p>
            </div>

            <?php if ($message !== ''): ?>
                <div class="message" role="alert">
                    <?= htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-group">
                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars(
                            $_POST['email'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
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
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="login-button">
                    Login to Account
                </button>

            </form>

            <div class="register-link">
                Don't have an account?
                <a href="register.php">Create Account</a>
            </div>

        </section>

    </main>

    <footer>
        &copy; <?= date('Y') ?> Job Portal. All rights reserved.
    </footer>

</body>
</html>
