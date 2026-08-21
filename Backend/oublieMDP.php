<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestion de la requête de pré-vérification OPTIONS
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Récupération des données JSON envoyées par Axios
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$email = isset($data['email']) ? trim($data['email']) : null;
// CORRECTION ICI : Correspondance exacte avec 'nouveauMdp' envoyé par Axios
$nouveauMotDePasse = isset($data['nouveauMdp']) ? $data['nouveauMdp'] : null;

if (empty($email) || empty($nouveauMotDePasse)) {
    echo json_encode(["success" => false, "message" => "Données incomplètes pour la réinitialisation."]);
    exit;
}

try {
    // Connexion à la base de données
    $bdd = new PDO("mysql:host=localhost;dbname=gestion_etudiant;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si l'utilisateur existe bien et récupérer ses infos (id et nom)
    $verif = $bdd->prepare("SELECT id, nom FROM utilisateurs WHERE email = ?");
    $verif->execute([$email]);
    $user = $verif->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Aucun utilisateur trouvé avec cette adresse email."]);
        exit;
    }

    // Hachage sécurisé du nouveau mot de passe
    $password_hash = password_hash($nouveauMotDePasse, PASSWORD_BCRYPT);

    // Mise à jour du mot de passe dans la table utilisateurs
    $req = $bdd->prepare("UPDATE utilisateurs SET motdepasse = ? WHERE email = ?");
    $req->execute([$password_hash, $email]);

    // On renvoie les infos du profil pour que le front puisse connecter l'utilisateur direct !
    echo json_encode([
        "success" => true, 
        "message" => "Votre mot de passe a été modifié avec succès !",
        "user" => [
            "id" => $user['id'],
            "nom" => $user['nom'],
            "email" => $email
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur SQL Base de données : " . $e->getMessage()
    ]);
}