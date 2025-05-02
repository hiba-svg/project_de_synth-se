<?php

// Ce script PHP utilise la fonction setcookie() pour supprimer le cookie nommé "cart" en lui attribuant une date d’expiration passée (time() - 3600), ce qui vide le panier. Ensuite, il redirige immédiatement l’utilisateur vers la page index.php grâce à la méthode header(), puis interrompt l’exécution avec exit.

setcookie('cart', '', time() - 3600); // Expire the cookie immediately
header('Location: index.php'); // Redirect to cart page
exit;
?>