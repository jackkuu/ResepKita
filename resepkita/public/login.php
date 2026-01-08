<?php
session_start();

$error = null;
$success = null;

// Login form: accepts email and password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $mysqli = new mysqli('localhost', 'root', '', 'resepkita');
        if ($mysqli->connect_errno) {
            $error = 'Database connection failed: ' . $mysqli->connect_error;
        } else {
            // ensure users table exists with email and password columns
            $create = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                is_premium TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $mysqli->query($create);

            // Check if email exists and password matches
            $stmt = $mysqli->prepare('SELECT id, name, password, is_premium FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $name, $hashed_password, $is_premium);
                $stmt->fetch();
                $stmt->close();

                // Verify password
                if (password_verify($password, $hashed_password)) {
                    // Login successful
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['is_premium'] = $is_premium;

                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Email atau password salah.';
                }
            } else {
                $stmt->close();
                $error = 'Email atau password salah.';
            }

            $mysqli->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Login — ResepKita</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        a{
            text-decoration: none;
            color: #2563eb;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .error {
            color: #b91c1c;
            margin-bottom: 12px;
            padding: 10px;
            background: #fee2e2;
            border-radius: 4px;
        }
        .success {
            color: #15803d;
            margin-bottom: 12px;
            padding: 10px;
            background: #dcfce7;
            border-radius: 4px;
        }
        .buttons {
            margin-top: 12px;
        }
        .buttons button {
            padding: 10px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        .buttons button:hover {
            background: #1d4ed8;
        }
        .register-link {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }
        .register-link a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width:480px;margin:40px auto;">
        <h2>Login</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="email@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>

            <div class="buttons">
                <button type="submit">Login</button>
                <a href="index.php" style="margin-left:12px;">Batal</a>
            </div>

            <div class="register-link">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </div>
        </form>
    </div>
</body>
</html>
