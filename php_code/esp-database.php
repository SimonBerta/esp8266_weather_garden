<?php
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
	function insertReading($value1, $value2, $value3, $value4, $value5, $value6, $value7, $value8, $value9) { //function for inserting values send from NodeMCU into database
		global $servername, $username, $password, $dbname, $port;
		//create connection
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		//check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error); //error if not successful
		}

		$sql = "INSERT INTO sensor_data (temperature,humidity,pressure,dew_point,soil_humidity,lux,rain,water_level,rssi)
		VALUES ('" . $value1 . "', '" . $value2 . "', '" . $value3 . "', '" . $value4 . "', '" . $value5 . "', '" . $value6 . "','" . $value7 . "','" . $value8 . "','" . $value9 . "')"; //sql code for data insert into table sensor_data

		if ($conn->query($sql) === TRUE) { //return string if insertion was/was not successful
			return "New record created successfully";
		}
		else {
			return "Error: " . $sql . "<br>" . $conn->error;
		}
		$conn->close(); //close connection
	}
	function setReadingsCount($limit) { //function for readings count number set in database
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "UPDATE relay_data SET readings = $limit WHERE id=1"; //sql code for data update in table relay_data

		if ($conn->query($sql) === TRUE) {
			return "Data updated successfully";
		}
		else {
			return "Error: " . $sql . "<br>" . $conn->error;
		}
		$conn->close();
	}
	function setMinDate($date) { //this function is currently not in use, will be used in the future
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "UPDATE relay_data SET date_start = $date WHERE id=1";

		if ($conn->query($sql) === TRUE) {
			return true;
		}
		else {
			return "Error: " . $sql . "<br>" . $conn->error;
		}
		$conn->close();
	}
	function setMaxDate($date) { //this function is currently not in use, will be used in the future
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "UPDATE relay_data SET date_stop = $date WHERE id=1";

		if ($conn->query($sql) === TRUE) {
			return true;
		}
		else {
			return "Error: " . $sql . "<br>" . $conn->error;
		}
		$conn->close();
	}
	function getReadingsCount() { //function reads number of readings count from database
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "SELECT readings FROM relay_data WHERE id=1";

		if ($result = $conn->query($sql)) {
			return $result;
		}
		else {
			return false;
		}
		$conn->close();
	}
	function getRelayState() { //function reads state of relays and auto/man switch from database and return these values
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "SELECT relay_1,relay_2,id,automatic FROM relay_data WHERE id=1";

		if ($result = $conn->query($sql)) {
			return $result;
		}
		else {
			return false;
		}
		$conn->close();
	}
	function setRelay1State($value) { //function sets state of relay 1 in database
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "UPDATE relay_data SET relay_1 = '{$value}' WHERE id=1"; //sql code for data update in table relay_data

		if ($conn->query($sql) === TRUE) {
			return "State of relay 1 updated successfully"; 
		}
		else {
			return "State of relay 1 not updated";
		}
		$conn->close();
	}
	function setRelay2State($value) { //function sets state of relay 2 in database
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "UPDATE relay_data SET relay_2 = '{$value}' WHERE id=1"; //sql code for data update in table relay_data

		if ($conn->query($sql) === TRUE) {
			return "State of relay 2 updated successfully"; 
		}
		else {
			return "State of relay 2 not updated";
		}
		$conn->close();
	}
	function setAutomatic($value) { //function sets state of auto/man switch in database
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "UPDATE relay_data SET automatic = '{$value}' WHERE id=1"; //sql code for data update in table relay_data

		if ($conn->query($sql) === TRUE) {
			return "State of auto/man updated successfully"; 
		}
		else {
			return "State of auto/man not updated";
		}
		$conn->close();
	}
	function getAllReadings($limit) { //function returns all data from last row to number of rows selected by limit value
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "SELECT id, reading_time, temperature, humidity, pressure, dew_point, soil_humidity, lux, rain, water_level, rssi FROM sensor_data order by reading_time desc limit " . $limit; //get data from sensor_data table from last reading to defined number of readings
		
		if ($result = $conn->query($sql)) {
			return $result; //return data gathered from database
		}
		else {
			return false;
		}
		$conn->close();
	}
	function getLastReadings() { //function gets last row from database in table sensor_data
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "SELECT id, reading_time, temperature, humidity, pressure, dew_point, soil_humidity, lux, rain, water_level, rssi FROM sensor_data order by reading_time desc limit 1" ;
		
		if ($result = $conn->query($sql)) {
			return $result->fetch_assoc();
		}
		else {
			return false;
		}
		$conn->close();
	}

	function minReading($limit, $value) { //function returns minimal value of variable from selected rows
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "SELECT MIN(" . $value . ") AS min_amount FROM (SELECT " . $value . " FROM sensor_data order by reading_time desc limit " . $limit . ") AS min"; //sql code for min value of varible from selected rows
		
		if ($result = $conn->query($sql)) {
			return $result->fetch_assoc();
		}
		else {
			return false;
		}
		$conn->close();
	}

	function maxReading($limit, $value) { //function returns maximal value of variable from selected rows
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "SELECT MAX(" . $value . ") AS max_amount FROM (SELECT " . $value . " FROM sensor_data order by reading_time desc limit " . $limit . ") AS max";
		
		if ($result = $conn->query($sql)) {
			return $result->fetch_assoc();
		}
		else {
			return false;
		}
		$conn->close();
	}

	function avgReading($limit, $value) { //function returns average value of variable from selected rows
		global $servername, $username, $password, $dbname, $port;
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "SELECT AVG(" . $value . ") AS avg_amount FROM (SELECT " . $value . " FROM sensor_data order by reading_time desc limit " . $limit . ") AS avg";
		
		if ($result = $conn->query($sql)) {
			return $result->fetch_assoc();
		}
		else {
			return false;
		}
		$conn->close();
	}
?>