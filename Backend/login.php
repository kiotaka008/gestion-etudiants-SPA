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

$motdepasse = isset($data['motdepasse']) ? $data['motdepasse'] : null; // Reçoit 'motdepasse' depuis Vue


if (empty($email) || empty($motdepasse)) {

    echo json_encode(["success" => false, "message" => "Veuillez remplir tous les champs."]);

    exit;

}


try {

    // Connexion à la base de données correcte

    $bdd = new PDO("mysql:host=localhost;dbname=gestion_etudiant;charset=utf8", "root", "");

    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // Recherche de l'utilisateur par son email

    $req = $bdd->prepare("SELECT * FROM utilisateurs WHERE email = ?");

    $req->execute([$email]);

    $user = $req->fetch(PDO::FETCH_ASSOC);


    // Vérification de l'existence de l'utilisateur et du mot de passe haché

    if ($user && password_verify($motdepasse, $user['motdepasse'])) {

        // Optionnel : ne pas renvoyer le mot de passe haché pour des raisons de sécurité

        unset($user['motdepasse']);

       

        echo json_encode([

            "success" => true,

            "message" => "Connexion réussie !",

            "user" => $user

        ]);

    } else {

        echo json_encode(["success" => false, "message" => "Email ou mot de passe incorrect."]);

    }


} catch (PDOException $e) {

    echo json_encode([

        "success" => false,

        "message" => "Erreur SQL Base de données : " . $e->getMessage()

    ]);

} 