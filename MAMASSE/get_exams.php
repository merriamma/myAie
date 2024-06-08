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
    return $array[array_rand($array)];
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

// Generate and insert exams only if they don't exist
if ($check_result['count'] == 0) {
    $module_count = count($modules);
    foreach ($exam_dates as $index => $exam_date) {
        if ($index >= $module_count) break; // Stop if we run out of modules

        $location = getRandomElement($locations);
        $timeslot = getRandomElement($timeslots);
        $module = $modules[$index]; // Use modules in order instead of randomly

        $insert_query = "INSERT INTO examen (id_module, date, heureDebut, heureFin, lieu_numero)
                        VALUES ({$module['id_module']}, '$exam_date', '{$timeslot['debut']}', '{$timeslot['fin']}', '$location')";
        $conn->query($insert_query);

        // Remove used location and timeslot to avoid duplicates
        $locations = removeElement($locations, $location);
        $timeslots = removeElement($timeslots, $timeslot);
    }
}

// Fetch and display exam schedule
$sql = "SELECT e.id, m.nom_module, e.date, e.heureDebut, e.heureFin, e.lieu_numero
        FROM examen e
        JOIN module m ON e.id_module = m.id_module
        WHERE m.nom_specialite = '$specialty' AND e.date BETWEEN '$startDate' AND DATE_ADD('$startDate', INTERVAL 6 DAY)
        ORDER BY e.date, e.heureDebut";

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
            </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["id"] . "</td>
                <td>" . $row["nom_module"] . "</td>
                <td>" . $row["date"] . "</td>
                <td>" . $row["heureDebut"] . "</td>
                <td>" . $row["heureFin"] . "</td>
                <td>" . $row["lieu_numero"] . "</td>
            </tr>";
    }
    echo "</table>";
} else {
    echo "No exams scheduled for $specialty between $startDate and " . date('Y-m-d', strtotime($startDate . ' +6 days')) . ".";
}

$conn->close();
?>