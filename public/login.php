
<?php

session_start();

require_once '../config/Database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = 'Please enter your email and password.';
    } else {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT id, name, email, password, role
                FROM users
                WHERE email = :email
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
   

            header('Location: index.php');
            exit;

        } else {
            $message = 'Invalid email or password.';
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
</head>
<body>

    <h1>Login to Job Portal</h1>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="email">Email</label><br>
        <input
            type="email"
            id="email"
            name="email"
            required
        ><br><br>

        <label for="password">Password</label><br>
        <input
            type="password"
            id="password"
            name="password"
            required
        ><br><br>

        <button type="submit">Login</button>

    </form>

    <p><a href="index.php">Back to Jobs</a></p>

</body>
</html>
