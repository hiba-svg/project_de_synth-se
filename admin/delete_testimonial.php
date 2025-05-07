<?php
session_start();

if (! isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$testimonial_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Get the testimonial to check if it exists
    $stmt = $mysqli->prepare("SELECT image_url FROM testimonials WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $testimonial_id);
        $stmt->execute();
        $result      = $stmt->get_result();
        $testimonial = $result->fetch_assoc();
        $stmt->close();

        if ($testimonial) {
            // Delete testimonial from database
            $stmt = $mysqli->prepare("DELETE FROM testimonials WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $testimonial_id);
                $stmt->execute();
                $stmt->close();

                // Delete the image file if it exists
                if ($testimonial['image_url']) {
                    $imagePath = realpath('../' . $testimonial['image_url']);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                echo '<script>alert("Témoignage supprimé avec succès.");</script>';
                echo '<script>window.location.href = "testimonials.php";</script>';
            }
        } else {
            echo '<script>alert("Témoignage introuvable.");</script>';
            echo '<script>window.location.href = "testimonials.php";</script>';
        }
    }
} catch (Exception $e) {
    echo '<script>alert("Erreur lors de la suppression du témoignage : ' . htmlspecialchars($e->getMessage()) . '");</script>';
    echo '<script>window.location.href = "testimonials.php";</script>';
}
