<?php
    session_start();

    // Redirect to login page if user is not logged in
    if (! isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    require_once 'db.php'; // Include database connection

    $target_dir = '../images/client_avis/';
    if (! is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $testimonial_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Fetch the testimonial data
    try {
        $stmt = $mysqli->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $testimonial_id); // Binding the ID parameter
        $stmt->execute();
        $result = $stmt->get_result();
        $item   = $result->fetch_assoc();

        if (! $item) {
            echo '<script>alert("Témoignage introuvable."); window.location.href = "testimonials.php";</script>';
            exit;
        }
    } catch (Exception $e) {
        echo '<script>alert("Erreur lors de la récupération du témoignage : ' . htmlspecialchars($e->getMessage()) . '");</script>';
        exit;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name             = trim($_POST['name']);
        $testimonial_text = trim($_POST['testimonial']);
        $image_url        = $item['image_url'];

        try {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name  = basename($_FILES['image']['name']);
                $target_file = $target_dir . $image_name;

                $check = getimagesize($_FILES['image']['tmp_name']);
                if ($check === false) {
                    echo '<script>alert("Le fichier n\'est pas une image valide.");</script>';
                }
                if ($_FILES['image']['size'] > 5242880) {
                    echo '<script>alert("Le fichier est trop volumineux. Veuillez télécharger une image de moins de 5 Mo.");</script>';
                }
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = $target_file;
                } else {
                    echo '<script>alert("Erreur lors de l\'upload du fichier. Vérifiez les permissions du répertoire.");</script>';
                }
            }

            // Update the testimonial record
            if (isset($image_url)) {
                $stmt = $mysqli->prepare("UPDATE testimonials SET name = ?, testimonial = ?, image_url = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $testimonial_text, $image_url, $testimonial_id); // Binding the parameters
            } else {
                $stmt = $mysqli->prepare("UPDATE testimonials SET name = ?, testimonial = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $testimonial_text, $testimonial_id); // Binding the parameters without image
            }
            $stmt->execute();

            echo '<script>alert("Témoignage mis à jour avec succès.");</script>';
            echo '<script>window.location.href = "testimonials.php";</script>';
            exit;
        } catch (Exception $e) {
            echo '<script>alert("Erreur lors de la mise à jour : ' . htmlspecialchars($e->getMessage()) . '");</script>';
        }
    }
?>

<?php include_once 'includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Modifier un Témoignage</h2>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Nom</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="testimonial" class="form-label">Témoignage</label>
            <textarea class="form-control" id="testimonial" name="testimonial" rows="5" required><?php echo htmlspecialchars($item['testimonial']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Image (facultative)</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
            <?php if (! empty($item['image_url'])): ?>
                <div class="mt-2">
                    <strong>Image actuelle :</strong><br>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Image actuelle" style="max-width: 200px;">
                </div>
            <?php endif; ?>
            <small class="form-text text-muted">Laissez vide pour conserver l'image actuelle. Max 5 Mo.</small>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="testimonials.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include_once 'includes/footer.php'; ?>
