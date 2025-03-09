<?php

session_start();
if (isset($_SESSION['user'])) {
	session_destroy();
}
//Variablen, die im html ausgelesen werden, und bei neuladen der seite evtl. geändert werden
$errorUserObs = false;
$errorPwObs = false;
$showRedirectObs = false;
$redirectObs = "";
$showLogout = false;

//funktion um Objekte auf der Konsole auszugeben zu debugzwecken
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function handleLogin($username, $password) {
	global $errorUserObs;
	global $errorPwObs;
	global $showRedirectObs;
	global $redirectObs;
	debug_to_console("checkLogin betreten");
	$url = 'http://api:8080/users/login/';
	$data = ['username' => $username, 'password' => $password];
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	$response = curl_exec($curl);
	$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	debug_to_console("api_call beendet");
	debug_to_console($http_status);
	
	$response_data = json_decode($response, true);
	switch($http_status){
		case "200": //Alles ok, user angemeldet
			debug_to_console("200");
			$token = $response_data['token'];
			setcookie('token', $token); //No expire. Cookie wird gelöscht, wenn Browser geschlossen
			$_SESSION['user'] = $username;
			$_SESSION['usermail'] = $response_data['email'];
			echo "<script>location.href='index.php'</script>";
			break;
		case "401": //Passwort falsch
			debug_to_console("401");
			$errorPwObs = true;
			break;
		case "404":
			debug_to_console("404"); //User wurde nicht gefunden
			$errorUserObs = true; //"display: block;";
			$showRedirectObs = true;
			$redirectObs = "register.php?name=".$username."";
			break;
		case "500": //Server Error
			debug_to_console("500");
			echo '<script>alert("Internal Server Error")</script>';
		default:
			debug_to_console("default");
			break;
	}
	
	
}//handleLogin

function handleReg($username, $password, $email){
	$url = 'http://api:8080/users/register/';
	$data = ['username' => $username, 'password' => $password, 'email' => $email];
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	$response = curl_exec($curl);
	$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$response_data = json_decode($response, true);
	switch($http_status){
		case "201": //Alles Funktioniert, user registriert
			$token = $response_data['token'];
			setcookie('token', $token); //No expire. Cookie wird gelöscht, wenn Browser geschlossen
			$_SESSION['user'] = $username;
			$_SESSION['usermail'] = $response_data['email'];
			echo "<script>location.href='index.php';</script>";
			break;
		case "409": //User bereits registriert
			echo "<script>location.href='register.php?alrReg=true&name=".$username."&email=".$email."';</script>";
			break;
		case "500": //Server Error
			echo '<script>alert("Internal Server Error");</script>';
			break;
		default:
			break;
	}
	
}//handleReg

//Feld Action wird ausgewertet, wenn das Formular unten oder das in register.php abgeschickt wurde
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'login':
			debug_to_console("login recieved");
			$username = $_POST['username'];
			$password = $_POST['password'];
			handleLogin($username, $password);
			break;
		
		case 'register':
			$username = $_POST['username'];
			$email = $_POST['email'];
			$password = $_POST['pw'];
			$password_check = $_POST['pw-check'];
			if($password == $password_check){
				handleReg($username, $password, $email);
			}else{
				echo "<script>location.href='register.php?check=false&name=".$username."&email=".$email."';</script>";
			}
			break;

		default:
			break;
	}
}

//Abmeldebutton in Index.php führt zur Ausführung dieses Codes
if(isset($_GET['logout']) && $_GET['logout'] == "true"){
	if (isset($_COOKIE['token'])) {
		unset($_COOKIE['token']);
		setcookie('token', '', time() - 3600, '/'); // empty value and old timestamp
	}
	$showLogout = true;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<title>SHOP - Anmeldung</title>
	<link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3515/3515146.png" type="image/png">
	<script>
		function deleteCookie(){
			document.cookie = 'chart=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC';
		}
	</script>
</head>
<body onload="deleteCookie();">
	<div class="topsection">
		<h1>Willkommen beim Shop</h1>
		<h2>Bitte melden Sie sich an</h2>
	</div>
	<div class="login">
		<form action="login.php" method="post">
			<input type="hidden" name="action" value="login">
			<input type="text" name="username" placeholder="Benutzername" required autofocus>
			<input type="password" name="password" placeholder="Passwort" required>
			<input type="submit" value="Anmelden" class="globalButton">
			<p id="errorUser" style="display: <?= $errorUserObs ? 'block' : 'none'; ?>; color: rgb(255, 55, 55);"> Der eingegebene User wurde nicht gefunden</p>
			<p id="errorPw" style="display: <?= $errorPwObs ? 'block' : 'none'; ?>; color: rgb(255, 55, 55);"> Das Eingegebene Passwort stimmt nicht</p>
		</form>
	</div>
	<div class="forgotpasswordcontainer">			
		<p id="showRedirect" style="display: <?= $showRedirectObs ? 'block' : 'none'; ?>;">Benutzername nicht gefunden&nbsp;<a id="redirect" href=<?= $redirectObs; ?>>Jetzt Registrieren</a></p>
		<p id="showRedirect" style="display: <?= $showLogout ? 'block' : 'none'; ?>;">Sie wurden erfolgreich abgemeldet</p>
	</div>
	<div id="logoutPopup" class="popup" popover>
		<h4>Sie wurden erfolgreich Abgemeldet</h4>
		<button onclick="document.getElementById('logoutPopup').togglePopover();">schließen</button>
	</div>
	<?php
		if ($showLogout == true) {
			echo "<script>document.getElementById('logoutPopup').togglePopover();</script>";
		}
	?>
</body>
</html>