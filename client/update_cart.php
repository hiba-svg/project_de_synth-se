<?php
//Ce code utilise les méthodes PHP `json_decode`, `file_get_contents`, `setcookie` et les requêtes SQL préparées (`prepare`, `bind_param`, `execute`) pour gérer dynamiquement un panier d’achat via AJAX. Il traite trois actions (`update`, `remove`, `clear`) en manipulant un cookie nommé `cart` pour stocker les produits et leurs quantités, tout en s'assurant que les produits existent dans la base de données.

header('Content-Type: application/json');


$input = json_decode(file_get_contents('php://input'), true);

if (! $input || ! isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}


$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

switch ($input['action']) {
    case 'update':

        if (! isset($input['product_id']) || ! isset($input['quantity'])) {
            echo json_encode(['success' => false, 'message' => 'Missing product ID or quantity']);
            exit;
        }

        $product_id = (int) $input['product_id'];
        $quantity   = (int) $input['quantity'];


        if ($quantity <= 0) {

            if (isset($cart[$product_id])) {
                unset($cart[$product_id]);
            }
        } else {

            require_once 'db.php';

            $stmt = $mysqli->prepare("SELECT id, price FROM menu_items WHERE id = ?");
            $stmt->bind_param('i', $product_id);
            $stmt->execute();


            $result  = $stmt->get_result();
            $product = $result->fetch_assoc();

            if (! $product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }


            if (isset($cart[$product_id])) {

                if (isset($input['replace']) && $input['replace'] === true) {
                    $cart[$product_id] = $quantity;
                } else {

                    if ($quantity > $cart[$product_id]) {
                        $cart[$product_id] += 1;
                    } elseif ($quantity < $cart[$product_id]) {
                        $cart[$product_id] -= 1;
                        if ($cart[$product_id] <= 0) {
                            unset($cart[$product_id]);
                        }
                    }
                }
            } else {

                $cart[$product_id] = $quantity;
            }
        }
        break;

    case 'remove':

        if (! isset($input['product_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing product ID']);
            exit;
        }

        $product_id = (int) $input['product_id'];


        if (isset($cart[$product_id])) {
            unset($cart[$product_id]);
        }
        break;

    case 'clear':

        $cart = [];
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}


$expiry = time() + (86400 * 30);
setcookie('cart', json_encode($cart), [
    'expires'  => $expiry,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
]);


echo json_encode([
    'success' => true,
    'message' => 'Cart updated successfully',
    'cart'    => $cart,
]);
