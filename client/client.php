<?php

// Ce code PHP utilise la méthode query() de l'objet $mysqli pour exécuter une requête SQL qui récupère tous les témoignages depuis la base de données, puis la méthode fetch_assoc() pour lire les résultats ligne par ligne dans un tableau associatif. Ensuite, dans la partie HTML, une boucle foreach est utilisée pour afficher dynamiquement chaque témoignage avec les fonctions htmlspecialchars() pour sécuriser les données affichées.

require_once 'db.php';

function getTestimonials()
{
    global $mysqli;

    // Execute the query
    $query  = "SELECT * FROM testimonials";
    $result = $mysqli->query($query);

    if ($result) {
        // Fetch all rows as an associative array
        $testimonials = [];
        while ($row = $result->fetch_assoc()) {
            $testimonials[] = $row;
        }
        return $testimonials;
    } else {
        // Log the error and return an empty array
        error_log("Error fetching testimonials: " . $mysqli->error);
        return [];
    }
}

$testimonials = getTestimonials();
?>

<section class="client_section layout_padding-bottom">
    <div class="container">
        <div class="heading_container heading_center psudo_white_primary mb_45">
            <h2>Ce que disent nos clients</h2>
        </div>
        <div class="carousel-wrap row">
            <div class="owl-carousel client_owl-carousel">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="item">
                        <div class="box">
                            <div class="detail-box">
                                <p><?php echo htmlspecialchars($testimonial['testimonial']); ?></p>
                                <h6><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                            </div>
                            <div class="img-box">
                                <img src="<?php echo htmlspecialchars($testimonial['image_url']); ?>" alt="" class="box-img" />
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>