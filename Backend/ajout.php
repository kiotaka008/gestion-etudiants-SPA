<?php
// 1. Inclure la connexion à la base de données
require_once 'config.php';

// 2. Récupérer les données envoyées par la SPA (format JSON)
$data = file_get_contents("php://input");
$decoded = json_decode($data, true);

// 3. Vérifier que les données ne sont pas vides
if (
    !empty($decoded['nom']) &&
    isset($decoded['note_math']) &&
    isset($decoded['note_phys'])
) {
    try {
        // 4. Préparer la requête d'insertion (Sécurisée contre les injections SQL)
        $query = "INSERT INTO etudiant (nom, note_math, note_phys) 
                  VALUES (:nom, :note_math, :note_phys)";
        
        $stmt = $pdo->prepare($query);

        // Liaison des valeurs
        $stmt->bindParam(":nom", $decoded['nom']);
        $stmt->bindParam(":note_math", $decoded['note_math']);
        $stmt->bindParam(":note_phys", $decoded['note_phys']);

        // 5. Exécuter la requête et renvoyer le message attendu par le sujet
        if ($stmt->execute()) {
            http_response_code(201); // Statut HTTP: Créé
            echo json_encode(["message" => "Insertion réussie"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "..échouée"]);
        }

    } catch (PDOException $exception) {
        http_response_code(500);
        echo json_encode(["message" => "..échouée"]);
    }
} else {
    // Si les données envoyées sont incomplètes
    http_response_code(400); // Mauvaise requête
    echo json_encode(["message" => "..échouée (Données incomplètes)"]);
}
?>