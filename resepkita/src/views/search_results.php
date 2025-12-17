<?php
// Include database connection
require_once '../db/connection.php';

// Check if ingredients are selected
if (isset($_POST['ingredients'])) {
    $selectedIngredients = $_POST['ingredients'];
    
    // Prepare the SQL query to search for recipes
    $ingredientPlaceholders = implode(',', array_fill(0, count($selectedIngredients), '?'));
    $sql = "SELECT * FROM recipes WHERE ingredients IN ($ingredientPlaceholders)";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->execute($selectedIngredients);
    
    // Fetch results
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $recipes = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="container">
        <h1>Search Results</h1>
        
        <?php if (!empty($recipes)): ?>
            <ul>
                <?php foreach ($recipes as $recipe): ?>
                    <li>
                        <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>
                        <p><?php echo htmlspecialchars($recipe['description']); ?></p>
                        <p><strong>Ingredients:</strong> <?php echo htmlspecialchars($recipe['ingredients']); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No recipes found for the selected ingredients.</p>
        <?php endif; ?>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>