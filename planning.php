<?php
require ("session.php");
// require ("tcpdf/tcpdf.php");
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myproject";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fonction pour sélectionner aléatoirement les enseignants disponibles
function selectRandomTeachers($conn, $count = 4)
{
    $teachers = array();

    // Sélection aléatoire des enseignants disponibles
    $sql = "SELECT nom FROM enseignant ORDER BY RAND() LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $count);
    $stmt->execute();
    $result = $stmt->get_result();

    // Récupération des noms des enseignants sélectionnés
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row['nom'];
    }

    return $teachers;
}

// Fonction pour sélectionner aléatoirement un lieu de type amphithéâtre en priorité, sinon n'importe quel type de lieu
function selectRandomLocation($conn)
{
    $location = array();

    // Sélection aléatoire d'un lieu de type "amphi" s'il en existe
    $sql = "SELECT numero, type_lieu, capacite FROM lieu WHERE type_lieu = 'amphi' ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);

    // Si un lieu de type "amphi" est trouvé, le sélectionner
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $location['numero'] = $row['numero'];
            $location['type_lieu'] = $row['type_lieu'];
            $location['capacite'] = $row['capacite'];
        }
    } else {
        // Si aucun lieu de type "amphi" n'est disponible, sélectionner n'importe quel type de lieu
        $sql = "SELECT numero, type_lieu, capacite FROM lieu ORDER BY RAND() LIMIT 1";
        $result = $conn->query($sql);

        // Récupération du lieu sélectionné
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $location['numero'] = $row['numero'];
                $location['type_lieu'] = $row['type_lieu'];
                $location['capacite'] = $row['capacite'];
            }
        }
    }

    return $location;
}


// Fonction pour récupérer les horaires disponibles à partir de la base de données
function getAvailableHours($conn)
{
    $hours = array();

    // Sélectionner tous les horaires disponibles
    $sql = "SELECT debut, fin FROM Horaire";
    $result = $conn->query($sql);

    // Récupérer les horaires disponibles
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hours[] = array(
                'debut' => $row['debut'],
                'fin' => $row['fin']
            );
        }
    }

    return $hours;
}

function getGroupesForSpecialite($conn, $specialite)
{
    $sql = "SELECT nom_groupe, section, nombre_etudiants FROM groupe WHERE nom_specialite = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt->bind_param("s", $specialite);
    $stmt->execute();
    $result = $stmt->get_result();

    $groupes = array();
    while ($row = $result->fetch_assoc()) {
        $groupes[] = $row;
    }

    return $groupes;
}
function isLieuAvailable($conn, $date, $heureDebut, $heureFin, $lieu_numero)
{
    // Vérifie s'il y a des enregistrements dans la table Lieu_NonDisponible qui correspondent au lieu, à la date, et à l'heure spécifiés
    $sql = "SELECT COUNT(*) AS count FROM Lieu_NonDisponible WHERE lieu_numero = ? AND jour = ? AND ((heureDebut >= ? AND heureDebut < ?) OR (heureFin > ? AND heureFin <= ?))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $lieu_numero, $date, $heureDebut, $heureFin, $heureDebut, $heureFin);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    // Si aucun enregistrement correspondant n'est trouvé, le lieu est disponible
    return $count == 0;
}
function isEnseignantAvailable($conn, $date, $heureDebut, $heureFin, $nom_enseignant)
{
    // Vérifie s'il y a des enregistrements dans la table Enseignant_Disponibilite qui correspondent à l'enseignant, à la date, et à l'heure spécifiés
    $sql = "SELECT COUNT(*) AS count FROM Enseignant_Disponibilite WHERE nom_enseignant = ? AND jour = ? AND heureDebut = ? AND heureFin = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nom_enseignant, $date, $heureDebut, $heureFin);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    // Si aucun enregistrement correspondant n'est trouvé, l'enseignant est disponible
    return $count == 0;
}
function getSectionsForSpecialite($conn, $specialite)
{
    $sql = "SELECT DISTINCT section FROM groupe WHERE nom_specialite = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $specialite);
    $stmt->execute();
    $result = $stmt->get_result();
    $sections = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sections[] = $row;
        }
    }

    return $sections;
}


$daysOfWeek = array(
    'Sunday' => 'Dimanche',
    'Monday' => 'Lundi',
    'Tuesday' => 'Mardi',
    'Wednesday' => 'Mercredi',
    'Thursday' => 'Jeudi',
);

