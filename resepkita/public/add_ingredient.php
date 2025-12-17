<?php
// Halaman untuk menambahkan ingredient
$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("DB error: " . $mysqli->connect_error);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    if ($name === '') {
        $message = 'Nama bahan tidak boleh kosong.';
    } else {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO ingredients (name) VALUES (?)");
        $stmt->bind_param('s', $name);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = 'Bahan berhasil ditambahkan.';
            } else {
                $message = 'Bahan sudah ada (tidak ditambahkan).';
            }
        } else {
            $message = 'Gagal menambahkan bahan: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tambah Ingredient — ResepKita</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <div class="container">
            <div class="brand">
                <a href="index.php"><img src="img/logo.png" class="logo-img" alt="ResepKita"></a>
                <div>
                    <a href="index.php" style="display:block;text-decoration:none;color:inherit;"><strong>ResepKita</strong></a>
                    <div style="font-size:12px;color:#6b7280;">Simpan & Temukan Resep</div>
                </div>
            </div>

            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="nutrition_filter.php">Filter Nutrisi</a>
                <a href="add_ingredient.php" class="active">Tambah Ingredient</a>
                <a href="add_recipe.php">Tambah Resep</a>
                <a href="manage_ingredients.php">Kelola Bahan</a>
                <a href="manage_recipes.php">Kelola Resep</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Tambah Ingredient</h1>

        <?php if ($message): ?>
            <p class="message <?php echo strpos($message, 'Gagal') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="post" action="">
            <label>Nama Bahan
                <input type="text" name="name" required>
            </label>
            <button type="submit">Simpan Bahan</button>
        </form>
    </div>
</body>
</html>