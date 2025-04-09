<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    echo "Veuillez vous conneter";
    header("Location: connexion.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idTodo'])) {
    $_SESSION['idTodo'] = $_POST['idTodo'];  // Sauvegarde en session

    // Récupérer l'ID de la liste
    $idTodo = $_SESSION['idTodo']; 
} elseif (isset($_SESSION['idTodo'])) {
    $idTodo = $_SESSION['idTodo'];  // Utilisation de la session
} else {
    die("Erreur : idTodo non reçu.");
}


// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$myDB = "ToDoList";
$conn = new mysqli($servername, $username, $password, $myDB);

if ($conn->connect_error) {
    die("Connexion non réussie: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskTitle = $_POST['taskTitle'] ?? '';
    $taskDescription = $_POST['taskDescription'] ?? '';

    if (!empty($taskTitle) && !empty($taskDescription)) {
        $sql = "INSERT INTO Tasks (idTodo, Title, Description) 
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $idTodo, $taskTitle, $taskDescription);
        
        if ($stmt->execute()) {
            echo "Tâche ajoutée avec succès.";
        } else {
            echo "Erreur lors de l'ajout de la tâche.";
        }
    } else {
        echo "Veuillez remplir tous les champs.";
    }
} 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Tâche</title>
    <link rel="stylesheet" href="ajouter_tache.css">
</head>
<body>
    <h1>Ajouter une Tâche à la Liste</h1>
    <form method="post" action="ajouter_tache.php?idList=<?php echo $idTodo; ?>">
        <label for="taskTitle">Titre de la tâche :</label>
        <input type="text" name="taskTitle" id="taskTitle" required><br>

        <label for="taskDescription">Description :</label>
        <textarea name="taskDescription" id="taskDescription" required></textarea><br>

        <input type="submit" value="Ajouter la tâche">
    </form>

    <a href="page_membre.php">Retour à la page d'accueil</a>
</body>
</html>
