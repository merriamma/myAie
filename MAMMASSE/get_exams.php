<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myproject";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get a random element from an array
function getRandomElement($array) {
    if (!empty($array)) {
        $randomKey = array_rand($array);
        return $array[$randomKey];
    } else {
        return null;
    }
}

// Function to remove an element from an array
function removeElement($array, $element) {
    $index = array_search($element, $array);
    if ($index !== false) {
        unset($array[$index]);
    }
    return array_values($array);
}

// Fetch locations
$locations_query = "SELECT numero FROM lieu WHERE type_lieu = 'Amphithéâtre'";
$locations_result = $conn->query($locations_query);
$locations = [];
while ($row = $locations_result->fetch_assoc()) {
    $locations[] = $row['numero'];
}

// Fetch time slots
$timeslots_query = "SELECT debut, fin FROM horaire";
$timeslots_result = $conn->query($timeslots_query);
$timeslots = [];
while ($row = $timeslots_result->fetch_assoc()) {
    $timeslots[] = ['debut' => $row['debut'], 'fin' => $row['fin']];
}

// Get the requested specialty and start date
$specialty = $_POST['specialty'];
$startDate = $_POST['startDate'];

// Generate array of 7 consecutive exam dates starting from the given date
$exam_dates = [];
$current = strtotime($startDate);
for ($i = 0; $i < 7; $i++) {
    $exam_dates[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}

// Fetch groups for the specialty
$groups_query = "SELECT nom_groupe, section FROM groupe WHERE nom_specialite = '$specialty' ORDER BY section, nom_groupe";
$groups_result = $conn->query($groups_query);

$groups = [];
while ($row = $groups_result->fetch_assoc()) {
    $groups[] = [
        'nom_groupe' => $row['nom_groupe'],
        'section' => $row['section']
    ];
}

// Fetch modules for the specialty
$modules_query = "SELECT id_module, nom_module FROM module WHERE nom_specialite = '$specialty'";
$modules_result = $conn->query($modules_query);

$modules = [];
while ($row = $modules_result->fetch_assoc()) {
    $modules[] = [
        'id_module' => $row['id_module'],
        'nom_module' => $row['nom_module']
    ];
}
// Check if exams already exist for this specialty in the given date range
$check_query = "SELECT COUNT(*) as count FROM examen e 
               JOIN module m ON e.id_module = m.id_module 
               WHERE m.nom_specialite = '$specialty' AND e.date BETWEEN '$startDate' AND DATE_ADD('$startDate', INTERVAL 6 DAY)";
$check_result = $conn->query($check_query)->fetch_assoc();


function hasConflict($conn, $date, $startTime, $endTime, $location, $specialty) {
    $query = "SELECT COUNT(*) as count FROM examen e 
              JOIN module m ON e.id_module = m.id_module 
              WHERE m.nom_specialite = '$specialty' 
              AND e.date = '$date' 
              AND e.heureDebut <= '$endTime' 
              AND e.heureFin >= '$startTime'
              AND e.lieu_numero = '$location'";
    $result = $conn->query($query)->fetch_assoc();
    return $result['count'] > 0;
}


// Generate and insert exams only if they don't exist
if ($check_result['count'] == 0) {
    $module_count = count($modules);
    foreach ($exam_dates as $index => $exam_date) {
        if ($index >= $module_count) break; // Stop if we run out of modules
    
        $module = $modules[$index]; // Use modules in order
        $timeslot = getRandomElement($timeslots);
    
        // Iterate over groups, checking for conflicts for each
        foreach ($groups as $group) {
            $location = getRandomElement($locations);
    
            // Check if the location exists in the lieu table
            $check_location_query = "SELECT COUNT(*) FROM lieu WHERE numero = '$location' AND type_lieu = 'Amphithéâtre'";
            $check_location_result = $conn->query($check_location_query);
            $location_exists = $check_location_result->fetch_array()[0] > 0;
    
            if ($location_exists) {
                // Check for conflicts before inserting
                if (!hasConflict($conn, $exam_date, $timeslot['debut'], $timeslot['fin'], $location, $specialty)) {
                    try {
                        $insert_query = "INSERT INTO examen (id_module, date, heureDebut, heureFin, lieu_numero, nom_groupe)
                                        VALUES ({$module['id_module']}, '$exam_date', '{$timeslot['debut']}', '{$timeslot['fin']}', '$location', '{$group['nom_groupe']}')";
                        $conn->query($insert_query);
    
                        // Remove used location to avoid duplicates
                        $locations = removeElement($locations, $location);
                    } catch (mysqli_sql_exception $e) {
                        // Handle the exception
                        echo "Error: " . $e->getMessage();
                        // You can also log the error or take other actions
                    }
                } else {
                    // If there's a conflict, try a different location or timeslot
                    // Implement your conflict resolution strategy here (e.g., find another available location)
                }
            } else {
                // Handle the case where the location doesn't exist
                // You can either skip inserting this exam record or choose a different location
            }
        }
    
        // Remove used timeslot to avoid duplicates
        $timeslots = removeElement($timeslots, $timeslot);
    }
}

// Fetch and display exam schedule
$sql = "SELECT e.id, m.nom_module, e.date, e.heureDebut, e.heureFin, e.lieu_numero, e.nom_groupe, g.section
        FROM examen e
        JOIN module m ON e.id_module = m.id_module
        JOIN groupe g ON e.nom_groupe = g.nom_groupe AND g.nom_specialite = '$specialty'
        WHERE m.nom_specialite = '$specialty' AND e.date BETWEEN '$startDate' AND DATE_ADD('$startDate', INTERVAL 6 DAY)
        ORDER BY e.date, e.heureDebut, m.nom_module, g.section, e.nom_groupe";

$result = $conn->query($sql);

echo "<h2>$specialty - Exams from $startDate to " . date('Y-m-d', strtotime($startDate . ' +6 days')) . "</h2>";

if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Module</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Location</th>
                <th>Group</th>
                <th>Section</th>
            </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["id"] . "</td>
                <td>" . $row["nom_module"] . "</td>
                <td>" . $row["date"] . "</td>
                <td>" . $row["heureDebut"] . "</td>
                <td>" . $row["heureFin"] . "</td>
                <td>" . $row["lieu_numero"] . "</td>
                <td>" . $row["section"] . "</td>
                <td>" . $row["nom_groupe"] . "</td>
            </tr>";
    }
    echo "</table>";
} else {
    echo "No exams scheduled for $specialty between $startDate and " . date('Y-m-d', strtotime($startDate . ' +6 days')) . ".";
}