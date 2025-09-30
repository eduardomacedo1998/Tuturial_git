<?php
session_start();
require 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    if (empty($identifier)) {
        $errors[] = 'Username or email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $username, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Authentication successful
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Invalid credentials.';
            }
        } else {
            $errors[] = 'Invalid credentials.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="" method="post">
            <label for="identifier">Username or Email</label>
            <input type="text" id="identifier" name="identifier" value="<?php echo isset($identifier) ? htmlspecialchars($identifier) : ''; ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password">

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
