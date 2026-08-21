<?php
// Entêtes de sécurité indispensables pour l'intégration Vue.js (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Connexion à la base de données
    $bdd = new PDO("mysql:host=localhost;dbname=gestion_etudiant;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération de tous les étudiants (table 'etudiant' de ta base de données)
    $req = $bdd->query("SELECT numEt, nom, note_math, note_phys FROM etudiant ORDER BY numEt DESC");
    $etudiants = $req->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter le calcul de la moyenne pour chaque étudiant avant de l'envoyer à Vue
    foreach ($etudiants as &$etudiant) {
        $math = floatval($etudiant['note_math']);
        $phys = floatval($etudiant['note_phys']);
        $etudiant['moyenne'] = round(($math + $phys) / 2, 2);
    }

    // Renvoi des données au format JSON
    echo json_encode([
        "success" => true,
        "data" => $etudiants
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur SQL Base de données : " . $e->getMessage()
    ]);
}