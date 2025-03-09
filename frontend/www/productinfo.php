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
	<title>Produktinformationen - SHOP</title>
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

        function addToChart(productid){
			let chartCookie = document.cookie.split(';').find(row => row.trim().startsWith('chart='));
			let chart = chartCookie ? chartCookie.split('=')[1].split(',') : [];
			chart.push(productid);
			document.cookie = 'chart=' + chart.join(',') + '; path=/';
			updateChart();
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
		<h2><?php ?></h2>
		<img src="chart.png" alt="" width="20px" onClick="window.location.href = 'chart.php'" style="cursor: pointer;">
		<span id="itemcount">0</span>
		<a href="login.php?logout=true" id="logoutbutton">Abmelden</a>
	</div>

	<div class="chart">

		<?php

            function debug_to_console($data) {
                $output = $data;
                if (is_array($output))
                    $output = implode(',', $output);

                echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
            }

            // Funktion um Infos zum User einer Rezension von API holen
            function getUserName($userId){
                $curlurl = "http://host.docker.internal:8080/users/".$userId;
                $curl = curl_init($curlurl);
                curl_setopt($curl, CURLOPT_POST, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                $response = curl_exec($curl);
                $http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if($http_response_code != 200){
                    echo "Fehler beim holen des Users";
                }
                curl_close($curl);
                $userInfo = json_decode($response, true);
                return $userInfo['username'];
            }

            //Produktinformationen von der API holen
			$productid = $_GET['productid'];
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

            //Produktinfos anzeigen
            $products = json_decode(curl_exec($curl), true);
            foreach($products as $product){
                echo "<div class='product'>";
                echo "<img src='".$product['thumbnail']."' class='productimage' alt='Produktbild konnte nicht geladen werden.' stlye='width: 100px;'>";
                echo "<h3 class='product_name'>".$product['name']."</h3>";
                echo "<p class='product_desc'>".$product['description']."</p>";
                echo "<p class='product_price'>".$product['preis']."€</p>";

                // echo produktrezensionen from api (/reviews/product/:productId)
                echo "<h3>Rezensionen</h3>";
                $curlurl = "http://host.docker.internal:8080/reviews/product/".$product['id'];
                $curl = curl_init($curlurl);
                curl_setopt($curl, CURLOPT_POST, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                $response = curl_exec($curl);
                $http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                
                if($http_response_code != 200){
                    echo "Fehler beim Laden der Rezensionen.";
                }
                $reviews = json_decode($response, true);
                curl_close($curl);

                
                //Rezensionen anzeigen
                echo "<table>";
                foreach($reviews as $review){
                    debug_to_console($review['rating']);
                    echo "<tr>";
                    echo "<td>".$review['review_text']."</td>";
                    echo "<td>Bewertung: ".$review['rating']." Stern(e)</td>";
                    echo "<td>Rezension von ".getUserName($review['user_id'])." am ".$review['created_at']."</td>";
                    echo "</tr>";
                }
                echo "</table>";

				echo "<button class='addToChartButton' onclick='addToChart(".$product['id'].");'>In den Warenkorb</button>";
                echo "</div>";
                
            }
		?>

	</div>
	
</body>
</html>