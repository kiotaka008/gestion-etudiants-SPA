<?php
require_once 'config.php';

// Récupérer les données envoyées (ex: {"numEt": 5})
$data = file_get_contents("php://input");
$decoded = json_decode($data, true);

if (!empty($decoded['numEt'])) {
    try {
        $query = "DELETE FROM etudiant WHERE numEt = :numEt";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":numEt", $decoded['numEt'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Suppression réussie"]);
        } else {
            echo json_encode(["message" => "..échouée"]);
        }
    } catch (PDOException $exception) {
        echo json_encode(["message" => "..échouée"]);
    }
} else {
    echo json_encode(["message" => "..échouée (ID manquant)"]);
}
?>