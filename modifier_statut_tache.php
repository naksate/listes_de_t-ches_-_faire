<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$myDB = "ToDoList";
$conn = new mysqli($servername, $username, $password, $myDB);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUser'])) {
    echo "Accès interdit.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idTask = $_POST['idTask'];
    $current_status = $_POST['current_status'];

    // Déterminer le nouveau statut de la tâche
    if ($current_status === 'pending') {
        $new_status = 'in_progress';
    } elseif ($current_status === 'in_progress') {
        $new_status = 'done';
    } else {
        $new_status = 'pending'; // Si la tâche est déjà "done", on peut la remettre à "pending" si besoin
    }

    // Mettre à jour le statut de la tâche
    $sql = "UPDATE Tasks SET status = ? WHERE idTask = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $idTask);
    $stmt->execute();

    // Récupérer l'id de la liste à laquelle appartient la tâche
    $sql = "SELECT idTodo FROM Tasks WHERE idTask = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idTask);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $idTodo = $row['idTodo'];

    // Vérifier si toutes les tâches de la liste sont "done"
    $sql = "SELECT COUNT(*) AS total_tasks, 
                   SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done_tasks 
            FROM Tasks WHERE idTodo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idTodo);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['total_tasks'] == $row['done_tasks']) {
        // Si toutes les tâches sont "done", mettre la liste en statut "completed"
        $sql = "UPDATE Todos SET status = 'completed' WHERE idTodo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idTodo);
        $stmt->execute();
    } else {
        $sql = "UPDATE Todos SET status = 'active' WHERE idTodo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idTodo);
        $stmt->execute();
    }

    // Rediriger vers la page précédente ou la liste des tâches
    header("Location: page_membre.php"); // Change la redirection si nécessaire
    exit;
}

$conn->close();
?>
