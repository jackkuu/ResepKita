<?php
require_once '../db/connection.php';

function searchRecipes($selectedIngredients) {
    global $conn;

    // Prepare the SQL query to search for recipes based on selected ingredients
    $placeholders = rtrim(str_repeat('?,', count($selectedIngredients)), ',');
    $sql = "SELECT * FROM recipes WHERE ingredients IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($selectedIngredients);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingredients'])) {
    $selectedIngredients = $_POST['ingredients'];
    $recipes = searchRecipes($selectedIngredients);
    
    // Include the view to display search results
    include '../views/search_results.php';
}
?>