<?php

session_start();
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
}
if(isset($_POST['items'], $_POST['total'], $_POST['name'], $_POST['address'], $_POST['city'], $_POST['zip'])){
    //prüfen of $items nicht leer ist
    if($_POST['items'] == ""){
        echo "<script>location.href='checkout.php?action=Items_nichts_drin';</script>";
    }

    //prüfen ob Name, Adresse, Stadt und zip nicht Leer sind
    if($_POST['name'] == "" && $_POST['address'] == "" && $_POST['city'] == "" && $_POST['zip'] == ""){
        echo "<script>location.href='checkout.php?action=name_addresse_city_und_oder_zip_sind_leer';</script>";
    }

    //prüfen ob zip 5 stellig ist
    if(strlen($_POST['zip']) != 5){
        echo "<script>location.href='checkout.php?action=zip_ist_nicht_fuenf_stellig';</script>";
    }
}else{
    echo "<script>location.href='checkout.php?action=Es_sind_nicht_alle_Felder_gesetzt';</script>";
}

//ItemString auswerten und nutzbar machen
$pairs = explode(',', rtrim($_POST['items'], ','));

//Für jedes Produkt eine Bestellung an die API absetzen
foreach($pairs as $pair){
    list($id, $amount) = explode(':', $pair);

    $data = ['productID' => $id, 'quantity' => $amount];
    $curl = curl_init('http://host.docker.internal:8080/order');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($curl);
    $response = json_decode($response, true);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($http_status == "404"){
        echo "<script>location.href='checkout.php?action=Produkt_nicht_gefunden';</script>";
    }elseif($http_status == "500"){
        echo "<script>location.href='checkout.php?action=internal_server_error';</script>";
    }
}

//Cookie mit den Bestellten Produkten Setzen und auf nächste Seite weiterleiten
setcookie("purchases", $_POST['items'], time() + 3600, '/');
echo "<script>location.href='thankyou.php'</script>";
?>