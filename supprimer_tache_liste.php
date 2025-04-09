<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: connexion.html");
    exit;
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$myDB = "ToDoList";
$conn = new mysqli($servername, $username, $password, $myDB);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Suppression d'une liste (et ses tâches associées)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_list"])) {
    $idTodo = $_POST["idTodo"];

    // Supprimer les tâches associées
    $sqlTasks = "DELETE FROM Tasks WHERE idTodo = ?";
    $stmtTasks = $conn->prepare($sqlTasks);
    $stmtTasks->bind_param("i", $idTodo);
    $stmtTasks->execute();
    $stmtTasks->close();

    // Supprimer la liste
    $sqlTodo = "DELETE FROM Todos WHERE idTodo = ?";
    $stmtTodo = $conn->prepare($sqlTodo);
    $stmtTodo->bind_param("i", $idTodo);
    $stmtTodo->execute();
    $stmtTodo->close();

    header("Location: page_membre.php");
    exit;
}

// Suppression d'une tâche spécifique
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_task"])) {
    $idTask = $_POST["idTask"];

    $sqlTask = "DELETE FROM Tasks WHERE idTask = ?";
    $stmtTask = $conn->prepare($sqlTask);
    $stmtTask->bind_param("i", $idTask);
    $stmtTask->execute();
    $stmtTask->close();

    header("Location: page_membre.php");
    exit;
}

$conn->close();
?>
