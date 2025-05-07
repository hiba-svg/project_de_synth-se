<?php
session_start();

if (! isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

if (! isset($_GET['id'])) {
    header('Location: menu_items.php');
    exit;
}

$id = (int) $_GET['id'];

$stmt = $mysqli->prepare("SELECT image_url FROM menu_items WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item   = $result->fetch_assoc();
    $stmt->close();

    if ($item) {
        if (! empty($item['image_url']) && file_exists($item['image_url'])) {
            unlink($item['image_url']);
        }

        $stmt = $mysqli->prepare("DELETE FROM menu_items WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

header('Location: menu_items.php?status=deleted');
exit;
