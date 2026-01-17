<?php
// backend/make_admin.php
include 'config/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    try {
        $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $message = "<p style='color:green'>✅ Success! User <strong>$email</strong> is now an Admin.</p>";
        } else {
            $message = "<p style='color:orange'>⚠️ User not found or already an admin.</p>";
        }
    } catch (Throwable $e) {
        $message = "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Make Admin</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.5;
        }

        input {
            padding: 0.5rem;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 1rem;
        }

        button {
            padding: 0.75rem 1.5rem;
            background: #4F46E5;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background: #4338ca;
        }
    </style>
</head>

<body>
    <h1>👑 Promote to Admin</h1>
    <p>Enter the email address of the user you want to make an Administrator.</p>

    <?= $message ?>

    <form method="POST">
        <label>User Email:</label>
        <input type="email" name="email" placeholder="e.g. your.email@example.com" required>
        <button type="submit">Make Admin</button>
    </form>

    <p style="margin-top: 2rem; font-size: 0.9rem; color: #666;">
        <a href="../frontend/login.html">Go to Login</a> |
        <a href="../frontend/admin.html">Go to Admin Panel</a>
    </p>
</body>

</html>