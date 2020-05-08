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
	$api_key_value1 = "tPmAT5Ab3j7F9";
	$api_key_value2 = "tPmAT5Ab3j7F8";

	$api_key = $value1 = $value2 = $value3 = $value4 = $value5 = $value6 = $value7 = $value8 = $value9 = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$api_key = test_input($_POST["api_key"]);
		if($api_key == $api_key_value1) {
			$value1 = test_input($_POST["value1"]);
			$value2 = test_input($_POST["value2"]);
			$value3 = test_input($_POST["value3"]);
			$value4 = test_input($_POST["value4"]);
			$value5 = test_input($_POST["value5"]);
			$value6 = test_input($_POST["value6"]);
			$value7 = test_input($_POST["value7"]);
			$value8 = test_input($_POST["value8"]);
			$value9 = test_input($_POST["value9"]);
			
			// Create connection
			$conn = new mysqli($servername, $username, $password, $dbname, $port);
			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			} 
			
			$sql = "INSERT INTO sensor_data (temperature, humidity, pressure, dew_point, soil_humidity, lux, rain, water_level, rssi)
			VALUES ('" . $value1 . "', '" . $value2 . "', '" . $value3 . "', '" . $value4 . "', '" . $value5 . "', '" . $value6 . "','" . $value7 . "','" . $value8 . "','" . $value9 . "')";
			
			if ($conn->query($sql) === TRUE) {
				echo "New record created successfully";
			} 
			else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		
			$conn->close();
		}
		else if($api_key == $api_key_value2) {
			$value1 = test_input($_POST["relay1"]);
			$value2 = test_input($_POST["relay2"]);
			
			// Create connection
			$conn = new mysqli($servername, $username, $password, $dbname, $port);
			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			} 
			
			$sql = "UPDATE relay_data SET relay_1 = '{$value1}',relay_2 = '{$value2}' WHERE id=1";
			
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
	//else {
		//echo "No data posted with HTTP POST.";
	//}

	if ($_SERVER["REQUEST_METHOD"] == "GET") {
			$action = test_input($_GET["action"]);
			if($action == "tPmAT5Ab3j7F9") {
				$result = getRelayState();
				if ($result) {
					while ($row = $result->fetch_assoc()) {
						$relay1 = $row["relay_1"];
						$relay2 = $row["relay_2"];
						$automatic = $row["automatic"];
						$unit_id = $row['id'];
						$rows = array("relay1"=>$relay1,"relay2"=>$relay2,"automatic"=>$automatic);
					}
				}
				echo json_encode($rows);
			}
			else {
				echo "Invalid HTTP request.";
			}
	}

	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
?> 