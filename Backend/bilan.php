<?php
require_once 'config.php';

try {
    // Requête SQL optimisée pour calculer toutes les statistiques d'un coup
    $query = "SELECT 
                ROUND(AVG((note_math + note_phys) / 2), 2) AS moyenne_classe,
                MIN((note_math + note_phys) / 2) AS moyenne_minimale,
                MAX((note_math + note_phys) / 2) AS moyenne_maximale,
                SUM(CASE WHEN (note_math + note_phys) / 2 >= 10 THEN 1 ELSE 0 END) AS admis,
                SUM(CASE WHEN (note_math + note_phys) / 2 < 10 THEN 1 ELSE 0 END) AS redoublants
              FROM etudiant";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Récupérer le résultat (une seule ligne)
    $bilan = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si la table est vide, on force des valeurs par défaut à 0 pour éviter le "null"
    if ($bilan['moyenne_classe'] === null) {
        $bilan = [
            "moyenne_classe" => 0,
            "moyenne_minimale" => 0,
            "moyenne_maximale" => 0,
            "admis" => 0,
            "redoublants" => 0
        ];
    } else {
        // Convertir les chaînes SQL en nombres propres pour le Frontend
        $bilan['moyenne_classe'] = (float)$bilan['moyenne_classe'];
        $bilan['moyenne_minimale'] = (float)$bilan['moyenne_minimale'];
        $bilan['moyenne_maximale'] = (float)$bilan['moyenne_maximale'];
        $bilan['admis'] = (int)$bilan['admis'];
        $bilan['redoublants'] = (int)$bilan['redoublants'];
    }

    // Renvoyer le bilan au format JSON
    echo json_encode($bilan);

} catch(PDOException $exception) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Impossible de générer le bilan : " . $exception->getMessage()
    ]);
}
?>