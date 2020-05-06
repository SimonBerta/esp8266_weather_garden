<?php
include_once('esp-database.php');

$servername = "localhost";

// REPLACE with your Database name
$dbname = "esp8266";
// REPLACE with Database user
$username = "esp8266";
// REPLACE with Database user password
$password = "simon1997";
$port = 3306;

// Keep this API Key value to be compatible with the ESP32 code provided in the project page. 
// If you change this value, the ESP32 sketch needs to match
$api_key_value = "tPmAT5Ab3j7F9";

$api_key = $value1 = $value2 = $value3 = $value4 = $value5 = $value6 = $value7 = $value8 = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = test_input($_POST["api_key"]);
    if($api_key == $api_key_value) {
        $value1 = test_input($_POST["value1"]);
        $value2 = test_input($_POST["value2"]);
        $value3 = test_input($_POST["value3"]);
	$value4 = test_input($_POST["value4"]);
	$value5 = test_input($_POST["value5"]);
	$value6 = test_input($_POST["value6"]);
	$value7 = test_input($_POST["value7"]);
	$value8 = test_input($_POST["value8"]);
        
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname, $port);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        
        $sql = "INSERT INTO sensor_data (temperature,humidity,pressure,dew_point,soil_humidity,lux,rain,rssi)
    	VALUES ('" . $value1 . "', '" . $value2 . "', '" . $value3 . "', '" . $value4 . "', '" . $value5 . "', '" . $value6 . "','" . $value7 . "','" . $value8 . "')";
        
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    
        $conn->close();
    }
    else {
        echo "Wrong API Key provided.";
    }

}
else {
    echo "No data posted with HTTP POST.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 