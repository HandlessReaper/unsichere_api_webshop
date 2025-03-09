<?php

session_start();
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<title>Warenkorb - SHOP</title>
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

		function removeFromChart(productid){
			let chartCookie = document.cookie.split(';').find(row => row.trim().startsWith('chart='));
			let chart = chartCookie ? chartCookie.split('=')[1].split(',') : [];
			chart = chart.filter(id => id != productid);
			document.cookie = "chart=" + chart.join(',') + "; path=/";
			location.reload();
		}
	</script>

	<div class="topsection">
		<h1 onclick="window.location.href='index.php';" style="cursor: pointer;">Willkommen beim Shop, <?php echo $_SESSION['user'] ?></h1>
		<h2>Das ist Ihr Warenkorb</h2>
		<img src="chart.png" alt="" width="20px">
		<span id="itemcount">0</span>
		<a href="login.php?logout=true" id="logoutbutton">Abmelden</a>
	</div>

	<div class="chart">
	<button class="globalButton" onclick="window.location.href='checkout.php';" style="cursor: pointer;">Zur Kasse gehen</button>

		<?php

			function debug_to_console($data) {
				$output = $data;
				if (is_array($output))
					$output = implode(',', $output);

				echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
			}

			// Check if Chart Cookie is set
			if(!isset($_COOKIE['chart'])){
				echo "Ihr Warenkorb ist leer.";
				exit();
			}

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
				foreach($products as $product){
					echo "<div class='productCHART'>";
					echo "<img src='".$product['thumbnail']."' class='productimage' alt='Produktbild konnte nicht geladen werden.'>";
					echo "<h3 class='product_name'>".$product['name']."</h3>";
					echo "<p class='product_ammount' style='text-align: center;'>Anzahl: ".getAmmount($product['id'])."</p>";
					echo "<p class='product_descCHART' style='text-align: center; width: 100%;'>".$product['description']."</p>";
					echo "<p class='product_price' style='text-align: center;'>".$product['preis']."€</p>";
					echo "<button class='addToChartButton' style='text-align: center;' onclick='removeFromChart(".$product['id'].");'>Entfernen</button>";
					echo "</div>";
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
			
			
		?>

	</div>
	
</body>
</html>