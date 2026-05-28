<?php
// Configuration des en-têtes pour l'API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$db_name = "gestion_etudiant";
$username = "root"; // Mets ton utilisateur MariaDB (souvent root)
$password = "";     // Mets ton mot de passe MariaDB (vide ou root sur XAMPP/Wamp)

try {
    // Tentative de connexion avec PDO
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8", $username, $password);
    
    // Configurer PDO pour lever des exceptions en cas d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 📢 MESSAGE DE TEST (À supprimer une fois le test réussi)
    /*echo json_encode([
        "status" => "success",
        "message" => "Connexion à la base de données réussie ! Bravo Développeur 1."
    ]);*/

} catch(PDOException $exception) {
    // Si la connexion échoue, on attrape l'erreur et on l'affiche
    echo json_encode([
        "status" => "error",
        "message" => "Échec de la connexion : " . $exception->getMessage()
    ]);
    exit();
}
?>