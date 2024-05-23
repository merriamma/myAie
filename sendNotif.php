<?php
include 'connection.php';

session_start();

try {
    $myproject = new PDO("mysql:host=localhost;dbname=myproject;charset=utf8", "root", "");
    $myproject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('<p class="text-danger">Erreur de connexion à la base de données : ' . $e->getMessage() . '</p>');
}

if (isset($_POST['nom']) && isset($_POST['notification_name']) && isset($_POST['message'])) {
    $nom = $_POST['nom'];
    $notification_name = $_POST['notification_name'];
    $message = $_POST['message'];

    // Récupérer l'ID du professeur à partir de son nom
    $query = "SELECT nom FROM enseignant WHERE nom = ?";
    $stmt = $myproject->prepare($query);
    $stmt->execute([$nom]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $nomProf = $row['nom'];

        // Insérer la notification
        $insert_notification = "INSERT INTO notification (notification_name, message, active) VALUES (?, ?, 1)";
        $stmt = $myproject->prepare($insert_notification);
        $stmt->execute([$notification_name, $message]);
        $notification_id = $myproject->lastInsertId();

        // Lier la notification au professeur
        $link_notification_prof = "INSERT INTO notification_enseignant (notification_id, nom_enseignant) VALUES (?, ?)";
        $stmt = $myproject->prepare($link_notification_prof);
        $stmt->execute([$notification_id, $nomProf]);

        header("Location: notifAdmin.php?success=1");
        exit();
    } else {
        echo "No such teacher found.";
    }
} else {
    echo "Please fill in all fields.";
}
