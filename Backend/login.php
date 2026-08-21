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

$email = isset($data['email']) ? trim($data['email']) : null;
$motdepasse = isset($data['motdepasse']) ? $data['motdepasse'] : null;

if (empty($email) || empty($motdepasse)) {
    echo json_encode(["success" => false, "message" => "Veuillez remplir tous les champs."]);
    exit;
}

$ldap_host = "192.168.1.10";
$ldap_port = 389;

$ldap_conn = @ldap_connect($ldap_host, $ldap_port);

if ($ldap_conn) {
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    $ldap_bind_user = $email . "@l2.eni.mg"; 

    // On enlève le "@" pour capturer l'erreur exacte du serveur Windows
    $ldap_bind = ldap_bind($ldap_conn, $ldap_bind_user, $motdepasse);

    if ($ldap_bind) {
        @ldap_close($ldap_conn);
        echo json_encode(["success" => true, "message" => "Succès total LDAP !"]);
    } else {
        // Récupération de l'erreur détaillée renvoyée par l'Active Directory
        $err_num = ldap_errno($ldap_conn);
        $err_str = ldap_error($ldap_conn);
        @ldap_close($ldap_conn);

        echo json_encode([
            "success" => false, 
            "message" => "Erreur LDAP [$err_num]: $err_str (Tentative avec: $ldap_bind_user)"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erreur : Impossible de joindre le serveur LDAP."
    ]);
}
?>