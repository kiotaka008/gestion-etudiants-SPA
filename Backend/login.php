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
$motdepasse = isset($data['motdepasse']) ? $data['motdepasse'] : null;

if (empty($email) || empty($motdepasse)) {
    echo json_encode(["success" => false, "message" => "Veuillez remplir tous les champs."]);
    exit;
}

// 1. PARAMÈTRES LDAP (Windows Server)
$ldap_host = "192.168.1.10";
$ldap_port = 389;

$ldap_conn = @ldap_connect($ldap_host, $ldap_port);

if ($ldap_conn) {
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    // Utilisation du format NetBIOS (L2\Rbrayan) ou UPN selon votre choix
    // Ici on utilise le format L2\ + le login saisi
    $ldap_bind_user = "L2\\" . $email; 

    // Tentative d'authentification LDAP auprès du Windows Server
    $ldap_bind = @ldap_bind($ldap_conn, $ldap_bind_user, $motdepasse);

    if ($ldap_bind) {
        // LE LDAP A VALIDÉ LE MOT DE PASSE AVEC SUCCÈS !
        @ldap_close($ldap_conn);

        try {
            // 2. Connexion à MySQL pour récupérer ou synchroniser les infos de l'utilisateur
            $bdd = new PDO("mysql:host=localhost;dbname=gestion_etudiant;charset=utf8", "root", "");
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // On vérifie si l'utilisateur existe en base locale (adaptez la colonne 'email' si nécessaire)
            $req = $bdd->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $req->execute([$email]);
            $user = $req->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // L'utilisateur existe en base locale, on le connecte
                unset($user['motdepasse']);
                echo json_encode([
                    "success" => true,
                    "message" => "Connexion réussie via LDAP et MySQL !",
                    "user" => $user
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Compte valide dans le domaine, mais profil introuvable dans l'application."
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Erreur SQL Base de données : " . $e->getMessage()
            ]);
        }

    } else {
        // ÉCHEC LDAP : Le mot de passe ou l'identifiant est faux sur le domaine Windows Server
        @ldap_close($ldap_conn);
        echo json_encode(["success" => false, "message" => "Email ou mot de passe incorrect sur le domaine."]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Erreur : Impossible de joindre le serveur d'annuaire (LDAP)."
    ]);
}
?>