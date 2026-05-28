<?php
// 1. Inclure le fichier de connexion à la base de données
require_once 'config.php';

// Supprimer le message de test du config.php pour ne pas polluer le JSON final
// (Pense à commenter ou effacer la ligne "echo json_encode([...]);" dans ton config.php)

try {
    // 2. Préparer la requête SQL en calculant la moyenne directement
    $query = "SELECT numEt, nom, note_math, note_phys, 
              (note_math + note_phys) / 2 AS moyenne 
              FROM etudiant 
              ORDER BY numEt DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // 3. Récupérer les données sous forme de tableau associatif
    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Renvoyer les données au Frontend
    // Si la table est vide, on renvoie un tableau vide []
    echo json_encode($etudiants);

} catch(PDOException $exception) {
    // En cas d'erreur SQL
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Impossible de récupérer les étudiants : " . $exception->getMessage()
    ]);
}
?>