<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $errorMessage = "Please enter both email and password.";
    } else {
        // Check user role and fetch ID and password
        $query = $conn->prepare("
            SELECT 'student' AS role, student_id AS id, name AS name, surname AS surname, password FROM student WHERE email = ?
            UNION ALL
            SELECT 'professor' AS role, professor_id AS id, name AS name, surname AS surname, password FROM professor WHERE email = ?
            UNION ALL
            SELECT 'secretary' AS role, id AS id, name AS name, surname AS surname, password FROM secretary WHERE email = ?
        ");
        if (!$query) {
            die("Query preparation failed: " . $conn->error);
        }

        $query->bind_param("sss", $email, $email, $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $role = $user['role'];
            $name = $user['name'];
            $surname = $user['surname'];
            $userId = $user['id'];
            $storedPassword = $user['password'];

            // Verify password (raw-text comparison)
            if ($password === $storedPassword) {
                // Set session variables
                $_SESSION['role'] = $role;
                $_SESSION['user_id'] = $userId;
                $_SESSION['name'] = $name;
                $_SESSION['surname'] = $surname;

                // Set a cookie that expires in 10 minutes
                setcookie('user_session', session_id(), time() + 1200, "/", "", false, true); // Secure and HttpOnly flags are recommended.

                // Redirect based on role
                switch ($role) {
                    case 'student':
                        header("Location: ./student/student.php");
                        break;
                    case 'professor':
                        header("Location: ./professor/");
                        break;
                    case 'secretary':
                        header("Location: ./secretary/secretary.php");
                        break;
                    default:
                        $errorMessage = "Unknown role. Contact admin.";
                }
                exit();
            } else {
                $errorMessage = "Invalid password.";
            }
        } else {
            $errorMessage = "Email not found.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style_login.css">
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if (isset($errorMessage)) : ?>
            <div class="error-message">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-button">Login</button>
        </form>
    </div>
</body>
</html>


