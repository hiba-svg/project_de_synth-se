<?php
    session_start();

    if (! isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    require_once 'db.php';

    $category_id = (int) $_GET['id']; // Get the category ID from the URL

    $stmt = $mysqli->prepare("SELECT * FROM categories WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result   = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();
    } else {
        die("Erreur lors de la récupération de la catégorie : " . $mysqli->error);
    }

    if (! $category) {
        // If category does not exist, redirect to manage categories
        header('Location: categories.php');
        exit;
    }

    $menu_item_count = 0;
    $stmt            = $mysqli->prepare("SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result          = $stmt->get_result();
        $row             = $result->fetch_assoc();
        $menu_item_count = $row['count'];
        $stmt->close();
    } else {
        die("Erreur lors de la vérification des éléments de menu : " . $mysqli->error);
    }

    $errors  = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Force delete the category and all associated menu items
        if ($menu_item_count > 0) {
            // Delete all associated menu items
            $stmt = $mysqli->prepare("DELETE FROM menu_items WHERE category_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $stmt->close();
            }

            // Delete the category
            $stmt = $mysqli->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $stmt->close();
                $success = true;
            }
        } else {
            $stmt = $mysqli->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $stmt->close();
                $success = true;
            }
        }
    }
?>

<?php include_once 'includes/header.php'; ?>
<div class="container mt-5">
    <h2>Supprimer la Catégorie</h2>

    <?php if ($menu_item_count > 0): ?>
        <div class="alert alert-warning">
            <strong>Attention !</strong> Cette catégorie est utilisée dans <strong><?php echo $menu_item_count; ?></strong> élément(s) de menu. Si vous continuez, tous les éléments de menu associés seront supprimés.
            <form method="POST">
                <button type="submit" class="btn btn-danger mt-3">Forcer la suppression de cette catégorie</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Il n'y a aucun élément de menu associé à cette catégorie. Vous pouvez la supprimer sans danger.
            <form method="POST">
                <button type="submit" class="btn btn-danger mt-3">Supprimer la Catégorie</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success mt-3">
            La catégorie a été supprimée avec succès, ainsi que tous les éléments de menu associés.
        </div>
        <a href="categories.php" class="btn btn-primary mt-3">Retour à la gestion des catégories</a>
    <?php endif; ?>
</div>
<?php include_once 'includes/footer.php'; ?>
