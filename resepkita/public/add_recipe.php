<?php
// Halaman untuk menambahkan resep dan mengaitkan bahan
$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("DB error: " . $mysqli->connect_error);
}

$message = '';

// Fungsi untuk handle upload gambar
function handleImageUpload($file_array) {
    if (empty($file_array) || $file_array['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file_array['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload error code: " . $file_array['error']);
    }
    $tmp = $file_array['tmp_name'];
    $info = getimagesize($tmp);
    if ($info === false) {
        throw new Exception('File bukan gambar yang valid.');
    }
    $max_size = 5 * 1024 * 1024;
    if ($file_array['size'] > $max_size) {
        throw new Exception('Ukuran file terlalu besar (maksimal 5MB).');
    }
    $ext = image_type_to_extension($info[2]);
    $filename = bin2hex(random_bytes(8)) . $ext;
    $uploads_dir = __DIR__ . '/uploads';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }
    $destination = $uploads_dir . '/' . $filename;
    if (!move_uploaded_file($tmp, $destination)) {
        throw new Exception('Gagal menyimpan file gambar.');
    }
    return 'uploads/' . $filename;
}

// ambil semua ingredient untuk form
$ings = [];
$res = $mysqli->query("SELECT id, name FROM ingredients ORDER BY name ASC");
while ($r = $res->fetch_assoc()) { $ings[] = $r; }
$res->free();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $instructions = isset($_POST['instructions']) ? trim($_POST['instructions']) : '';
    $ingredient_ids = isset($_POST['ingredient_ids']) ? $_POST['ingredient_ids'] : [];
    $quantities = isset($_POST['quantity']) ? $_POST['quantity'] : [];
    $new_ings_raw = isset($_POST['new_ingredients']) ? trim($_POST['new_ingredients']) : '';

    if ($title === '') {
        $message = 'Judul resep diperlukan.';
    } else {
        $mysqli->begin_transaction();
        try {
            // Handle image upload
            $image_path = null;
            if (!empty($_FILES['image'])) {
                $image_path = handleImageUpload($_FILES['image']);
            }

            // handle new ingredients
            $new_ids = [];
            if ($new_ings_raw !== '') {
                $parts = array_filter(array_map('trim', explode(',', $new_ings_raw)));
                $insStmt = $mysqli->prepare("INSERT IGNORE INTO ingredients (name) VALUES (?)");
                foreach ($parts as $p) {
                    if ($p === '') continue;
                    $insStmt->bind_param('s', $p);
                    $insStmt->execute();
                }
                $insStmt->close();
            }

            // build ingredients display string (names)
            $names = [];
            if (!empty($ingredient_ids)) {
                $in = implode(',', array_fill(0, count($ingredient_ids), '?'));
                $types = str_repeat('i', count($ingredient_ids));
                $sql = "SELECT name FROM ingredients WHERE id IN ($in)";
                $s = $mysqli->prepare($sql);
                $bind = [];
                $bind[] = & $types;
                for ($i=0;$i<count($ingredient_ids);$i++) {
                    $bind[] = & $ingredient_ids[$i];
                }
                call_user_func_array(array($s, 'bind_param'), $bind);
                $s->execute();
                $r2 = $s->get_result();
                while ($row = $r2->fetch_assoc()) { $names[] = $row['name']; }
                $s->close();
            }
            if ($new_ings_raw !== '') {
                $parts = array_filter(array_map('trim', explode(',', $new_ings_raw)));
                foreach ($parts as $p) if ($p !== '') $names[] = $p;
            }
            $ingredients_display = implode(', ', $names);

            // INSERT dengan kolom image_path
            $stmt = $mysqli->prepare("INSERT INTO recipes (title, ingredients, instructions, image_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $title, $ingredients_display, $instructions, $image_path);
            $stmt->execute();
            $recipe_id = $mysqli->insert_id;
            $stmt->close();

            // ensure new ingredients exist and get ids for all selected + new
            $all_ing_ids = [];
            foreach ($ingredient_ids as $iid) $all_ing_ids[] = (int)$iid;
            if ($new_ings_raw !== '') {
                $parts = array_filter(array_map('trim', explode(',', $new_ings_raw)));
                $ins = $mysqli->prepare("INSERT IGNORE INTO ingredients (name) VALUES (?)");
                $sel = $mysqli->prepare("SELECT id FROM ingredients WHERE name = ?");
                foreach ($parts as $p) {
                    if ($p === '') continue;
                    $ins->bind_param('s', $p);
                    $ins->execute();
                    $sel->bind_param('s', $p);
                    $sel->execute();
                    $resid = $sel->get_result()->fetch_assoc();
                    if ($resid && isset($resid['id'])) $all_ing_ids[] = (int)$resid['id'];
                }
                $ins->close();
                $sel->close();
            }

            // insert into recipe_ingredients
            if (!empty($all_ing_ids)) {
                $riStmt = $mysqli->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES (?, ?, ?)");
                foreach ($all_ing_ids as $iid) {
                    $qty = isset($quantities[$iid]) ? trim($quantities[$iid]) : null;
                    $riStmt->bind_param('iis', $recipe_id, $iid, $qty);
                    $riStmt->execute();
                }
                $riStmt->close();
            }

            $mysqli->commit();
            $message = 'Resep berhasil disimpan.';
            $ings = [];
            $res = $mysqli->query("SELECT id, name FROM ingredients ORDER BY name ASC");
            while ($r = $res->fetch_assoc()) { $ings[] = $r; }
            $res->free();
        } catch (Exception $e) {
            $mysqli->rollback();
            $message = 'Gagal menyimpan resep: ' . $e->getMessage();
        }
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tambah Resep — ResepKita</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ingredient-row { margin-bottom:6px; }
        .ingredient-row input.qty { width:100px; margin-left:8px; }
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
            <a href="add_recipe.php" class="active">Tambah Resep</a>
            <a href="manage_ingredients.php">Kelola Bahan</a>
            <a href="manage_recipes.php">Kelola Resep</a>
    </nav>
    <div class="container">
    <h1>Tambah Resep</h1>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data">
        <label>Judul Resep<br>
            <input type="text" name="title" required>
        </label><br><br>

        <label>Instruksi<br>
            <textarea name="instructions" rows="6" cols="60"></textarea>
        </label><br><br>

        <label>Deskripsi Resep<br>
            <textarea name="description" rows="3" cols="60" placeholder="Cerita singkat tentang resep ini..."></textarea>
        </label><br><br>

        <label>Upload Foto Resep<br>
            <input type="file" name="image" accept="image/*">
        </label><br><br>

        <fieldset>
            <legend>Pilih bahan dari daftar</legend>
            <?php if (empty($ings)): ?>
                <p>Tidak ada bahan, tambahkan bahan dulu <a href="add_ingredient.php">di sini</a>.</p>
            <?php else: ?>
                <?php foreach ($ings as $ing): ?>
                    <div class="ingredient-row">
                        <label>
                            <input type="checkbox" name="ingredient_ids[]" value="<?php echo (int)$ing['id']; ?>">
                            <?php echo htmlspecialchars($ing['name']); ?>
                        </label>
                        <label>Qty: <input class="qty" type="text" name="quantity[<?php echo (int)$ing['id']; ?>]" placeholder="opsional"></label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </fieldset>

        <p>Atau tambahkan bahan baru (pisahkan dengan koma):<br>
            <input type="text" name="new_ingredients" placeholder="contoh: tomat, daun kemangi">
        </p>

        <button type="submit">Simpan Resep</button>
    </form>
</body>
</html>