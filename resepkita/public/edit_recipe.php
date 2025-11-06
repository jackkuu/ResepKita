<?php

$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$message = '';
$recipe = null;
$recipe_ingredients = [];

// Get recipe data
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare("SELECT * FROM recipes WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $recipe = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($recipe) {
        // Get recipe ingredients
        $stmt = $mysqli->prepare("
            SELECT i.id, i.name, ri.quantity 
            FROM recipe_ingredients ri
            JOIN ingredients i ON ri.ingredient_id = i.id
            WHERE ri.recipe_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recipe_ingredients[$row['id']] = $row;
        }
        $stmt->close();
    }
}

// Get all ingredients for selection
$all_ingredients = [];
$res = $mysqli->query("SELECT id, name FROM ingredients ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $all_ingredients[] = $row;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $instructions = trim($_POST['instructions']);
    $ingredient_ids = isset($_POST['ingredient_ids']) ? $_POST['ingredient_ids'] : [];
    $quantities = isset($_POST['quantity']) ? $_POST['quantity'] : [];

    if ($title === '') {
        $message = 'Judul resep diperlukan.';
    } else {
        $mysqli->begin_transaction();
        try {
            // Update recipe
            $stmt = $mysqli->prepare("UPDATE recipes SET title = ?, instructions = ? WHERE id = ?");
            $stmt->bind_param('ssi', $title, $instructions, $id);
            $stmt->execute();
            $stmt->close();

            // Delete old ingredients
            $stmt = $mysqli->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            // Insert new ingredients
            if (!empty($ingredient_ids)) {
                $stmt = $mysqli->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES (?, ?, ?)");
                foreach ($ingredient_ids as $ing_id) {
                    $qty = isset($quantities[$ing_id]) ? trim($quantities[$ing_id]) : null;
                    $stmt->bind_param('iis', $id, $ing_id, $qty);
                    $stmt->execute();
                }
                $stmt->close();
            }

            // Update ingredients display field
            $names = [];
            if (!empty($ingredient_ids)) {
                $in = implode(',', array_fill(0, count($ingredient_ids), '?'));
                $sql = "SELECT name FROM ingredients WHERE id IN ($in)";
                $s = $mysqli->prepare($sql);
                $types = str_repeat('i', count($ingredient_ids));
                $bind = array($types);
                foreach ($ingredient_ids as $key => $value) {
                    $bind[] = &$ingredient_ids[$key];
                }
                call_user_func_array(array($s, 'bind_param'), $bind);
                $s->execute();
                $r2 = $s->get_result();
                while ($row = $r2->fetch_assoc()) {
                    $names[] = $row['name'];
                }
                $s->close();
            }
            $ingredients_display = implode(', ', $names);
            
            $stmt = $mysqli->prepare("UPDATE recipes SET ingredients = ? WHERE id = ?");
            $stmt->bind_param('si', $ingredients_display, $id);
            $stmt->execute();
            $stmt->close();

            $mysqli->commit();
            $message = 'Resep berhasil diupdate.';
            
            // Refresh recipe data
            $stmt = $mysqli->prepare("SELECT * FROM recipes WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $recipe = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Refresh recipe ingredients
            $recipe_ingredients = [];
            $stmt = $mysqli->prepare("
                SELECT i.id, i.name, ri.quantity 
                FROM recipe_ingredients ri
                JOIN ingredients i ON ri.ingredient_id = i.id
                WHERE ri.recipe_id = ?
            ");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $recipe_ingredients[$row['id']] = $row;
            }
            $stmt->close();

        } catch (Exception $e) {
            $mysqli->rollback();
            $message = 'Gagal mengupdate resep: ' . $e->getMessage();
        }
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Edit Resep — ResepKita</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ingredient-row { margin-bottom:6px; }
        .ingredient-row input.qty { width:100px; margin-left:8px; }
    </style>
</head>
<body>
    <nav>
        <div class="container">
            <div class="brand">
                <a href="index.php"><img src="img/logo.png" alt="ResepKita" class="logo-img"></a>
                <div>
                    <a href="index.php" style="display:block;text-decoration:none;color:inherit;"><strong>ResepKita</strong></a>
                    <div style="font-size:12px;color:#6b7280;">Simpan & Temukan Resep</div>
                </div>
            </div>

            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="add_ingredient.php">Tambah Ingredient</a>
                <a href="add_recipe.php">Tambah Resep</a>
                <a href="manage_ingredients.php">Kelola Bahan</a>
                <a href="manage_recipes.php" class="active">Kelola Resep</a>
            </div>
        </div>
    </nav>

    <div class="container">
    <h1>Edit Resep</h1>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($recipe): ?>
        <form method="post" action="">
            <input type="hidden" name="id" value="<?php echo $recipe['id']; ?>">
            
            <label>Judul Resep<br>
                <input type="text" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
            </label><br><br>

            <label>Instruksi<br>
                <textarea name="instructions" rows="6" cols="60"><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
            </label><br><br>

            <fieldset>
                <legend>Pilih bahan</legend>
                <?php if (empty($all_ingredients)): ?>
                    <p>Tidak ada bahan tersedia.</p>
                <?php else: ?>
                    <?php foreach ($all_ingredients as $ing): ?>
                        <div class="ingredient-row">
                            <label>
                                <input type="checkbox" name="ingredient_ids[]" 
                                       value="<?php echo $ing['id']; ?>"
                                       <?php echo isset($recipe_ingredients[$ing['id']]) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($ing['name']); ?>
                            </label>
                            <input class="qty" type="text" 
                                   name="quantity[<?php echo $ing['id']; ?>]" 
                                   value="<?php echo isset($recipe_ingredients[$ing['id']]) ? htmlspecialchars($recipe_ingredients[$ing['id']]['quantity']) : ''; ?>"
                                   placeholder="opsional">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </fieldset>

            <br>
            <button type="submit" name="update">Update Resep</button>
            <a href="manage_recipes.php" class="button">Kembali</a>
        </form>
    <?php else: ?>
        <p>Resep tidak ditemukan.</p>
        <a href="manage_recipes.php" class="button">Kembali</a>
    <?php endif; ?>
</body>
</html>