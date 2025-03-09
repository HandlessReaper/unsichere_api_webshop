<?php

session_start();
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
}

if (isset($_COOKIE['purchases'])) {
	$pairs = explode(',', rtrim($_COOKIE['purchases'], ','));
    unset($_COOKIE['purchases']);
    setcookie('purchases', '', time() - 3600, '/'); // empty value and old timestamp
}else{
	echo "<script>console.log('Cookie nicht gesetzt');</script>";
}

if (isset($_COOKIE['chart'])) {
    unset($_COOKIE['chart']);
    setcookie('chart', '', time() - 3600, '/'); // empty value and old timestamp
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<title>Dankeschön</title>
	<link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3515/3515146.png" type="image/png">
</head>
<body>
	<div class="itemoverview">
		<h1>Dankeschön für deine Bestellung. Sie wird in kürze verarbeitet.</h1>
		<p>Wenn Sie mögen, können Sie für Ihre bestellten Produkte eine Rezension hinterlassen.</p>
		<p>Ihre bestellten Produkte:</p>
		<table>
			<tr>
				<th></th>
				<th>Produkt</th>
				<th>Anzahl</th>
				<th>Einzelpreis</th>
				<th>Gesamtpreis</th>
				<th></th>
			</tr>
		<?php
			foreach($pairs as $pair){
				list($id, $amount) = explode(':', $pair);
				echo "<script>console.log('ID: " . $id . " Amount: ". $amount ."' );</script>";
				$curlurl = "http://host.docker.internal:8080/products/".$id;
				$curl = curl_init($curlurl);
				curl_setopt($curl, CURLOPT_POST, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

				$response = curl_exec($curl);
				$http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				curl_close($curl);

				if($http_response_code != 200){
					echo "Fehler beim Laden der Produkte im Warenkorb";
					exit();
				}

				$products = json_decode(curl_exec($curl), true);
				echo "<tr>";
				foreach($products as $product){
					echo "<td><img src='".$product['thumbnail']."' alt='Produktbild konnte nicht geladen werden.'></td>";
					echo "<td>".$product['name']."</td>";
					echo "<td>".$amount."</td>";
					echo "<td>".$product['preis']." €</td>";
					echo "<td>".number_format(($amount * $product['preis']), 2, ".", ",")." €</td>";
					echo "<td><a href='writerezension.php?pid=".$id."' target='_blank'>Eine Rezension verfassen</a></td>";
				}
				echo "</tr>";
			}
		?>
		</table>
		<br>
		<a href="index.php">Zurück zum Shop</a>
	</div>
</body>
</html>