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
	<title>SHOP</title>
	<link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3515/3515146.png" type="image/png">
</head>
<body onload="updateChart();">
	<script type="text/javascript"> // TODO
			function updateChart(){
			let chartCookie = document.cookie.split(';').find(row => row.trim().startsWith('chart='));
			let chart = chartCookie ? chartCookie.split('=')[1].split(',').filter(id => id) : [];
			document.getElementById('itemcount').innerText = chart.length;

			if (chart.length === 0) {
				document.cookie = 'chart=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
			}
		}

		function addToChart(productid){
			let chartCookie = document.cookie.split(';').find(row => row.trim().startsWith('chart='));
			let chart = chartCookie ? chartCookie.split('=')[1].split(',') : [];
			chart.push(productid);
			document.cookie = 'chart=' + chart.join(',') + '; path=/';
			updateChart();
		}
	</script>

	<script type="text/javascript">
		function setActive(originelement){
			Array.prototype.forEach.call(document.getElementsByClassName('liactive'), function(element) {
				element.classList.remove('liactive');
			});
			originelement.classList.add('liactive');
		}

		function filerElements(filter){
			Array.prototype.forEach.call(document.getElementsByClassName('product'), function(element) {
				if(filter == 'all'){
					element.style.display = 'flex';
				}else if(element.getElementsByClassName('product_name')[0].innerText.includes(filter)){
					element.style.display = 'flex';
				}else{
					element.style.display = 'none';
				}
			});
		}
	</script>

	<div class="topsection">
		<h1>Willkommen beim Shop, <?php echo $_SESSION['user'] ?></h1>
		<h2>Hier kannst du durch verschiedene Abschlussarbeiten stöbern. Viel Spaß beim Einkaufen.</h2>
		<img src="chart.png" alt="" width="20px" onClick="window.location.href = 'chart.php'" style="cursor: pointer;">
		<span id="itemcount">0</span>
		<a href="login.php?logout=true" id="logoutbutton">Abmelden</a>
	</div>

	<div class="filters">
		<li onclick="setActive(this); filerElements('all');">Alles zeigen</li>
		<li onclick="setActive(this); filerElements('Bachelor');">Bachelorarbeiten</li>
		<li onclick="setActive(this); filerElements('Master');">Masterarbeiten</li>
		<li onclick="setActive(this); filerElements('Disser');">Dissertationen</li>
	</div>

	<div class="products">
		<?php
			function debug_to_console($data) {
				$output = $data;
				if (is_array($output))
					$output = implode(',', $output);

				echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
			}

			// Get Products from API and display
			$curlurl = "http://host.docker.internal:8080/products";
			$curl = curl_init($curlurl);
			curl_setopt($curl, CURLOPT_POST, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

			$response = curl_exec($curl);
			$http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

			if($http_response_code != 200){
				echo "Fehler beim Laden der Produkte";
				exit();
			}

			$products = json_decode(curl_exec($curl), true);
			foreach($products as $product){
				echo "<div class='product'>";
				echo "<img src='".$product['thumbnail']."' class='productimage' alt='Produktbild konnte nicht geladen werden.'>";
				echo "<h3 class='product_name'>".$product['name']."</h3>";
				echo "<p class='product_desc'>".$product['description']."</p>";
				echo "<p class='product_price'>".$product['preis']."€</p>";
				//echo "<p class='product_stock'>".$product['stock']." Stück verfügbar</p>";
				echo "<button class='viewButton' onclick='window.location.href=\"productinfo.php?productid=".$product['id']."\"'>Ansehen</button>";
				echo "<br>";
				echo "<button class='addToChartButton' onclick='addToChart(".$product['id'].");'>In den Warenkorb</button>";
				echo "</div>";
			}
			
		?>
	</div>
	
</body>
</html>