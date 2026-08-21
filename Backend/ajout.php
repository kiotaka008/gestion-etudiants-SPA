<?php
// 1. Entêtes de sécurité indispensables pour l'intégration Vue.js (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestion de la pré-vérification du navigateur (OPTIONS)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 2. Récupération des données envoyées par Axios
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$numEt = isset($data['numEt']) ? intval($data['numEt']) : null;
$nom = isset($data['nom']) ? trim($data['nom']) : null;
$note_math = isset($data['note_math']) ? $data['note_math'] : null;
$note_phys = isset($data['note_phys']) ? $data['note_phys'] : null;

// Vérification de la présence des données obligatoires
if (empty($numEt) || empty($nom) || $note_math === null || $note_phys === null) {
    echo json_encode([
        "success" => false, 
        "message" => "Données incomplètes reçues par le serveur PHP."
    ]);
    exit;
}

try {
    // 3. Connexion à ta base de données
    $bdd = new PDO("mysql:host=localhost;dbname=gestion_etudiant;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si le numéro d'étudiant existe déjà
    $verif = $bdd->prepare("SELECT numEt FROM etudiant WHERE numEt = ?");
    $verif->execute([$numEt]);

    if ($verif->rowCount() > 0) {
        echo json_encode([
            "success" => false, 
            "message" => "Erreur : Un étudiant possède déjà ce numéro (" . $numEt . ")."
        ]);
        exit;
    }

    // 4. Insertion dans la table 'etudiant' (structure exacte de ta console)
    $req = $bdd->prepare("INSERT INTO etudiant (numEt, nom, note_math, note_phys) VALUES (?, ?, ?, ?)");
    $req->execute([$numEt, $nom, $note_math, $note_phys]);

    echo json_encode([
        "success" => true, 
        "message" => "L'étudiant a été enregistré avec succès !"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur interne SQL : " . $e->getMessage()
    ]);
}