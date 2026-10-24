
<?php

require_once '../config/Database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $message = 'Please fill in all fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';

    } elseif (strlen($password) < 8) {
        $message = 'Password must contain at least 8 characters.';

    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';

    } else {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            $message = 'This email is already registered.';

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
            } else {
                $message = 'Registration failed. Please try again.';
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
    <title>Register - Job Portal</title>
</head>
<body>

    <h1>Create Your Account</h1>

    <?php if ($message !== ''): ?>
        <p>
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="name">Full Name</label><br>
        <input type="text" id="name" name="name" required>
        <br><br>

        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required>
        <br><br>

        <label for="password">Password</label><br>
        <input type="password" id="password" name="password"
               minlength="8" required>
        <br><br>

        <label for="confirm_password">Confirm Password</label><br>
        <input type="password" id="confirm_password"
               name="confirm_password" minlength="8" required>
        <br><br>

        <button type="submit">Register</button>

    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
    <p><a href="index.php">Back to Jobs</a></p>

</body>
</html>
