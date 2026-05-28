<?php
require_once 'config.php';

$data = file_get_contents("php://input");
$decoded = json_decode($data, true);

if (
    !empty($decoded['numEt']) &&
    !empty($decoded['nom']) &&
    isset($decoded['note_math']) &&
    isset($decoded['note_phys'])
) {
    try {
        $query = "UPDATE etudiant 
                  SET nom = :nom, note_math = :note_math, note_phys = :note_phys 
                  WHERE numEt = :numEt";
        
        $stmt = $pdo->prepare($query);

        $stmt->bindParam(":nom", $decoded['nom']);
        $stmt->bindParam(":note_math", $decoded['note_math']);
        $stmt->bindParam(":note_phys", $decoded['note_phys']);
        $stmt->bindParam(":numEt", $decoded['numEt'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Modification réussie"]);
        } else {
            echo json_encode(["message" => "..échouée"]);
        }
    } catch (PDOException $exception) {
        echo json_encode(["message" => "..échouée"]);
    }
} else {
    echo json_encode(["message" => "..échouée (Données incomplètes)"]);
}
?>