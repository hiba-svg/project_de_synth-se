<?php

// Ce code utilise la méthode mysqli->query() pour exécuter une requête SQL permettant de récupérer les catégories liées aux produits dans la base de données, puis la méthode fetch_assoc() pour parcourir les résultats et construire un tableau PHP des catégories. Ensuite, il génère dynamiquement une liste HTML <ul> avec des éléments <li> contenant un attribut data-filter, utile pour filtrer les produits affichés par catégorie via des bibliothèques comme Isotope.js.

    require_once 'db.php';

    function getCategories()
    {
        global $mysqli;

        $query = "
        SELECT c.id, c.name
        FROM categories c
        INNER JOIN menu_items mi ON c.id = mi.category_id
        GROUP BY c.id, c.name
    ";

        $result = $mysqli->query($query);

        if ($result) {

            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            return $categories;
        } else {

            error_log("Error fetching categories: " . $mysqli->error);
            return [];
        }
    }

    $categories = getCategories();
?>

<ul class="filters_menu">
    <li class="active" data-filter="*">Tout</li>
    <?php foreach ($categories as $category): ?>
<?php

    $sanitizedCategoryName = strtolower(str_replace(' ', '-', $category['name']));
?>
        <li data-filter=".<?php echo htmlspecialchars($sanitizedCategoryName); ?>">
            <?php echo htmlspecialchars($category['name']); ?>
        </li>
    <?php endforeach; ?>
</ul>