// Fonction pour vérifier si un module est déjà planifié pour une journée donnée
function isModuleScheduledForDay($planning, $date)
{
    foreach ($planning as $examen) {
        if ($examen['date'] === $date) {
            return true; // Un module est déjà planifié pour cette journée
        }
    }
    return false;
}


// Fonction pour générer aléatoirement un planning initial pour une spécialité spécifique et une période spécifique
function generateRandomPlanning($conn, $specialite, $dateDebut, $dateFin)
{
    $planning = array();

    // Préparer la requête pour sélectionner les modules affectés à la spécialité spécifique
    $sql = "SELECT id_module, nom_module, activite, nom_specialite FROM module WHERE nom_specialite = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $specialite);
    $stmt->execute();
    $result = $stmt->get_result();

    // Récupérer les horaires disponibles
    $availableHours = getAvailableHours($conn);

    // Récupérer les sections pour cette spécialité
    $sections = getSectionsForSpecialite($conn, $specialite);

    // Générer aléatoirement un planning pour chaque module dans la période spécifiée
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Générer une date aléatoire pour l'examen dans la période spécifiée
            // $date = date('Y-m-d', rand(strtotime($dateDebut), strtotime($dateFin)));
            // Générer une date aléatoire pour l'examen dans la période spécifiée
            do {
                $date = date('Y-m-d', rand(strtotime($dateDebut), strtotime($dateFin)));
                $dayOfWeek = date('N', strtotime($date)); // 1 (for Monday) through 7 (for Sunday)
            } while ($dayOfWeek == 5 || $dayOfWeek == 6 || isModuleScheduledForDay($planning, $date)); // Re-generate date if it's Friday (5) or Saturday (6)

            // Sélection aléatoire d'une heure de début parmi les horaires disponibles
            $randIndex = array_rand($availableHours);
            $heureDebut = $availableHours[$randIndex]['debut'];
            $heureFin = $availableHours[$randIndex]['fin'];

            // Sélection aléatoire des enseignants disponibles
            $enseignants = selectRandomTeachers($conn, rand(4, 5)); // Sélectionne aléatoirement entre 4 et 5 enseignants

            // Vérifier la disponibilité de chaque enseignant et le remplacer s'il n'est pas disponible
            $enseignantsDisponibles = array();
            foreach ($enseignants as $enseignant) {
                if (isEnseignantAvailable($conn, $date, $heureDebut, $heureFin, $enseignant)) {
                    $enseignantsDisponibles[] = $enseignant;
                } else {
                    // Sélectionner un autre enseignant disponible
                    $nouvelEnseignant = findAvailableTeacher($conn, $date, $heureDebut, $heureFin);
                    if ($nouvelEnseignant) {
                        $enseignantsDisponibles[] = $nouvelEnseignant;
                    }
                }
            }
            $enseignants = $enseignantsDisponibles;


            // Sélection aléatoire d'un lieu disponible
            //$lieu = selectRandomLocation($conn);


            // Récupérer les groupes pour cette spécialité
            $groupes = getGroupesForSpecialite($conn, $specialite);

            // Affecter un lieu à chaque section et à ses groupes, en vérifiant la disponibilité des lieux
            $sectionsWithLieux = array();
            $firstLieu = null; // Lieu pour les trois premiers groupes
            $secondLieu = null; // Lieu pour les autres groupes

            foreach ($sections as $section) {
                // Sélection aléatoire d'un lieu disponible
                do {
                    $lieu = selectRandomLocation($conn);
                } while (!isLieuAvailable($conn, $date, $heureDebut, $heureFin, $lieu['numero']));

                $sectionsWithLieux[] = array(
                    "section" => $section['section'],
                    "numero_lieu" => $lieu['numero'],
                    "type_lieu" => $lieu['type_lieu']
                );

                // Affecter les trois premiers groupes à $firstLieu, et les autres à $secondLieu
                $counter = 0;
                foreach ($groupes as &$groupe) {
                    if ($groupe['section'] == $section['section']) {
                        if ($counter < 3) {
                            // Affecter les trois premiers groupes à $firstLieu
                            $groupe['numero_lieu'] = $lieu['numero'];
                            $groupe['type_lieu'] = $lieu['type_lieu'];
                            $firstLieu = $lieu;
                        } else {
                            // Affecter les autres groupes à $secondLieu
                            if ($secondLieu === null) {
                                // Sélectionner un autre lieu disponible pour $secondLieu
                                do {
                                    $secondLieu = selectRandomLocation($conn);
                                } while (!isLieuAvailable($conn, $date, $heureDebut, $heureFin, $secondLieu['numero']));
                            }
                            $groupe['numero_lieu'] = $secondLieu['numero'];
                            $groupe['type_lieu'] = $secondLieu['type_lieu'];
                        }
                        $counter++;
                    }
                }
            }
            unset($groupe); // Libérer la référence




            // Ajouter l'examen au planning avec les enseignants sélectionnés et les groupes
            $planning[] = array(
                "id_module" => $row['id_module'],
                "nom_module" => $row['nom_module'],
                "activite" => $row['activite'],
                "nom_specialite" => $row['nom_specialite'],
                "date" => $date,
                "heureDebut" => $heureDebut,
                "heureFin" => $heureFin,
                "enseignants" => $enseignants,
                "numero_lieu" => $lieu['numero'],
                "type_lieu" => $lieu['type_lieu'],
                "groupes" => $groupes,
                "sections" => $sectionsWithLieux
            );
        }
    }

    return $planning;
}

