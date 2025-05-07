<?php
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';

    // Validate category name
    if (empty($name)) {
        $errors[] = 'Le nom de la catégorie est requis.';
    } else {
        // Check if category name already exists
        $stmt = $mysqli->prepare("SELECT * FROM categories WHERE name = ?");
        if ($stmt) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result            = $stmt->get_result();
            $existing_category = $result->fetch_assoc();
            $stmt->close();

            if ($existing_category) {
                $errors[] = "Cette catégorie existe déjà.";
            } else {
                // Insert category into categories table
                $stmt = $mysqli->prepare("INSERT INTO categories (name, created_at) VALUES (?, NOW())");
                if ($stmt) {
                    $stmt->bind_param("s", $name);
                    $stmt = $mysqli->prepare("
                    INSERT INTO categories (name, created_at)
                    VALUES (?, NOW())
                ");
                    $stmt->execute([$name]);

                    echo '<script>alert("Catégorie ajoutée avec succès.");</script>';
                    echo '<script>window.location.href = "categories.php";</script>';

                    $stmt->close();
                } else {
                    $errors[] = "Erreur lors de la préparation de l'insertion : " . $mysqli->error;
                }
            }
        } else {
            $errors[] = "Erreur lors de la préparation de la requête de vérification : " . $mysqli->error;
        }
    }
}
