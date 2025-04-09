<?php
session_start();

// Vérifier la session de l'utilisateur
if (!isset($_SESSION['username'])) {
    echo "Accès interdit.";
    header("Location: connexion.html");
    exit;
} else {
    echo "Bienvenue, membre " . $_SESSION['username'];
}

// Récupérer id User
$idUser = $_SESSION['idUser'];

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$myDB = "ToDoList";
$conn = new mysqli($servername, $username, $password, $myDB);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupérer les listes créées par l'utilisateur
$sql = "SELECT idTodo, title FROM Todos WHERE idUser = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUser);
$stmt->execute();
$resultTodos = $stmt->get_result();

// Récupérer toutes les listes et leurs tâches associées
$sql = "SELECT T.idTodo, T.title, Tk.description 
        FROM Todos T
        LEFT JOIN Tasks Tk ON T.idTodo = Tk.idTodo
        WHERE T.idUser = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUser);
$stmt->execute();
$resultTodosTasks = $stmt->get_result();

// Récupérer tâches, listes, dates de création status
$sql = "SELECT T.idTodo, T.title, T.created_at AS list_created_at, T.status AS list_status,
               Tk.idTask, Tk.description, Tk.created_at AS task_created_at, Tk.status AS task_status
        FROM Todos T
        LEFT JOIN Tasks Tk ON T.idTodo = Tk.idTodo
        WHERE T.idUser = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUser);
$stmt->execute();
$resultTodosTasks = $stmt->get_result();

// Explication: Récupérer toutes les listes et leurs tâches associées avec le Titre, Description, Created_at, status



/* Organiser les données
$todos = [];
while ($row = $resultTodosTasks->fetch_assoc()) {
    $idTodo = $row['idTodo'];
    $title = htmlspecialchars($row['title']);
    $description = $row['description'] ? htmlspecialchars($row['description']) : "Aucune tâche";

    // Grouper les tâches par liste
    if (!isset($todos[$idTodo])) {
        $todos[$idTodo] = ["title" => $title, "tasks" => []];
    }
    $todos[$idTodo]["tasks"][] = $description;
} */

// Organiser les données des listes
$todos = [];
while ($row = $resultTodosTasks->fetch_assoc()) {
    $idTodo = $row['idTodo'];
    $title = htmlspecialchars($row['title']);
    $list_created_at = $row['list_created_at'];
    $list_status = htmlspecialchars($row['list_status']); 
    $description = $row['description'] ? htmlspecialchars($row['description']) : "Pas de description car pas de tâche";
    // Htmlspecialchars permet de changer les caractères tels que: <> en $lt ou $gt pour éviter les attaques 
    $task_created_at = $row['task_created_at'] ? $row['task_created_at'] : "Pas de date car pas de tâche";
    $task_status = $row['task_status'] ? htmlspecialchars($row['task_status']) : "Pas de statut car pas de tâche";
    $idTask = $row['idTask']; // Récupération de l'idTask

    // Grouper les tâches par liste
    if (!isset($todos[$idTodo])) {
        $todos[$idTodo] = [
            "idTodo" => $idTodo,
            "title" => $title,
            "list_created_at" => $list_created_at ?? "Pas de date car pas de tâche",
            "list_status" => $list_status ?? "Pas de statut car pas de tâche",
            "tasks" => []
        ];
    }
    $todos[$idTodo]["tasks"][] = [
        "idTask" => $idTask, // Ajout de l'idTask
        "description" => $description,
        "task_created_at" => $task_created_at,
        "task_status" => $task_status
    ];
}



// Récupérer les données du formulaire pour ajouter un Post et ajouter à la DB
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $idUser = $_SESSION['idUser']; 

    $sql = "INSERT INTO Todos (title, start_date, end_date, idUser, status) VALUES (?, ?, ?, ?, 'active')";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssi", $title, $start_date, $end_date, $idUser);

        if ($stmt->execute()) {
            echo "Liste ajoutée avec succès !";
            header("Location: page_membre.php"); // Rediriger vers la page membre
        } else {
            echo "Erreur lors de l'ajout de la liste : " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Erreur de préparation de la requête : " . $conn->error;
    }
}




$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Liste</title>
    <link rel="stylesheet" href="pagemembre.css">

</head>
<body>

<h2>Ajouter une nouvelle liste </h2>

<form method="POST" action="page_membre.php">
    <label for="title">Titre de la liste :</label>
    <input type="text" id="title" name="title" required><br><br>

    <label for="start_date">Date de début :</label>
    <input type="date" id="start_date" name="start_date" required><br><br>

    <label for="end_date">Date de fin :</label>
    <input type="date" id="end_date" name="end_date" required><br><br>

    <input type="submit" value="Ajouter la liste">
</form>

<h2>Mes listes</h3>
<ul>
    <?php if ($resultTodos->num_rows > 0): ?>
        <?php while ($row = $resultTodos->fetch_assoc()) { ?>
            <li>
                <?= htmlspecialchars($row['title']) ?> -
                <form method="POST" action="ajouter_tache.php" class="bouton-ajouter_tache">
                    <input type="hidden" name="idTodo" value="<?= $row['idTodo'] ?>">
                    <button type="submit">Ajouter une tâche</button>
                </form>
            </li>
        <?php } ?>
    <?php else: ?>
        <li>Pas de listes ajoutées</li>
    <?php endif; ?>
</ul>


<h2>Mes listes et mes tâches</h4>
<ul>
    <?php foreach ($todos as $todo): ?>
        <li>

            <!-- Titre de la liste (Ajoutée le : ...) - Status -->
            <strong><?php echo $todo["title"]; ?></strong> 
            (Ajoutée le <?php echo $todo["list_created_at"] ?? "Inconnue"; ?>) - 
            <em>Statut : <?php echo $todo["list_status"] ?? "Non défini"; ?></em>

            <!-- Bouton pour supprimer une liste -->
            <form method="POST" action="supprimer_tache_liste.php" style="display:inline;">
                <input type="hidden" name="idTodo" value="<?php echo $todo["idTodo"]; ?>">
                <button type="submit" name="delete_list">🗑 Supprimer la liste</button>
            </form>



            <ul>

                <!-- Afficher pour chaque tâches Description de la tâche (Date d'ajout)-->
                <?php foreach ($todo["tasks"] as $task): ?>
                    <li>
                        <?php echo $task["description"]; ?> 
                        (Ajoutée le <?php echo $task["task_created_at"] ?? "Inconnue"; ?>) - 
                        <em>Statut : <?php echo $task["task_status"] ?? "Non défini"; ?></em> 

                        <?php if (isset($task["idTask"])): ?>
                            <!-- Bouton pour changer le statut -->
                            <form method="POST" action="modifier_statut_tache.php" style="display:inline;">
                                <input type="hidden" name="idTask" value="<?php echo $task["idTask"]; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $task["task_status"]; ?>">
                                <button type="submit">🔄 Changer le statut</button>
                            </form>

                            <!-- Bouton pour supprimer -->
                            <form method="POST" action="supprimer_tache_liste.php" style="display:inline;">
                                <input type="hidden" name="idTask" value="<?php echo $task["idTask"]; ?>">
                                <button type="submit" name="delete_task">🗑 Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>
    <?php endforeach; ?>
</ul>


</body>
</html>
