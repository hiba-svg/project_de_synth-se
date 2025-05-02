<?php
//Ce code utilise la méthode query() de MySQLi pour récupérer les éléments du menu ayant une remise (discount > 0), les trie par remise décroissante, puis les affiche dynamiquement avec une boucle foreach en limitant à 4 résultats.Chaque élément est protégé avec htmlspecialchars() pour éviter les failles XSS, et une fonction personnalisée getOffers() est définie pour encapsuler la logique de récupération des offres en base de données.
require_once 'db.php';

function getOffers()
{
    global $mysqli;


    $query  = "SELECT * FROM menu_items WHERE discount > 0 ORDER BY discount DESC LIMIT 4";
    $result = $mysqli->query($query);

    if ($result) {

        $offers = [];
        while ($row = $result->fetch_assoc()) {
            $offers[] = $row;
        }
        return $offers;
    } else {

        error_log("Error fetching offers: " . $mysqli->error);
        return [];
    }
}

$offers = getOffers();
?>
<section class="offer_section layout_padding-bottom">
    <div class="container">
        <div class="row">
            <?php foreach ($offers as $offer): ?>
                <div class="col-md-6">
                    <div class="box">
                        <div class="img-box">
                            <img src="<?php echo htmlspecialchars($offer['image_url']); ?>" alt="<?php echo htmlspecialchars($offer['name']); ?>" />
                        </div>
                        <div class="detail-box">
                            <h5><?php echo htmlspecialchars($offer['name']); ?></h5>
                            <h6><span><?php echo htmlspecialchars(number_format($offer['discount'], 0)); ?>%</span> Off</h6>
                            <div class="btn-box">
                                <button class="btn1" onclick="addToCart(<?php echo htmlspecialchars($offer['id']); ?>)">
                                    Commander Maintenant
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>