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

// --- AI nutrition helper (uses Ollama on localhost:11434) ---
$nutrition_ai = null;
$ai_error = null;
$ollama_url = 'http://localhost:11434/api/generate';
$ollama_model = 'mistral'; // or 'llama2', 'neural-chat', etc

function call_ollama_api($prompt, $url, $model)
{
    $data = [
        'model' => $model,
        'prompt' => "Anda adalah asisten nutrisi yang singkat dan ringkas. Analisis bahan-bahan resep dan perkirakan nilai gizi. Kembalikan HANYA JSON valid (tanpa markdown) dengan kunci berikut: calories (angka), sugar_g (angka), fat_g (angka), protein_g (angka), carbs_g (angka), catatan (string dengan penjelasan singkat) dan berikan peringatan bahwasannya ini hanya estimasi. Contoh: {\"calories\": 450, \"sugar_g\": 8, \"fat_g\": 15, \"protein_g\": 20, \"carbs_g\": 55, \"catatan\": \"perkiraan per porsi\"}\n\nBahan resep:\n" . $prompt,
        'stream' => false
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Ollama bisa lambat
    $resp = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return ['error' => $err];
    if ($http_code !== 200) return ['error' => "HTTP $http_code", 'raw' => substr($resp, 0, 500)];
    
    $json = json_decode($resp, true);
    if (!$json) return ['error' => 'invalid_response', 'raw' => substr($resp, 0, 500)];
    
    $text = $json['response'] ?? null;
    if (!$text) return ['error' => 'no_content', 'raw' => json_encode($json)];

    // Extract JSON from response (may have markdown code blocks)
    $text = str_replace(['```json', '```'], '', $text);
    $text = trim($text);
    
    $parsed = json_decode($text, true);
    if ($parsed) return ['result' => $parsed, 'raw' => $text];

    // Try to extract JSON from response
    if (preg_match('/\{[^{}]*\}/', $text, $m)) {
        $p = json_decode($m[0], true);
        if ($p) return ['result' => $p, 'raw' => $m[0]];
    }

    return ['error' => 'cannot_parse', 'raw' => substr($text, 0, 500)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_nutrition']) && $recipe) {
    $servings = !empty($recipe['servings']) ? (int)$recipe['servings'] : 1;
    $lines = [];
    foreach ($recipe_ingredients as $ing) {
        $lines[] = trim($ing['name']) . ($ing['quantity'] ? ' (' . trim($ing['quantity']) . ')' : '');
    }
    $prompt = implode("\n", $lines) . "\n\nJumlah porsi: $servings";

    $resp = call_ollama_api($prompt, $ollama_url, $ollama_model);
    if (isset($resp['result'])) {
        $nutrition_ai = $resp['result'];
    } else {
        $ai_error = $resp['error'] . (isset($resp['raw']) ? ': ' . substr($resp['raw'], 0, 1000) : '');
    }
}

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
                <a href="nutrition_filter.php">Filter Nutrisi</a>
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

                    <!-- AI Nutrition Generator -->
                    <div style="margin-top:18px;">
                        <form method="post" style="display:inline">
                            <input type="hidden" name="generate_nutrition" value="1">
                            <button type="submit">Estimasi Nutrisi (AI)</button>
                        </form>

                        <?php if ($ai_error): ?>
                            <div style="margin-top:12px;color:#b00;font-size:14px;">Gagal menghasilkan ringkasan: <?php echo htmlspecialchars($ai_error); ?></div>
                        <?php endif; ?>

                        <?php if ($nutrition_ai): ?>
                            <div style="margin-top:14px;padding:12px;background:#fcfcfc;border:1px solid #eee;border-radius:6px;">
                                <h3>Ringkasan Estimasi Nutrisi (AI)</h3>
                                <ul style="list-style:none;padding:0;margin:0;font-size:15px;">
                                    <li><strong>Kalori:</strong> <?php echo htmlspecialchars($nutrition_ai['calories'] ?? '—'); ?> kcal</li>
                                    <li><strong>Gula:</strong> <?php echo htmlspecialchars($nutrition_ai['sugar_g'] ?? '—'); ?> g</li>
                                    <li><strong>Lemak:</strong> <?php echo htmlspecialchars($nutrition_ai['fat_g'] ?? '—'); ?> g</li>
                                    <li><strong>Protein:</strong> <?php echo htmlspecialchars($nutrition_ai['protein_g'] ?? '—'); ?> g</li>
                                    <li><strong>Karbohidrat:</strong> <?php echo htmlspecialchars($nutrition_ai['carbs_g'] ?? '—'); ?> g</li>
                                </ul>
                                <?php if (!empty($nutrition_ai['catatan'])): ?>
                                    <div style="margin-top:8px;color:#444;font-size:14px;"><strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($nutrition_ai['catatan'])); ?></div>
                                <?php elseif (!empty($nutrition_ai['notes'])): ?>
                                    <div style="margin-top:8px;color:#444;font-size:14px;"><strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($nutrition_ai['notes'])); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

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