<?php

session_start();	

// Connexion DB, Vérification
$servername = "localhost";
$username = "root";
$password = "";
$myDB="ToDoList";
$conn = new mysqli($servername, $username, $password, $myDB);
if ($conn -> connect_error){
	die("Connexion non réussie " . $conn->connect_error);
} else {
	echo "Connexion à la base réussie";
}

// Récupérer email et mot de passe de connexion.html
$email = $_POST['email'];
$password = $_POST['password'];

// Vérifier champs
if (!isset($_POST['email'], $_POST['password'])) {
	echo "Veuillez remplir tous les champs";
} else {
	// Si champs remplis, vérifier si utilisateur existe
	$sql = "SELECT idUser, username, password FROM Users WHERE email = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $email);
	$stmt->execute(); 
	$stmt->store_result();

	if ($stmt->num_rows > 0) {
		$stmt->bind_result($idUser, $username, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Mot de passe correct → Création de la session utilisateur
            $_SESSION['idUser'] = $idUser;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            echo "Compte reconnu";

		// Cookie
		$temps = 365*24*3600;
        	setcookie("cookieid", $_POST['email'], time()+$temps);

		//echo "Session démarée, redirection redirection_role_membre";
		//sleep(2);
		header("Location: page_membre.php");
		//exit();

	} else {
		//echo "Comptae non approuvé et/ou MDP, email non vérifiés";
		//sleep(2);
		header("Location: index.html");
		//exit();
	}
	} else {
		echo "Email MDP pas vérifiés";
	}
}
?>