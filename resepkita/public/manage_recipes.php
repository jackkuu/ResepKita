<?php
$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$message = '';

// Handle delete
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $mysqli->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $message = 'Resep berhasil dihapus.';
    } else {
        $message = 'Gagal menghapus resep.';
    }
    $stmt->close();
}

// Get all recipes with their ingredients
$recipes = [];
$res = $mysqli->query("
    SELECT r.*, GROUP_CONCAT(i.name) as ingredient_names
    FROM recipes r
    LEFT JOIN recipe_ingredients ri ON r.id = ri.recipe_id
    LEFT JOIN ingredients i ON ri.ingredient_id = i.id
    GROUP BY r.id
    ORDER BY r.title
");

while ($row = $res->fetch_assoc()) {
    $recipes[] = $row;
}
$res->free();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kelola Resep — ResepKita</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .recipe-card { 
            margin-bottom: 20px; 
            padding: 10px; 
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .recipe-card:hover { background: #f9f9f9; }
        .recipe-title { font-size: 1.2em; font-weight: bold; }
        .recipe-ingredients { color: #666; margin: 5px 0; }
        .recipe-instructions { margin-top: 10px; }
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
            <a href="manage_ingredients.php">Kelola Bahan</a>
            <a href="manage_recipes.php" class="active">Kelola Resep</a>
        </div>
    </nav>
        <div class="container">
    <h1>Kelola Resep</h1>
    
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (empty($recipes)): ?>
        <p>Belum ada resep tersimpan.</p>
    <?php else: ?>
        <?php foreach ($recipes as $recipe): ?>
            <div class="recipe-card">
                <div class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></div>
                <div class="recipe-ingredients">
                    <strong>Bahan:</strong> <?php echo htmlspecialchars($recipe['ingredients']); ?>
                </div>
                <?php if ($recipe['instructions']): ?>
                    <div class="recipe-instructions">
                        <strong>Instruksi:</strong><br>
                        <?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?>
                    </div>
                <?php endif; ?>
                <div class="actions" style="margin-top:10px;">
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $recipe['id']; ?>">
                        <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="button">Edit</a>
                        <button type="submit" name="delete" onclick="return confirm('Yakin hapus resep ini?');">Hapus</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>