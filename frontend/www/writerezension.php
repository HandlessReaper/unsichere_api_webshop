<?php
session_start();
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
	exit();
}

if(isset($_POST['submitrez'])){
	$reztext = $_POST['reztext'];
	$stars = $_POST['stars'];
	$pid = $_POST['pid'];

	$rezdata = array(
		"product_id" => $pid,
		"rating" => $stars,
		"review_text" => $reztext,
	);

	$curlurl = "http://host.docker.internal:8080/reviews";

	if (!isset($_COOKIE['token']) || empty($_COOKIE['token'])) {
		echo "Fehler: Kein Token vorhanden. Bitte erneut einloggen.";
		exit();
	}

	$headers = [
		'Content-Type: application/json',
		'Authorization: Bearer ' . $_COOKIE['token']
	];

	$curl = curl_init($curlurl);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($rezdata));

	$response = curl_exec($curl);
	$http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$curl_error = curl_error($curl);
	curl_close($curl);

	if($http_response_code != 200){
		echo "Fehler beim Absenden der Rezension.<br>";
		echo "HTTP-Code: " . $http_response_code . "<br>";
		echo "Antwort: " . $response . "<br>";
		echo "cURL-Fehler: " . $curl_error . "<br>";
		exit();
	}

	echo "<script>location.href='productinfo.php?productid=".$pid."'</script>";
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<title>Rezension verfassen - SHOP</title>
	<link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3515/3515146.png" type="image/png">
</head>
<body onload="updateChart();">

	<div class="topsection">
		<h1 onclick="window.location.href='index.php';" style="cursor: pointer;">Willkommen beim Shop, <?php echo $_SESSION['user'] ?></h1>
		<h2><?php ?></h2>
		<img src="chart.png" alt="" width="20px">
		<span id="itemcount">0</span>
		<a href="login.php?logout=true" id="logoutbutton">Abmelden</a>
	</div>

	<div class="chart">

		<?php

			$productid = $_GET['pid'];
            $curlurl = "http://host.docker.internal:8080/products/".$productid;
            $curl = curl_init($curlurl);
            curl_setopt($curl, CURLOPT_POST, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($curl);
            $http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if($http_response_code != 200){
                echo "Fehler beim Laden der Produktinformationen.";
                exit();
            }

            $products = json_decode(curl_exec($curl), true);
            foreach($products as $product){
                echo "<div class='product'>";
                echo "<img src='".$product['thumbnail']."' class='productimage' alt='Produktbild konnte nicht geladen werden.' stlye='width: 100px;'>";
                echo "<h3 class='product_name'>".$product['name']."</h3>";
                echo "<p class='product_desc'>".$product['description']."</p>";
                echo "<p class='product_price'>".$product['preis']."€</p>";

                echo "<h3>Rezension verfassen</h3>";
            }
		?>

		<form action="writerezension.php" method="POST" class="review-form">
			<label for="stars">Sterne: <span id="sval">3</span></label>
			<input type="range" name="stars" id="stars" min="0" max="5" step="1" value="3" oninput="document.getElementById('sval').innerHTML=this.value" required>
			<br>
			<label for="reztext">Ihr Meinung</label>
			<br>
			<textarea name="reztext" id="reztext" cols="30" rows="10" required></textarea>
			<input type="hidden" name="pid" value="<?php echo $productid; ?>">
			<input type="submit" value="Rezension absenden" class="globalButton" name="submitrez">
		</form>

	</div>
	
</body>
</html>