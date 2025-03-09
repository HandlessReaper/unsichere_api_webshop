<?php

session_start();
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
}

$errorInputCheck = false;
$success = false;

if(isset($_GET['action'])){
	switch ($_GET['action']){
		case 'success':
			$success = true;
			echo "<script>location.href='thankyou.html'</script>";
			break;
		default:
			$errorInputCheck = true;
			echo "<script>console.log('Fehler: " . $_GET['action'] . "' );</script>";
			break;
		}
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<title>Kasse - SHOP</title>
	<link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3515/3515146.png" type="image/png">
</head>
<body onload="updateChart();">
	<script type="text/javascript">
		function updateChart(){
			let chartCookie = document.cookie.split(';').find(row => row.trim().startsWith('chart='));
			let chart = chartCookie ? chartCookie.split('=')[1].split(',').filter(id => id) : [];
			document.getElementById('itemcount').innerText = chart.length;

			if (chart.length === 0) {
				document.cookie = 'chart=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
			}
		}
	</script>

	<div class="topsection">
		<h1 onclick="window.location.href='index.php';" style="cursor: pointer;">Willkommen beim Shop, <?php echo $_SESSION['user'] ?></h1>
		<h2>Kasse - Bitte geben Sie Ihre Daten ein</h2>
		<img src="chart.png" alt="" width="20px">
		<span id="itemcount">0</span>
		<a href="login.php?logout=true" id="logoutbutton">Abmelden</a>
	</div>

    <div class="itemoverview">
		<table>
			<tr>
				<th></th>
				<th>Produkt</th>
				<th>Anzahl</th>
				<th>Einzelpreis</th>
				<th>Gesamtpreis</th>
			</tr>
        <?php

           // Check if Chart Cookie is set
			if(!isset($_COOKIE['chart'])){
				echo "Ihr Warenkorb ist leer.";
				exit();
			}

            $total = 0;
			$items = "";
			// Build List of Producs in Chart
			$chart = explode(",", $_COOKIE['chart']);
			$chartItems = array_unique($chart);
			
			// Get Products from API and display
			if (strlen($_COOKIE['chart']) < 1) {
				echo "<p style='padding-top: 200px'>Ihr Warenkorb ist leer.</p>";
				exit();
			} else {
				foreach ($chartItems as $chartProduct) {
					displayProduct($chartProduct);
				}
			}

			function getAmmount($productID){
				$chart = explode(",", $_COOKIE['chart']);
				$ammount = 0;
				foreach($chart as $product){
					if($product == $productID){
						$ammount++;
					}
				}
				return $ammount;
			}

			function displayProduct($productID){
				$curlurl = "http://host.docker.internal:8080/products/".$productID;
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
					echo "<td>".getAmmount($product['id'])."</td>";
					echo "<td>".$product['preis']." €</td>";
					echo "<td>".number_format((getAmmount($product['id']) * $product['preis']), 2, ".", ",")." €</td>";
                    $GLOBALS["total"] += ($product['preis'] * getAmmount($product['id']));
					$GLOBALS["items"] .= $product['id'].":".getAmmount($product['id']).",";
				}
				echo "</tr>";
			}

           echo "<tr><td></td><td></td><td>Gesamt:</td><td><b>".number_format($total, 2, ".", ",")." €</b></td><td></td></tr>";
        ?>
		</table>
    </div>

	<div class="checkout">
        <form action="checkoutprog.php" method="post">
            <input type="hidden" name="items" value="<?php echo $items ?>">
			<input type="hidden" name="total" value="<?php echo $total ?>">
            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="address" placeholder="Adresse" required>
            <input type="text" name="city" placeholder="Stadt" required>
            <input type="text" name="zip" placeholder="PLZ" required>
			<p id="errorUser" style="display: <?= $errorInputCheck ? 'block' : 'none'; ?>; color: rgb(255, 55, 55);" style="padding-left: 100px;">Eingaben nicht valide. Bitte Eingaben überprüfen.</p>
            <input type="submit" value="Jetzt Bestellen" class="globalButton">
        </form>
	
</body>
</html>