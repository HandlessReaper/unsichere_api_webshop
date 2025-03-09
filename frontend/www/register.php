<?php

session_start();

$errorPw = false;
$emailValue = "";
$alreadyRegistered = false;

//Wenn die Login.php fehler in der Eingabe erkennt, werden diese hier ausgewertet und der user entsprechend informiert
if(isset($_GET['check'])){
    if($_GET['check'] == "false"){
        $errorPw = true;
        $emailValue = $_GET['email'];
    }
}
if(isset($_GET['alrReg']) && $_GET['alrReg'] == "true"){
    $alreadyRegistered = true;
    $emailValue = $_GET['email'];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<title>SHOP - Registrierung</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3515/3515146.png" type="image/png">
</head>
<body>
	<div class="topsection">
		<h1>Willkommen beim Shop</h1>
		<h2>Bitte Registrieren Sie sich als Nutzer</h2>
	</div>
    <div class="register">
        <form action="login.php" method="post">
            <input type="hidden" name="action" value="register">
            <input type="text" name="username" value="<?= $_GET['name']?>" required>
            <input type="text" name="email" placeholder="E-mail-Adresse" value= "<?= $emailValue ?>" required>
            <input type="password" name="pw" placeholder="Passwort" required>
            <input type="password" name="pw-check" placeholder="Passwort wiederholen" required>
            <input type="submit" value="Registrieren" class="globalButton">
        </form>
    </div>
    <div class="forgotpasswordcontainer">
        <p id="errorPw" style="display: <?= $errorPw ? 'block' : 'none'; ?>; color: rgb(255, 55, 55);">Die eingegebenen Passwörter stimmen nicht überein</p>
        <p id="errorReg" style="display: <?= $alreadyRegistered ? 'block' : 'none'; ?>; color: rgb(255, 55, 55);">Dieser Nutzer wurde bereits registriert</p>
    </div>
</body>
</html>