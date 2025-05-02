<?php

// Ce code PHP récupère les éléments du menu les plus populaires depuis la base de données à l'aide d'une requête SQL qui applique un calcul de prix après remise, puis affiche ces éléments dans une section HTML à l'aide de la méthode fetch_assoc() pour obtenir les résultats sous forme de tableau associatif. Il utilise également des boucles foreach pour afficher chaque élément du menu, incluant les informations comme l'image, la description et le prix avec ou sans remise, et permet d'ajouter des articles au panier via un bouton d'action JavaScript.

    require_once 'db.php';

    function getMenuItems()
    {
        global $mysqli;

        // Execute the query
        $query  = "SELECT *, price - (price * discount / 100) AS discounted_price FROM menu_items ORDER BY sales_count LIMIT 9";
        $result = $mysqli->query($query);

        if ($result) {
            // Fetch all rows as an associative array
            $menuItems = [];
            while ($row = $result->fetch_assoc()) {
                $menuItems[] = $row;
            }
            return $menuItems;
        } else {
            // Log the error and return an empty array
            error_log("Query failed: " . $mysqli->error);
            return [];
        }
    }

    $menuItems = getMenuItems();
?>
<section class="food_section layout_padding-bottom">
    <div class="container">
        <div class="heading_container heading_center">
            <h2>Menu populaire</h2>
        </div>
        <div class="filters-content">
            <div class="row grid">
                <?php if (! empty($menuItems)): ?>
<?php foreach ($menuItems as $item): ?>
                        <div class="col-sm-6 col-lg-4 all">
                            <div class="box">
                                <div>
                                    <div class="img-box">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
                                    </div>
                                    <div class="detail-box">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                                        <div class="options">
                                            <?php if ($item['discount'] > 0): ?>
                                                <h6 class="text-light"><?php echo htmlspecialchars(number_format($item['discounted_price'], 2)); ?> DH</h6>
                                                <h6 class="text-danger"><del><?php echo htmlspecialchars(number_format($item['price'], 2)); ?> DH</del></h6>
                                            <?php else: ?>
                                                <h6><?php echo htmlspecialchars(number_format($item['price'], 2)); ?> DH</h6>
                                            <?php endif; ?>
                                            <button class="add-to-cart" onclick="addToCart(<?php echo htmlspecialchars($item['id']); ?>)">
                                                <img src="icons/cart.png" alt="Add to cart" width="22px">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
<?php else: ?>
                    <div class="col-12 text-center">
                        <p>No menu items available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
