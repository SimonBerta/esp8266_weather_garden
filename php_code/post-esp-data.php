<?php
	include_once('esp-database.php'); //include file with functions for data read and write from database

	$servername = "localhost"; //address of server
	//Credentials for access in database
	//database name
	$dbname = "esp8266";
	//database user
	$username = "esp8266";
	//database user password
	$password = "simon1997";
	//port
	$port = 3306;

	//API Key values used to recognise and allow POST and GET requests from NodeMCU to be executed 
	$api_key_value1 = "tPmAT5Ab3j7F9";
	$api_key_value2 = "tPmAT5Ab3j7F8";

	$api_key = $value1 = $value2 = $value3 = $value4 = $value5 = $value6 = $value7 = $value8 = $value9 = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") { //if there is POST request on server from client
		$api_key = test_input($_POST["api_key"]); //check API key sent in POST request
		if($api_key == $api_key_value1) { //if matched than asign values from POST request to variables value1-9
			$value1 = test_input($_POST["value1"]);
			$value2 = test_input($_POST["value2"]);
			$value3 = test_input($_POST["value3"]);
			$value4 = test_input($_POST["value4"]);
			$value5 = test_input($_POST["value5"]);
			$value6 = test_input($_POST["value6"]);
			$value7 = test_input($_POST["value7"]);
			$value8 = test_input($_POST["value8"]);
			$value9 = test_input($_POST["value9"]);
			
			//create connection
			$conn = new mysqli($servername, $username, $password, $dbname, $port);
			//check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error); //if failed to connect than error
			} 
			
			$sql = "INSERT INTO sensor_data (temperature, humidity, pressure, dew_point, soil_humidity, lux, rain, water_level, rssi)
			VALUES ('" . $value1 . "', '" . $value2 . "', '" . $value3 . "', '" . $value4 . "', '" . $value5 . "', '" . $value6 . "','" . $value7 . "','" . $value8 . "','" . $value9 . "')"; //sql code for data insertion into sensor_data table
			
			if ($conn->query($sql) === TRUE) { //for debug purposes
				echo "New record created successfully"; 
			} 
			else {
				echo "Error: " . $sql . "<br>" . $conn->error; 
			}
		
			$conn->close(); //close connection
		}
		else if($api_key == $api_key_value2) { //check if there is POST request with second API key matched
			$value1 = test_input($_POST["relay1"]); //if matched than asign values from request to value1-3
			$value2 = test_input($_POST["relay2"]);
			$value3 = test_input($_POST["automan"]);

			$conn = new mysqli($servername, $username, $password, $dbname, $port);
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			} 
			
			$sql = "UPDATE relay_data SET relay_1 = '{$value1}',relay_2 = '{$value2}',automatic = '{$value3}' WHERE id=1"; //update relay_data table with values from NodeMCU
			
			if ($conn->query($sql) === TRUE) {
				echo "New record created successfully";
			} 
			else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		
			$conn->close();
		}
		else {
			echo "Wrong API Key provided."; //print this line if API keys do not match
		}

	}

	if ($_SERVER["REQUEST_METHOD"] == "GET") { //if there is GET request on server from client
			$action = test_input($_GET["action"]); //check if value in action variable matches with api_key_value2
			if($action == $api_key_value2) {
				$result = getRelayState();//if there is a match, get states of relays and auto/man switch from database
				if ($result) {
					while ($row = $result->fetch_assoc()) { //get data from result variable (in rows) and asign it accordingly
						$relay1 = $row["relay_1"];
						$relay2 = $row["relay_2"];
						$automatic = $row["automatic"];
						$rows = array("relay1"=>$relay1,"relay2"=>$relay2,"automatic"=>$automatic); //make array of data
					}
				}
				echo json_encode($rows);//make JSON serial structure from gathered data and return it to client
			}
			else {
				echo "Invalid HTTP request."; //print error line if no match in API key
			}
	}

	function test_input($data) { //function that deletes unnecesary spacers, backslashes and converts html special characters from data if they occur
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data); 
		return $data;
	}
?> 