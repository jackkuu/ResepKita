<?php
session_start();

$error = null;
$success = null;

// Registration form: name, email, password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $password_confirm) {
        $error = 'Password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $mysqli = new mysqli('localhost', 'root', '', 'resepkita');
        if ($mysqli->connect_errno) {
            $error = 'Database connection failed: ' . $mysqli->connect_error;
        } else {
            // ensure users table exists
            $create = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                is_premium TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $mysqli->query($create);

            // Check if email already exists
            $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Email sudah terdaftar.';
                $stmt->close();
            } else {
                $stmt->close();

                // Hash password and insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $ins = $mysqli->prepare('INSERT INTO users (name, email, password, is_premium) VALUES (?, ?, ?, 0)');
                $ins->bind_param('sss', $name, $email, $hashed_password);

                if ($ins->execute()) {
                    $id = $ins->insert_id;
                    $ins->close();

                    // Auto-login after registration
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['is_premium'] = 0;

                    $mysqli->close();

                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Gagal mendaftar: ' . $ins->error;
                    $ins->close();
                }
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
    <title>Daftar — ResepKita</title>
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
            box-sizing: border-box;
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
        .login-link {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }
        .login-link a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width:480px;margin:40px auto;">
        <h2>Daftar Akun Baru</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" placeholder="Nama Anda" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="email@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="Ketik ulang password" required>
            </div>

            <div class="buttons">
                <button type="submit">Daftar</button>
                <a href="index.php" style="margin-left:12px;">Batal</a>
            </div>

            <div class="login-link">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </form>
    </div>
</body>
</html>