// Fonction pour filtrer le planning en fonction des contraintes
function filterPlanning($planning, $conn)
{
    $filteredPlanning = array();


    foreach ($planning as $examen) {
        // Récupérer les informations de l'examen
        $date = $examen['date'];
        $heureDebut = $examen['heureDebut'];
        $heureFin = $examen['heureFin'];
        $enseignants = $examen['enseignants'];
        $groupes = $examen['groupes'];
        $sections = $examen['sections'];


        $enseignantsDisponibles = array();
        foreach ($enseignants as $enseignant) {
            if (isEnseignantAvailable($conn, $date, $heureDebut, $heureFin, $enseignant)) {
                $enseignantsDisponibles[] = $enseignant;
            } else {
                // Sélectionner un autre enseignant disponible
                $nouvelEnseignant = findAvailableTeacher($conn, $date, $heureDebut, $heureFin);
                if ($nouvelEnseignant) {
                    $enseignantsDisponibles[] = $nouvelEnseignant;
                }
            }
        }

        // Si pas tous les enseignants ont pu être remplacés, ignorer cet examen
        if (count($enseignantsDisponibles) < count($enseignants)) {
            continue;
        }

        // Mettre à jour les enseignants de l'examen avec les enseignants disponibles
        $examen['enseignants'] = $enseignantsDisponibles;

        // Toutes les contraintes sont satisfaites, ajouter l'examen filtré au planning filtré
        $filteredPlanning[] = $examen;
    }

    return $filteredPlanning;
}


// Fonction pour trouver un enseignant disponible à une date et heure donnée
function findAvailableTeacher($conn, $date, $heureDebut, $heureFin)
{
    $sql = "SELECT nom FROM enseignant WHERE nom NOT IN (
                SELECT nom_enseignant FROM Enseignant_Disponibilite 
                WHERE jour = ? AND 
                ((heureDebut <= ? AND heureFin >= ?) OR (heureDebut <= ? AND heureFin >= ?))
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $date, $heureDebut, $heureDebut, $heureFin, $heureFin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['nom'];
    } else {
        return null;
    }
}

