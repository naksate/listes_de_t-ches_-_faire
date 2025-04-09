<?php


// Identifiants pour la base de donnée TP3SLAM4
$servername = "localhost";
$username = "root";
$password = "";
$myDB="ToDoList";

// Faire la conusername: nexion
$conn = new mysqli($servername, $username, $password, $myDB);

//Vérifier Connexion
if ($conn -> connect_error){
	die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";


//Récupérer les données du formulaire
$username = $_POST['username'];
$name = $_POST['name'];
$first_name = $_POST['first_name'];
$email = $_POST['email'];
$age = $_POST['age'];
$password = $_POST['password'];
$conf_password = $_POST['conf_password'];


// Vérifier champs et motdepasse

	if (!isset($_POST['username'], $_POST['name'], $_POST['first_name'], $_POST['email'], $_POST['age'], $_POST['password'], $_POST['conf_password']))	 {
        echo "Veuillez remplir tous les champs";
        } else {
            if ($password === $conf_password) {
                echo "Inscription réussie, les MDP Correspondent";

                // Hasher le MDP
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Requête pour insérer dans la base de données les données du formulaire
                $sql = "INSERT INTO users (username, first_name, name, email, age, password) VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssis", $username, $first_name, $name, $email, $age, $hashed_password);

                if ($stmt->execute()) {
                    echo "Les données ont été ajoutées dans la base User.";
                } else {
                    echo "Les données n'ont pas pu être ajoutées dans la base Utilisateur : " . $conn->error;
                }

            } else {
                echo "Les mots de passe ne correspondent pas";
            }
    }

?>
	<a href="index.html"><br>Cliquez pour retourner à la page d'accueil</a>




