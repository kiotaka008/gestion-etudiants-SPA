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

// Récupération des données envoyées par Axios
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$numEt = isset($data['numEt']) ? intval($data['numEt']) : null;
$nom = isset($data['nom']) ? trim($data['nom']) : null;
$note_math = isset($data['note_math']) ? $data['note_math'] : null;
$note_phys = isset($data['note_phys']) ? $data['note_phys'] : null;

// Vérification des données obligatoires
if (empty($numEt) || empty($nom) || $note_math === null || $note_phys === null) {
    echo json_encode([
        "success" => false, 
        "message" => "Données incomplètes pour la modification."
    ]);
    exit;
}

try {
    // Connexion à la base de données
    $bdd = new PDO("mysql:host=localhost;dbname=gestion_etudiant;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête de mise à jour basée sur la structure exacte de ta table
    $req = $bdd->prepare("UPDATE etudiant SET nom = ?, note_math = ?, note_phys = ? WHERE numEt = ?");
    $req->execute([$nom, $note_math, $note_phys, $numEt]);

    echo json_encode([
        "success" => true, 
        "message" => "Les informations de l'étudiant ont été modifiées avec succès !"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur SQL lors de la modification : " . $e->getMessage()
    ]);
}