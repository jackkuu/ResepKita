<?php

class Recipe {
    private $id;
    private $title;
    private $ingredients;
    private $instructions;
    private $description;

    public function __construct($id, $title, $ingredients, $instructions = null, $description = null) {
        $this->id = $id;
        $this->title = $title;
        $this->ingredients = $ingredients;
        $this->instructions = $instructions;
        $this->description = $description;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getIngredients() {
        return $this->ingredients;
    }

    public function getInstructions() {
        return $this->instructions;
    }

    public function getDescription() {
        return $this->description;
    }

    public static function findByIngredients($ingredients) {
        // Convert JSON string to array if needed
        if (is_string($ingredients)) {
            $ingredients = json_decode($ingredients, true);
        }
        
        // Handle empty ingredients - show all recipes
        if (empty($ingredients)) {
            $mysqli = new mysqli('localhost', 'root', '', 'resepkita');
            $res = $mysqli->query("SELECT * FROM recipes");
            $recipes = [];
            while ($row = $res->fetch_assoc()) {
                $recipes[] = new Recipe($row['id'], $row['title'], $row['ingredients'], $row['instructions'], $row['description'] ?? null);
            }
            $mysqli->close();
            return $recipes;
        }

        $mysqli = new mysqli('localhost', 'root', '', 'resepkita');
        
        // Prepare placeholders for IN clause
        $placeholders = str_repeat('?,', count($ingredients) - 1) . '?';
        
        $sql = "
            SELECT r.* 
            FROM recipes r
            INNER JOIN recipe_ingredients ri ON r.id = ri.recipe_id
            INNER JOIN ingredients i ON ri.ingredient_id = i.id
            WHERE i.name IN ($placeholders)
            GROUP BY r.id
            HAVING COUNT(DISTINCT i.id) = ?
        ";

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }

        // Add count to ingredients array for HAVING clause
        $params = array_merge($ingredients, [count($ingredients)]);
        $types = str_repeat('s', count($ingredients)) . 'i';
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = new Recipe(
                $row['id'],
                $row['title'],
                $row['ingredients'],
                $row['instructions'],
                $row['description'] ?? null
            );
        }

        $stmt->close();
        $mysqli->close();

        return $recipes;
    }

    public static function findByIngredientsAndName($ingredients, $search = '') {
        if (is_string($ingredients)) {
            $ingredients = json_decode($ingredients, true);
        }
        
        $mysqli = new mysqli('localhost', 'root', '', 'resepkita');
        
        if (empty($ingredients) && empty($search)) {
            // Show all recipes if no search criteria
            $res = $mysqli->query("SELECT * FROM recipes ORDER BY title");
            $recipes = [];
            while ($row = $res->fetch_assoc()) {
                $recipes[] = new Recipe($row['id'], $row['title'], $row['ingredients'], $row['instructions'], $row['description'] ?? null);
            }
            $mysqli->close();
            return $recipes;
        }
        
        // Build query based on ingredients and search term
        $where = [];
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $where[] = "r.title LIKE ?";
            $params[] = "%$search%";
            $types .= 's';
        }
        
        if (!empty($ingredients)) {
            $placeholders = str_repeat('?,', count($ingredients) - 1) . '?';
            $where[] = "i.name IN ($placeholders)";
            foreach ($ingredients as $ing) {
                $params[] = $ing;
                $types .= 's';
            }
            $having = "HAVING COUNT(DISTINCT i.id) = " . count($ingredients);
        } else {
            $having = "";
        }
        
        $whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
        
        $sql = "
            SELECT r.* 
            FROM recipes r
            LEFT JOIN recipe_ingredients ri ON r.id = ri.recipe_id
            LEFT JOIN ingredients i ON ri.ingredient_id = i.id
            $whereClause
            GROUP BY r.id
            $having
            ORDER BY r.title
        ";
        
        $stmt = $mysqli->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $recipes = [];
        
        while ($row = $result->fetch_assoc()) {
            $recipes[] = new Recipe($row['id'], $row['title'], $row['ingredients'], $row['instructions'], $row['description'] ?? null);
        }
        
        $stmt->close();
        $mysqli->close();
        
        return $recipes;
    }
}
?>