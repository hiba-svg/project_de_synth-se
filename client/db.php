<?php

// Définir les paramètres de connexion à la base de données
$host     = 'localhost';
$dbname   = 'elbaraka';
$username = 'root';
$password = '';

// Créer une instance de la classe mysqli pour se connecter à la base de données
$mysqli = new mysqli($host, $username, $password, $dbname);

// Vérifier si la connexion échoue
if ($mysqli->connect_error) {
    // Si une erreur se produit, arrêter l'exécution et afficher un message d'erreur
    die("Connection failed: " . $mysqli->connect_error);
}

?>
