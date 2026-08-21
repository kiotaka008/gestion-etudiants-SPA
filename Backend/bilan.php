<?php
// Permettre l'accès depuis le serveur de développement Vue.js
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestion des requêtes de pré-vérification CORS (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

try {
    // 1. Requête pour calculer toutes les statistiques globales d'un coup
    $queryStats = "SELECT 
                    ROUND(AVG((note_math + note_phys) / 2), 2) AS moyenne_classe,
                    ROUND(MIN((note_math + note_phys) / 2), 2) AS moyenne_minimale,
                    ROUND(MAX((note_math + note_phys) / 2), 2) AS moyenne_maximale,
                    SUM(CASE WHEN (note_math + note_phys) / 2 >= 10 THEN 1 ELSE 0 END) AS admis,
                    SUM(CASE WHEN (note_math + note_phys) / 2 < 10 THEN 1 ELSE 0 END) AS redoublants
                  FROM etudiant";
    
    $stmtStats = $pdo->prepare($queryStats);
    $stmtStats->execute();
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // Si la table est vide, on force des valeurs par défaut à 0
    if ($stats['moyenne_classe'] === null) {
        $stats = [
            "moyenne_classe" => 0,
            "moyenne_minimale" => 0,
            "moyenne_maximale" => 0,
            "admis" => 0,
            "redoublants" => 0
        ];
    } else {
        $stats['moyenne_classe'] = (float)$stats['moyenne_classe'];
        $stats['moyenne_minimale'] = (float)$stats['moyenne_minimale'];
        $stats['moyenne_maximale'] = (float)$stats['moyenne_maximale'];
        $stats['admis'] = (int)$stats['admis'];
        $stats['redoublants'] = (int)$stats['redoublants'];
    }

    // 2. Requête pour obtenir le classement des étudiants (Ajout dynamique de 'prenom' si existant)
    $checkPrenom = $pdo->query("SHOW COLUMNS FROM etudiant LIKE 'prenom'");
    $hasPrenom = $checkPrenom->rowCount() > 0;

    if ($hasPrenom) {
        $queryClassement = "SELECT numEt, nom, prenom, note_math, note_phys, 
                            ROUND((note_math + note_phys) / 2, 2) AS moyenne 
                            FROM etudiant 
                            ORDER BY moyenne DESC";
    } else {
        $queryClassement = "SELECT numEt, nom, '' AS prenom, note_math, note_phys, 
                            ROUND((note_math + note_phys) / 2, 2) AS moyenne 
                            FROM etudiant 
                            ORDER BY moyenne DESC";
    }

    $stmtClassement = $pdo->prepare($queryClassement);
    $stmtClassement->execute();
    $classement = $stmtClassement->fetchAll(PDO::FETCH_ASSOC);

    // 3. Préparation des 12 mois par défaut
    $nomsMois = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];
    $effectifsParMois = [];
    foreach ($nomsMois as $nom) {
        $effectifsParMois[] = [
            "mois" => $nom,
            "nombre" => 0
        ];
    }

    // On vérifie si la colonne date_creation existe avant de lancer la requête d'histogramme
    $checkDate = $pdo->query("SHOW COLUMNS FROM etudiant LIKE 'date_creation'");
    
    if ($checkDate->rowCount() > 0) {
        $queryMonths = "SELECT MONTH(date_creation) as mois_num, COUNT(*) as nombre 
                        FROM etudiant 
                        WHERE date_creation IS NOT NULL
                        GROUP BY MONTH(date_creation)";
        $stmtMonths = $pdo->prepare($queryMonths);
        $stmtMonths->execute();
        $monthsRaw = $stmtMonths->fetchAll(PDO::FETCH_ASSOC);

        // Remplissage des mois existants
        foreach ($monthsRaw as $row) {
            $mIndex = (int)$row['mois_num'] - 1;
            if ($mIndex >= 0 && $mIndex < 12) {
                $effectifsParMois[$mIndex]['nombre'] = (int)$row['nombre'];
            }
        }
    } else {
        // Fallback : Si pas de date, on met la totalité des étudiants sur le mois en cours
        $currentMonthIndex = (int)date('n') - 1;
        $queryTotal = "SELECT COUNT(*) as total FROM etudiant";
        $totalEtudiants = $pdo->query($queryTotal)->fetchColumn();
        $effectifsParMois[$currentMonthIndex]['nombre'] = (int)$totalEtudiants;
    }

    // Compilation finale
    echo json_encode([
        "success" => true,
        "stats" => $stats,
        "classement" => $classement,
        "effectifs" => $effectifsParMois
    ]);

} catch(PDOException $exception) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Impossible de générer le bilan : " . $exception->getMessage()
    ]);
}
?>