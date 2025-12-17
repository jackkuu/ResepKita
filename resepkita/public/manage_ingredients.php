<?php
$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$message = '';

// Handle delete
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $mysqli->prepare("DELETE FROM ingredients WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $message = 'Bahan berhasil dihapus.';
    } else {
        $message = 'Gagal menghapus bahan.';
    }
    $stmt->close();
}

// Handle edit
if (isset($_POST['edit']) && isset($_POST['id']) && isset($_POST['name'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $mysqli->prepare("UPDATE ingredients SET name = ? WHERE id = ?");
        $stmt->bind_param('si', $name, $id);
        if ($stmt->execute()) {
            $message = 'Bahan berhasil diupdate.';
        } else {
            $message = 'Gagal mengupdate bahan.';
        }
        $stmt->close();
    }
}

// Get all ingredients
$ingredients = [];
$res = $mysqli->query("SELECT id, name FROM ingredients ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $ingredients[] = $row;
}
$res->free();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kelola Bahan — ResepKita</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ingredient-row { margin-bottom: 10px; padding: 5px; }
        .ingredient-row:hover { background: #f5f5f5; }
        .actions { display: inline-block; margin-left: 10px; }
    </style>
</head>
<body>
    <style>
        a{
            text-decoration: none;
            margin-right: 15px;
            color: #333;
        }
    </style>
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
            <a href="add_ingredient.php">Tambah Ingredient</a>
            <a href="add_recipe.php">Tambah Resep</a>
            <a href="manage_ingredients.php"  class="active">Kelola Bahan</a>
            <a href="manage_recipes.php" >Kelola Resep</a>
    </nav>
    <div class="container">
    <h1>Kelola Bahan</h1>
    
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (empty($ingredients)): ?>
        <p>Belum ada bahan tersimpan.</p>
    <?php else: ?>
        <?php foreach ($ingredients as $ing): ?>
            <div class="ingredient-row" id="ing-<?php echo $ing['id']; ?>">
                <form method="post" action="" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $ing['id']; ?>">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($ing['name']); ?>">
                    <div class="actions">
                        <button type="submit" name="edit">Update</button>
                        <button type="submit" name="delete" onclick="return confirm('Yakin hapus bahan ini?');">Hapus</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>