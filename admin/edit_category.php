<?php
    session_start();

    if (! isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    require_once 'db.php';

    $errors  = [];
    $success = false;

    if (! isset($_GET['id'])) {
        header('Location: categories.php');
        exit;
    }

    $id = (int) $_GET['id'];

    // Get current category
    $stmt = $mysqli->prepare("SELECT * FROM categories WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result   = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        if (! $category) {
            header('Location: categories.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';

            if (empty($name)) {
                $errors[] = 'Le nom de la catégorie est requis.';
            } else {
                // Check for duplicate (excluding current category)
                $stmt = $mysqli->prepare("SELECT * FROM categories WHERE name = ? AND id != ?");
                if ($stmt) {
                    $stmt->bind_param("si", $name, $id);
                    $stmt->execute();
                    $result   = $stmt->get_result();
                    $existing = $result->fetch_assoc();
                    $stmt->close();

                    if ($existing) {
                        $errors[] = 'Une autre catégorie porte déjà ce nom.';
                    } else {
                        // Update
                        try {
                            $stmt = $mysqli->prepare("UPDATE categories SET name = ? WHERE id = ?");
                            if ($stmt) {
                                $stmt->bind_param("si", $name, $id);
                                $stmt->execute();
                                $stmt->close();
                                $success = true;

                                // Refresh category data
                                $category['name'] = $name;
                            }
                        } catch (Exception $e) {
                            $errors[] = "Une erreur est survenue lors de la mise à jour.";
                        }
                    }
                }
            }
        }
    }
?>
<?php include_once 'includes/header.php'; ?>
<div class="container mt-5">
    <h2>Modifier la Catégorie</h2>
    <?php if (! empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php if ($success): ?>
        <div class="alert alert-success">
            La catégorie a été mise à jour avec succès.
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group mb-3">
            <label for="name">Nom de la Catégorie *</label>
            <input type="text" class="form-control" name="name" id="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <a href="categories.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
<?php include_once 'includes/footer.php'; ?>
