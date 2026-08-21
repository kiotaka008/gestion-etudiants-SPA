<?php
// Entêtes de sécurité indispensables pour l'intégration Vue.js (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestion de la pré-vérification (OPTIONS) du navigateur
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Récupération du numEt envoyé par Axios
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$numEt = isset($data['numEt']) ? intval($data['numEt']) : null;

// Vérification que le numéro d'étudiant est bien transmis
if (empty($numEt)) {
    echo json_encode([
        "success" => false, 
        "message" => "Identifiant (numEt) de l'étudiant manquant."
    ]);
    exit;
}

try {
    // Connexion à la base de données
    $bdd = new PDO("mysql:host=localhost;dbname=gestion_etudiant;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête de suppression
    $req = $bdd->prepare("DELETE FROM etudiant WHERE numEt = ?");
    $req->execute([$numEt]);

    echo json_encode([
        "success" => true, 
        "message" => "L'étudiant a été supprimé avec succès !"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur SQL lors de la suppression : " . $e->getMessage()
    ]);
}