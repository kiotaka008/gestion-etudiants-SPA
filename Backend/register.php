<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Supporte à la fois les champs envoyés en Français ou en Anglais par Vue.js
$nom = isset($data['nom']) ? trim($data['nom']) : (isset($data['name']) ? trim($data['name']) : null);
$email = isset($data['email']) ? trim($data['email']) : null;
$motdepasse = isset($data['motdepasse']) ? $data['motdepasse'] : (isset($data['password']) ? $data['password'] : null);

if (empty($nom) || empty($email) || empty($motdepasse)) {
    echo json_encode([
        "success" => false, 
        "message" => "Données incomplètes. Assurez-vous d'envoyer le nom, l'email et le mot de passe."
    ]);
    exit;
}

try {
    // Connexion à la BDD (Vérifie bien le nom 'gestion_etudiant')
    $bdd = new PDO("mysql:host=localhost;dbname=gestion_etudiant;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification de l'existence de l'email
    $verif = $bdd->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $verif->execute([$email]);
    
    if ($verif->rowCount() > 0) {
        echo json_encode(["success" => false, "message" => "Cet email est déjà utilisé."]);
        exit;
    }

    // Hachage du mot de passe
    $password_hash = password_hash($motdepasse, PASSWORD_BCRYPT);

    // Insertion du nouvel utilisateur
    $req = $bdd->prepare("INSERT INTO utilisateurs (nom, email, motdepasse) VALUES (?, ?, ?)");
    $req->execute([$nom, $email, $password_hash]);

    echo json_encode(["success" => true, "message" => "Inscription réussie avec succès !"]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Erreur SQL Base de données : " . $e->getMessage()
    ]);
}