// Traitement du formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération de la spécialité sélectionnée
    $specialite = $_POST["specialite"];

    // Récupération de la période de planification des examens
    $periode = $_POST["periode"];
    list($dateDebut, $dateFin) = explode(" à ", $periode);

    // Générer un planning initial pour la spécialité "Informatique" pour une période donnée (1er au 30 mai 2024)
    $planningInitial = generateRandomPlanning($conn, $specialite, $dateDebut, $dateFin);

    // Filtrer le planning initial en fonction des contraintes
    $planningFiltre = filterPlanning($planningInitial, $conn);

}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enregistrer'])) {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=localhost;dbname=myproject', 'root', '');

    // Fonction pour insérer les données d'un enseignant dans la table Enseignant_Disponibilite
    function inserer_enseignant($date, $heure_debut, $heure_fin, $nom_enseignant) {
        global $pdo;
        
        // Préparer la requête d'insertion
        $stmt = $pdo->prepare("INSERT INTO Enseignant_Disponibilite (jour, heureDebut, heureFin, nom_enseignant) VALUES (:date, :heureDebut, :heureFin, :nom_enseignant)");
        
        // Exécuter la requête avec les valeurs des paramètres
        $stmt->execute([
            ':date' => $date,
            ':heureDebut' => $heure_debut,
            ':heureFin' => $heure_fin,
            ':nom_enseignant' => $nom_enseignant
        ]);
    }

    // Fonction pour insérer les données d'un lieu dans la table Lieu_NonDisponible
    function inserer_lieu($date, $heure_debut, $heure_fin, $lieu_numero) {
        global $pdo;
        
        // Préparer la requête d'insertion
        $stmt = $pdo->prepare("INSERT INTO Lieu_NonDisponible (jour, heureDebut, heureFin, lieu_numero) VALUES (:date, :heureDebut, :heureFin, :lieu_numero)");
        
        // Exécuter la requête avec les valeurs des paramètres
        $stmt->execute([
            ':date' => $date,
            ':heureDebut' => $heure_debut,
            ':heureFin' => $heure_fin,
            ':lieu_numero' => $lieu_numero
        ]);
    }

    // Récupérer les données du formulaire
    $specialite = $_POST['specialite'];
    $periode = $_POST['periode'];

    // Traitement des données de période pour extraire la date de début et la date de fin
    list($dateDebut, $dateFin) = explode(" à ", $periode);

    // Traiter les données du planning et les insérer dans la base de données
    foreach ($planningFiltre as $exam) {
        // Récupérer les données de l'examen
        $date = $exam['date'];
        $heure_debut = $exam['heureDebut'];
        $heure_fin = $exam['heureFin'];

        // Insérer les données pour chaque enseignant
        foreach ($exam['enseignants'] as $enseignant) {
            inserer_enseignant($date, $heure_debut, $heure_fin, $enseignant);
        }

        // Insérer les données pour chaque lieu
        foreach ($exam['groupes'] as $groupe) {
            $lieu_numero = $groupe['numero_lieu'];
            inserer_lieu($date, $heure_debut, $heure_fin, $lieu_numero);
        }
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>





    <!-- My CSS -->
    <link rel="stylesheet" href="assets/css/planning.css">

    <title>Planning</title>
</head>

<body>


    <!-- SIDEBAR -->
    <section id="sidebar" class="hide">
        <a href="#" class="brand">
            <i class='bx bx-grid-alt'></i>
            <span class="text">Admin</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="dashboard.php">
                    <!-- <i class='bx bxs-dashboard' ></i> -->
                    <i class='bx bx-stats'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="enseignant.php">
                    <i class='bx bx-user'></i>
                    <span class="text">Enseignants</span>
                </a>
            </li>
            <li>
                <a href="etudiant.php">
                    <i class='bx bxs-group'></i>
                    <span class="text">Etudiants</span>
                </a>
            </li>
            <li>
                <a href="salle.php">
                    <i class='bx bxs-school'></i>
                    <span class="text">Salles</span>
                </a>
            </li>
            <li>
                <a href="module.php">
                    <i class='bx bx-file'></i>
                    <span class="text">Modules</span>
                </a>
            </li>
            <li>
                <a href="notifAdmin.php">
                    <i class='bx bx-message'></i>
                    <span class="text">Notifications</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="planning.php">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Generation Planning</span>
                </a>
            </li>
            <li>
                <a href="session.php?logout=true" class="logout" onclick="return logoutConfirmation()">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Déconnexion</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->



    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
            <a href="#" class="profile">
                <i class='bx bx-user'></i>
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <section id="dash">
                <div class="head-title">
                    <div class="left">
                        <h1>Dashboard</h1>
                        <ul class="breadcrumb">
                            <li>
                                <a href="#">Dashboard</a>
                            </li>
                            <li><i class='bx bx-chevron-right'></i></li>
                            <li>
                                <a class="active" href="#">Planning</a>
                            </li>
                        </ul>
                    </div>

                </div>

                <br>
                <!-- Dashboard content -->
                <h1>Génération du planning d'examens</h1>
                <button onclick="downloadExcel()">Télécharger Planning Excel</button>
                <button id="downloadBtn">Télécharger PDF</button>
                <!-- <button onclick="telechargerPlanningPDF()">Télécharger Planning PDF</button> -->

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <br>
                    <label for="specialite">Spécialité :</label>
                    <select name="specialite" id="specialite">
                        <?php
                        // Connexion à la base de données
                        $pdo = new PDO('mysql:host=localhost;dbname=myproject', 'root', '');
                        $stmt = $pdo->query('SELECT nom_specialite FROM specialite');
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['nom_specialite'] . "'>" . $row['nom_specialite'] . "</option>";
                        }
                        ?>
                    </select><br><br>

                    <label for="periode">Période des examens :</label>
                    <input type="text" id="periode" name="periode" placeholder="YYYY-MM-DD à YYYY-MM-DD"><br><br>
                    <input class="btn-outline-success" type="submit" name="submit" value="Générer le planning">
                    <input class="btn-outline-success" type="submit" value="Enregistrer" name="enregistrer">
                </form>

                <div class="table-data">
                    <div class="order">
                        <div class="head">
                            <h3>Generation de planning</h3>
                            
                            <i class='bx bx-filter'></i>
                        </div>


                        <?php
                        // Tri du tableau $randomPlanning par la clé 'date'
                        usort($planningFiltre, function ($a, $b) {
                            return strtotime($a['date']) - strtotime($b['date']);
                        });

                        ?>
                        <!-- Affichage du planning d'examens généré -->
                        <?php if (isset($planningFiltre) && !empty($planningFiltre)): ?>
                            <h2>Planning pour la spécialité <?php echo $specialite; ?> dans la période du
                                <?php echo $dateDebut; ?> au <?php echo $dateFin; ?>
                            </h2>
                            <br>
                            <table id="tableauPlanning">
                                <tr>
                                    <th>Date et Heure</th>
                                    <th>Module</th>
                                    <!-- <th>Sections et Groupes</th> -->
                                    <th>Lieu</th>
                                    <!-- <th>Lieu</th> -->
                                    <th>Surveillants</th>
                                </tr>
                                <?php foreach ($planningFiltre as $exam): ?>
                                    <tr>
                                        <td><?php echo $exam['date']; ?> <br> <?php echo " de " . $exam['heureDebut']; ?> <br>
                                            <?php echo " à " . $exam['heureFin']; ?> </td>
                                        <td><?php echo $exam['nom_module']; ?></td>
                                        <td>
                                            <?php
                                            // Récupérer les sections et groupes
                                            foreach ($exam['sections'] as $section) {
                                                echo "<strong>Section: " . $section['section'] . "</strong><br>";
                                                foreach ($exam['groupes'] as $groupe) {
                                                    if ($groupe['section'] == $section['section']) {
                                                        echo "Groupe: " . $groupe['nom_groupe'] . " (" . $groupe['numero_lieu'] . ")<br>";
                                                    }
                                                }
                                                echo "<br>";
                                            }
                                            ?>
                                        </td>
                                        <!-- Afficher le type de lieu et le numéro -->
                                        <td>
                                            <?php foreach ($exam['enseignants'] as $enseignant): ?>
                                                <?php echo $enseignant; ?><br>
                                            <?php endforeach; ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

            </section>

        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script>
        function logoutConfirmation() {
            if (confirm("Êtes-vous sûr de vouloir vous déconnecter ?")) {
                return true; // Si l'utilisateur confirme, la déconnexion se produit
            } else {
                return false; // Si l'utilisateur annule, la déconnexion est annulée
            }
        }
    </script>

    <script>
        function downloadExcel() {
            // Récupérer le tableau
            var table = document.querySelector('table');

            // Convertir le tableau en format Excel
            var wb = XLSX.utils.table_to_book(table);

            // Générer le nom du fichier
            var fileName = 'planning_examens.xlsx';

            // Convertir le workbook en binaire Excel
            var binaryData = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

            // Créer un objet Blob pour le contenu binaire
            var blob = new Blob([s2ab(binaryData)], { type: 'application/octet-stream' });

            // Créer un objet URL à partir du Blob
            var url = URL.createObjectURL(blob);

            // Créer un lien temporaire et déclencher le téléchargement
            var a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            a.click();

            // Libérer l'URL
            URL.revokeObjectURL(url);
        }

        // Fonction pour convertir la chaîne en tableau de bytes
        function s2ab(s) {
            var buf = new ArrayBuffer(s.length);
            var view = new Uint8Array(buf);
            for (var i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
            return buf;
        }

    </script>

    <script>
        // JavaScript pour télécharger le PDF
        document.getElementById("downloadBtn").addEventListener("click", function () {
            var table = document.getElementById("tableauPlanning");
            var html = table.outerHTML;

            // Convertit le HTML en PDF
            var pdf = new jsPDF();
            pdf.fromHTML(html, 15, 15);

            // Télécharge le PDF
            pdf.save("table.pdf");
        });
    </script>

    <!-- Bibliothèque jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
</body>

<script src="assets/js/planning.js"></script>
</body>

</html>
