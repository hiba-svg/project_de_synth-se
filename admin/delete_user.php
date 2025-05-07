<?php
session_start();

// Redirect to login page if user is not logged in or not admin
if (! isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'db.php'; // Include database connection

// Check if the user ID is provided
if (! isset($_POST['id'])) {
    echo '<script>alert("ID de l\'utilisateur manquant."); window.location.href = "manage_users.php";</script>';
    exit;
}

$user_id = intval($_POST['id']); // Sanitize the user ID

try {
    // Fetch the user to ensure it exists
    $stmt = $mysqli->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id); // Bind the user_id parameter
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();

    if (! $user) {
        echo '<script>alert("Utilisateur introuvable."); window.location.href = "manage_users.php";</script>';
        exit;
    }

    // Prevent deletion of the admin user (optional, as the trigger already enforces this)
    if ($user['username'] === 'admin') {
        echo '<script>alert("L\'utilisateur admin ne peut pas être supprimé."); window.location.href = "manage_users.php";</script>';
        exit;
    }

    // Delete the user
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id); // Bind the user_id parameter
    $stmt->execute();

    // Notify success
    echo '<script>alert("Utilisateur supprimé avec succès."); window.location.href = "manage_users.php";</script>';
} catch (Exception $e) {
    // Notify error
    echo '<script>alert("Erreur lors de la suppression de l\'utilisateur : ' . addslashes($e->getMessage()) . '"); window.location.href = "manage_users.php";</script>';
}
