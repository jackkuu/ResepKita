<?php
$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$recipe = null;
$recipe_ingredients = [];

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
            SELECT i.name, ri.quantity 
            FROM recipe_ingredients ri
            JOIN ingredients i ON ri.ingredient_id = i.id
            WHERE ri.recipe_id = ?
            ORDER BY i.name
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recipe_ingredients[] = $row;
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
    <title><?php echo $recipe ? htmlspecialchars($recipe['title']) : 'Resep'; ?> — ResepKita</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .recipe-detail-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            align-items: start;
        }

        .recipe-detail-image {
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .recipe-detail-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .recipe-detail-info h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .recipe-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }

        .recipe-description {
            font-size: 16px;
            line-height: 1.7;
            color: #333;
            margin-bottom: 20px;
        }

        .recipe-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .ingredients-section h2,
        .instructions-section h2 {
            font-size: 20px;
            margin-top: 0;
        }

        .ingredient-item {
            display: flex;
            gap: 12px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 6px;
            margin-bottom: 8px;
            border-left: 3px solid var(--accent);
        }

        .ingredient-item input[type="checkbox"] {
            flex-shrink: 0;
            margin-top: 3px;
        }

        .ingredient-name {
            flex-grow: 1;
        }

        .ingredient-qty {
            color: #666;
            font-size: 14px;
            min-width: 100px;
            text-align: right;
        }

        .instructions-text {
            white-space: pre-line;
            line-height: 1.8;
            color: #333;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .recipe-detail-header {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .recipe-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
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
                <a href="add_ingredient.php">Tambah Ingredient</a>
                <a href="add_recipe.php">Tambah Resep</a>
                <a href="manage_ingredients.php">Kelola Bahan</a>
                <a href="manage_recipes.php">Kelola Resep</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <a href="index.php" class="button back-button">← Kembali ke Pencarian</a>

        <?php if ($recipe): ?>
            <div class="recipe-detail-header">
                <div class="recipe-detail-image">
                    <?php if (!empty($recipe['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                    <?php elseif (!empty($recipe['image'])): ?>
                        <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                    <?php else: ?>
                        <div style="width:100%;height:400px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#999;">
                            Tidak ada foto
                        </div>
                    <?php endif; ?>
                </div>

                <div class="recipe-detail-info">
                    <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
                    
                    <div class="recipe-meta">
                        <span>📅 Dibuat: <?php echo date('d M Y', strtotime($recipe['created_at'])); ?></span>
                    </div>

                    <?php if ($recipe['description']): ?>
                        <div class="recipe-description">
                            <?php echo nl2br(htmlspecialchars($recipe['description'])); ?>
                        </div>
                    <?php endif; ?>

                    <div class="recipe-meta">
                        <strong>Total Bahan:</strong> <?php echo count($recipe_ingredients); ?> item
                    </div>
                </div>
            </div>

            <div class="recipe-content">
                <div class="ingredients-section">
                    <h2>🛒 Bahan-Bahan</h2>
                    <?php if (empty($recipe_ingredients)): ?>
                        <p>Tidak ada bahan tersedia.</p>
                    <?php else: ?>
                        <?php foreach ($recipe_ingredients as $ing): ?>
                            <div class="ingredient-item">
                                <input type="checkbox">
                                <div class="ingredient-name">
                                    <?php echo htmlspecialchars($ing['name']); ?>
                                </div>
                                <?php if ($ing['quantity']): ?>
                                    <div class="ingredient-qty">
                                        <?php echo htmlspecialchars($ing['quantity']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="instructions-section">
                    <h2>👨‍🍳 Cara Membuat</h2>
                    <?php if ($recipe['instructions']): ?>
                        <div class="instructions-text">
                            <?php echo htmlspecialchars($recipe['instructions']); ?>
                        </div>
                    <?php else: ?>
                        <p>Instruksi tidak tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="card">
                <h1>Resep tidak ditemukan</h1>
                <p><a href="index.php" class="button">Kembali ke Pencarian</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>