<?php

    $page = $_SERVER['PHP_SELF']; //settings for self refresh after 15 seconds
    $sec = "15";

    include_once('esp-database.php'); //include second file for data read and write from database
    if (isset($_GET["readingsCount"])){ //if used to get number of data values from page form
      $readings_count = $_GET["readingsCount"]; //value stored in variable readings_count
      setReadingsCount($readings_count); //function that writes number of values to database
    }
    //default readings count set to value from database
    else {
	$result = getReadingsCount(); //this function get the number from database
        if ($result) { 
        	while ($row = $result->fetch_assoc()) {
            	$readings_count = $row["readings"]; //set variable readings_count
		}
	}
        $result->free();
    }
    /* if (isset($_GET["datemin"])){ //unused code for future functions
      $data = $_GET["datemin"];
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      $datemin = $_GET["datemin"];
      setMinDate($datemin);
    } else {
      $datemin = "2020-01-01T00:00";
    }
    if (isset($_GET["datemax"])){
      $data = $_GET["datemax"];
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      $datemax = $_GET["datemax"];
      setMaxDate($datemax);
    } else {
      $datemax = "2020-01-01T00:00";
    } */

    $last_reading = getLastReadings(); //function gets last readings of sensors from database
    $last_reading_time = $last_reading["reading_time"]; //set variables with data from database
    $last_reading_temp = $last_reading["temperature"];
    $last_reading_humi = $last_reading["humidity"];
    $last_reading_press = $last_reading["pressure"];
    $last_reading_dew_p = $last_reading["dew_point"];  
    $last_reading_soil = $last_reading["soil_humidity"];  
    $last_reading_lux = $last_reading["lux"];
    $last_reading_rain = $last_reading["rain"];  
    $last_reading_water = $last_reading["water_level"];
    $last_reading_rssi = $last_reading["rssi"];
    

    $min_temp = minReading($readings_count, 'temperature'); //calculate min, max and average value from selected number of data values
    $max_temp = maxReading($readings_count, 'temperature');
    $avg_temp = avgReading($readings_count, 'temperature');

    $min_humi = minReading($readings_count, 'humidity');
    $max_humi = maxReading($readings_count, 'humidity');
    $avg_humi = avgReading($readings_count, 'humidity');

    $min_press = minReading($readings_count, 'pressure');
    $max_press = maxReading($readings_count, 'pressure');
    $avg_press = avgReading($readings_count, 'pressure');

    $min_dew_p = minReading($readings_count, 'dew_point');
    $max_dew_p = maxReading($readings_count, 'dew_point');
    $avg_dew_p = avgReading($readings_count, 'dew_point');


    $min_soil = minReading($readings_count, 'soil_humidity');
    $max_soil = maxReading($readings_count, 'soil_humidity');
    $avg_soil = avgReading($readings_count, 'soil_humidity');

    $min_lux = minReading($readings_count, 'lux');
    $max_lux = maxReading($readings_count, 'lux');
    $avg_lux = avgReading($readings_count, 'lux');

    $min_rain = minReading($readings_count, 'rain');
    $max_rain = maxReading($readings_count, 'rain');
    $avg_rain = avgReading($readings_count, 'rain');

    $min_water = minReading($readings_count, 'water_level');
    $max_water = maxReading($readings_count, 'water_level');
    $avg_water = avgReading($readings_count, 'water_level');

    $min_rssi = minReading($readings_count, 'rssi');
    $max_rssi = maxReading($readings_count, 'rssi');
    $avg_rssi = avgReading($readings_count, 'rssi');
?>

<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1"> <!--//-->
  <script src="https://code.highcharts.com/highcharts.js"></script> <!--include javascript code for graphs//-->
  <style> <!--define style for body an h2 header//-->
    body {
        min-width: 310px;
    	max-width: 1080px;
    	height: 500px;
        margin: 0 auto;
    }
    h2 {
      font-family: Arial;
      font-size: 2.5rem;
      text-align: center;
    }
  </style>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"> <!--define head//-->
	<meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'"> <!--refresh of page set to 15 seconds//-->
        <link rel="stylesheet" type="text/css" href="esp-style.css"> <!--define css file with graphic styles//-->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> <!--include jquery library//-->
    </head>
    <header class="header">
        <h1> <img src="station.png" alt="weather station logo" style="width:50px;height:50px;"> <font color="blue">ESP8266</font> Weather station with irrigation control</h1> <!--include meteostation logo, add main header//-->
        <form method="get"> <!--define form with update button//-->
            <input type="number" style="width:200px;height:20px;" name="readingsCount" min="1" placeholder="Number of readings (<?php echo $readings_count; ?>)"> <!--show number of selected readings in form//-->
            <input type="submit" value="UPDATE"> <!--update button//-->
        </form>
	<br>
    </header>
<body>
<?php
	function reloadStates() { //defined functon for getting states of relays, auto/man switch and log enable from database
		$result = getRelayState();
			if ($result) {
				while ($row = $result->fetch_assoc()) {
					$relay1 = $row["relay_1"];
					$relay2 = $row["relay_2"];
					$automatic = $row["automatic"];
					$log_enable = $row["log_enable"];
				}
			}
			$result->free();
		return array($relay1, $relay2, $automatic, $log_enable);
	}
	//set color and text of buttons according to the states from database 1/ON-green, 0/OFF-red, set color and state of log enable button Start-green, Stop-red
	list($relay1, $relay2, $automatic, $log_enable)= reloadStates();
	if($relay1 == 1){ 
		$inv_relay1 = 0;
		$text_relay1 = "ON";
		$color_relay1 = "#6ed829";
	}
	else{
		$inv_relay1 = 1;
		$text_relay1 = "OFF";
		$color_relay1 = "#e04141";
	}
	if($relay2 == 1){
		$inv_relay2 = 0;
		$text_relay2 = "ON";
		$color_relay2 = "#6ed829";
	}
	else{
		$inv_relay2 = 1;
		$text_relay2 = "OFF";
		$color_relay2 = "#e04141";
	}
	if($automatic == 1){
		$inv_automatic = 0;
		$text_automatic = "ON";
		$color_automatic = "#6ed829";
	}
	else{
		$inv_automatic = 1;
		$text_automatic = "OFF";
		$color_automatic = "#e04141";
	}
	if($log_enable == 1){ 
		$inv_log = 0;
		$text_log = "Stop";
		$color_log = "#e04141";
	}
	else{
		$inv_log = 1;
		$text_log = "Start";
		$color_log = "#6ed829";
	}
?>
	<h2>Log data from sensors to database</h2> <!--add header //-->
	<form method= 'POST'> <!--button with POST functionality//-->
			<input type='hidden' name='value0' value=<?php echo $log_enable; ?>> <!--variable value0 is hidden, and is changed when button pressed//-->
			<input type="submit" onclick="<?php  //do action on button click
				if (isset($_POST["value0"])){
					if ($_POST["value0"]==0) {setLog(1);header("Refresh:0");} //if variable value0 is 0, set log enable to 1 and send it to database via function setLog and refresh page to see the change
					else {setLog(0);header("Refresh:0");}} //similarly, when log enable is set to 1, set it to 0 and refresh
					?>" style='width:100px;height:50px;margin:0 50%;position:relative;left:-50px; font-size: 30px; text-align:center; background-color: <?php echo $color_log; ?>;' value=<?php echo $text_log; ?> > <!--set dimensions and style of button, align it on page//-->
	</form>

	<table class='table' id="relays" style='font-size: 30px;'> <!--create table for buttons and names//-->
		<thead style='text-align:right;'>
			   <h2>Relays and auto/man switch</h2> <!--main header for buttons//-->
		</thead>
			<tbody>
			<tr> <!--class='active'//-->  
				<td>Irrigation</td> <!--names of buttons with necessary spacing for centering//-->          
				<td>&nbsp;</td>
				<td>Pump</td>
				<td>&emsp;&emsp;&ensp;&nbsp;</td>
				<td>Automatic</td>
			</tr>
				<td><form method= 'POST'> <!--buttons with POST functionality//-->
				<input type='hidden' name='value1' value=<?php echo $relay1; ?>> <!--variable value1 is hidden, and is changed when button pressed//-->
				<input type="submit" onclick="<?php //do action on button click
					if (isset($_POST["value1"])){
						if ($_POST["value1"]==0) {setRelay1State(1); header("Refresh:0");} //if variable value1 is 0, set relay 1 to 1 and send it to database via function setRelay1State and refresh page to see the change
						else {setRelay1State(0); header("Refresh:0");}} //similarly, when relay 1 is set to 1, set it to 0 and refresh
						?>" style='width:100px;height:50px;margin:0 50%;position:relative;left:-50px; font-size: 30px; text-align:center; background-color: <?php echo $color_relay1; ?>;' value=<?php echo $text_relay1; ?> ></form></td> <!--set dimensions and style of button, align it on page//-->
				<td><form method= 'POST'> <!--same code but for relay 2//-->
				<input type='hidden' name='value2' value=<?php echo $relay2; ?>>
				<input type="submit" onclick="<?php 
					if (isset($_POST["value2"])){
						if ($_POST["value2"]==0) {setRelay2State(1);header("Refresh:0");} 
						else {setRelay2State(0);header("Refresh:0");}}
						?>" style='width:100px;height:50px;margin:0 50%;position:relative;left:+55px; font-size: 30px; text-align:center; background-color: <?php echo $color_relay2; ?>;' value=<?php echo $text_relay2; ?> ></form></td>
				<td><form method= 'POST'> <!--same code but for automatic/manual switch//-->
				<input type='hidden' name='value3' value=<?php echo $automatic; ?>>
				<input type="submit" onclick="<?php 
					if (isset($_POST["value3"])){
						if ($_POST["value3"]==0) {setAutomatic(1);header("Refresh:0");} 
						else {setAutomatic(0);header("Refresh:0");}}
						?>" style='width:100px;height:50px;margin:0 50%;position:relative;left:+155px; font-size: 30px; text-align:center; background-color: <?php echo $color_automatic; ?>;' value=<?php echo $text_automatic; ?> ></form></td>
			</tr>
			</tbody> 
	</table>
	<br>
	<h2>Sensor Data Gauges</h2> <!--set another section of page for gauges//-->
    <p>Last reading: <?php echo $last_reading_time; ?></p> <!--show time and date of last reading in database//-->
    <section class="content">
	    <div class="box gauge--1"> <!--set new gauge//-->
			<h3>TEMPERATURE</h3> <!--name of new gauge//-->
			<div class="mask">
				 <div class="semi-circle"></div> <!--set the style of gauge (style is set in .css file) //-->
				 <div class="semi-circle--mask"></div>
			</div>
			<p style="font-size: 30px;" id="temp">--</p> <!--show paragraph with last reading value-temperature//-->
			<table cellspacing="5" cellpadding="5">
				<tr>
					<th colspan="3">Temperature <?php echo $readings_count; ?> readings</th>
				</tr>
				<tr>
					<td>Min</td>
					<td>Max</td>
					<td>Average</td>
				</tr>
				<tr>
					<td><?php echo $min_temp['min_amount']; ?> &deg;C</td> <!--show min,max,avg values-->
					<td><?php echo $max_temp['max_amount']; ?> &deg;C</td>
					<td><?php echo round($avg_temp['avg_amount'], 2); ?> &deg;C</td>
				</tr>
			</table>
        </div>
        <div class="box gauge--2">
            <h3>HUMIDITY</h3>
            <div class="mask">
                <div class="semi-circle"></div>
                <div class="semi-circle--mask"></div>
            </div>
            <p style="font-size: 30px;" id="humi">--</p>
            <table cellspacing="5" cellpadding="5">
                <tr>
                    <th colspan="3">Humidity <?php echo $readings_count; ?> readings</th>
                </tr>
                <tr>
                    <td>Min</td>
                    <td>Max</td>
                    <td>Average</td>
                </tr>
                <tr>
                    <td><?php echo $min_humi['min_amount']; ?> %</td>
                    <td><?php echo $max_humi['max_amount']; ?> %</td>
                    <td><?php echo round($avg_humi['avg_amount'], 2); ?> %</td>
                </tr>
            </table>
        </div>
		<div class="box gauge--3">
			<h3>PRESSURE</h3>
			<div class="mask">
				<div class="semi-circle"></div>
				<div class="semi-circle--mask"></div>
			</div>
			<p style="font-size: 30px;" id="press">--</p>
			<table cellspacing="5" cellpadding="5">
				<tr>
					<th colspan="3">Pressure <?php echo $readings_count; ?> readings</th>
				</tr>
				<tr>
					<td>Min</td>
					<td>Max</td>
					<td>Average</td>
				</tr>
				<tr>
					<td><?php echo $min_press['min_amount']; ?> hPa</td>
					<td><?php echo $max_press['max_amount']; ?> hPa</td>
					<td><?php echo round($avg_press['avg_amount'], 2); ?> hPa</td>
				</tr>
			</table>
		</div>
		<div class="box gauge--4">
			<h3>SOIL HUMIDITY</h3>
			<div class="mask">
				<div class="semi-circle"></div>
				<div class="semi-circle--mask"></div>
			</div>
			<p style="font-size: 30px;" id="soil">--</p>
			<table cellspacing="5" cellpadding="5">
				<tr>
					<th colspan="3">Soil humidity <?php echo $readings_count; ?> readings</th>
				</tr>
				<tr>
					<td>Min</td>
					<td>Max</td>
					<td>Average</td>
				</tr>
				<tr>
					<td><?php echo $min_soil['min_amount']; ?> %</td>
					<td><?php echo $max_soil['max_amount']; ?> %</td>
					<td><?php echo round($avg_soil['avg_amount'], 2); ?> %</td>
				</tr>
			</table>
		</div>
	<div class="box gauge--5">
	    <h3>LIGHT INTENSITY</h3>
        <div class="mask">
			<div class="semi-circle"></div>
			<div class="semi-circle--mask"></div>
		</div>
		<p style="font-size: 30px;" id="lux">--</p>
		<table cellspacing="5" cellpadding="5">
		    <tr>
				<th colspan="3">Lux <?php echo $readings_count; ?> readings</th>
	        </tr>
		    <tr>
		        <td>Min</td>
                <td>Max</td>
                <td>Average</td>
            </tr>
            <tr>
                <td><?php echo $min_lux['min_amount']; ?> lux</td>
                <td><?php echo $max_lux['max_amount']; ?> lux</td>
                <td><?php echo round($avg_lux['avg_amount'], 2); ?> lux</td>
             </tr>
        </table>
    </div>
	<div class="box gauge--6">
	    <h3>RAIN</h3>
        <div class="mask">
			<div class="semi-circle"></div>
			<div class="semi-circle--mask"></div>
		</div>
		<p style="font-size: 30px;" id="rain">--</p>
		<table cellspacing="5" cellpadding="5">
		    <tr>
		        <th colspan="3">Rain <?php echo $readings_count; ?> readings</th>
	        </tr>
		    <tr>
		        <td>Min</td>
                <td>Max</td>
                <td>Average</td>
            </tr>
            <tr>
                <td><?php echo $min_rain['min_amount']; ?> </td>
                <td><?php echo $max_rain['max_amount']; ?> </td>
                <td><?php echo round($avg_rain['avg_amount'], 2); ?> </td>
            </tr>
        </table>
    </div>
	<div class="box gauge--7">
	    <h3>WATER LEVEL</h3>
        <div class="mask">
			<div class="semi-circle"></div>
			<div class="semi-circle--mask"></div>
		</div>
		<p style="font-size: 30px;" id="water">--</p>
		<table cellspacing="5" cellpadding="5">
		    <tr>
		        <th colspan="3">Water level <?php echo $readings_count; ?> readings</th>
	        </tr>
		    <tr>
		        <td>Min</td>
                <td>Max</td>
                <td>Average</td>
            </tr>
            <tr>
                <td><?php echo $min_water['min_amount']; ?> litres</td>
                <td><?php echo $max_water['max_amount']; ?> litres</td>
                <td><?php echo round($avg_water['avg_amount'], 2); ?> litres</td>
            </tr>
        </table>
    </div>
	<div class="box gauge--8">
	    <h3>RSSI</h3>
        <div class="mask">
			<div class="semi-circle"></div>
			<div class="semi-circle--mask"></div>
		</div>
		<p style="font-size: 30px;" id="rssi">--</p>
		<table cellspacing="5" cellpadding="5">
		    <tr>
		        <th colspan="3">RSSI <?php echo $readings_count; ?> readings</th>
	        </tr>
		    <tr>
		        <td>Min</td>
                <td>Max</td>
                <td>Average</td>
            </tr>
            <tr>
                <td><?php echo $min_rssi['min_amount']; ?> dBm</td>
                <td><?php echo $max_rssi['max_amount']; ?> dBm</td>
                <td><?php echo round($avg_rssi['avg_amount'], 2); ?> dBm</td>
            </tr>
        </table>
    </div>
    </section>
<?php
    echo   '<h2> Graphs of Latest ' . $readings_count . ' Readings</h2>'; //show graphs of last values
    $result = getAllReadings($readings_count); //get datasets from database
        if ($result) {
			while ($data = $result->fetch_assoc()) { //read all data from variable result
				$sensor_data[] = $data;
			}
			$val1 = json_encode(array_reverse(array_column($sensor_data, 'temperature')), JSON_NUMERIC_CHECK); //encode data in JSON format for all variables
			$val2 = json_encode(array_reverse(array_column($sensor_data, 'humidity')), JSON_NUMERIC_CHECK);
			$val3 = json_encode(array_reverse(array_column($sensor_data, 'pressure')), JSON_NUMERIC_CHECK);
			$val4 = json_encode(array_reverse(array_column($sensor_data, 'dew_point')), JSON_NUMERIC_CHECK);
			$val5 = json_encode(array_reverse(array_column($sensor_data, 'soil_humidity')), JSON_NUMERIC_CHECK);
			$val6 = json_encode(array_reverse(array_column($sensor_data, 'lux')), JSON_NUMERIC_CHECK);
			$val7 = json_encode(array_reverse(array_column($sensor_data, 'rain')), JSON_NUMERIC_CHECK);
			$val7 = json_encode(array_reverse(array_column($sensor_data, 'rain')), JSON_NUMERIC_CHECK);
			$val8 = json_encode(array_reverse(array_column($sensor_data, 'water_level')), JSON_NUMERIC_CHECK);
			$val9 = json_encode(array_reverse(array_column($sensor_data, 'rssi')), JSON_NUMERIC_CHECK);
			$read_time = json_encode(array_reverse(array_column($sensor_data, 'reading_time')), JSON_NUMERIC_CHECK); //get data for x-axis=date and time
		}
        $result->free();
?>
	<div class="grid-container"> <!--create grid of graphs//-->
		<div class="grid-item" id="chart-temperature"></div> <!--graphs for all variables//-->
  		<div class="grid-item" id="chart-humidity"></div>
  		<div class="grid-item" id="chart-pressure"></div>  
  		<div class="grid-item" id="chart-soil"></div>
  		<div class="grid-item" id="chart-lux"></div>
  		<div class="grid-item" id="chart-rain"></div>
  		<div class="grid-item" id="chart-water"></div>
  		<div class="grid-item" id="chart-rssi"></div>  
	</div>    

	<h2>View Latest <?php echo $readings_count ?> Readings</h2> <!--create another section with table of variables//-->
    <table cellspacing="5" cellpadding="5" id="tableReadings"> <!--create table//-->
        <tr>
            <th>ID</th> <!--table column names//-->
		    <th>Timestamp</th>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
            <th>Pressure (hPa)</th>
		    <th>Dew point (°C)</th>
		    <th>Soil humidity (%)</th>
		    <th>Light intensity (lux)</th>
		    <th>Rain (Y/N)</th>
			<th>Water level (litres)</th>
		    <th>RSSI (dBm)</th>
        </tr>
<?php
    $result = getAllReadings($readings_count); //get content (rows) for table
        if ($result) {
			while ($row = $result->fetch_assoc()) { //create rows until all data is read
				$row_id = $row["id"];
				$row_reading_time = $row["reading_time"];
				$row_temperature = $row["temperature"];
				$row_humidity = $row["humidity"];
				$row_pressure = $row["pressure"];
				$row_dew_p = $row["dew_point"];
				$row_soil = $row["soil_humidity"];
				$row_lux = $row["lux"];
				$row_rain = $row["rain"];
				$row_water = $row["water_level"];
				$row_rssi = $row["rssi"];
				//print variables to correct columns
				echo '<tr> 
					<td>' . $row_id . '</td>
					<td>' . $row_reading_time . '</td>
					<td>' . $row_temperature . '</td>
					<td>' . $row_humidity . '</td>
					<td>' . $row_pressure . '</td>
					<td>' . $row_dew_p . '</td>
					<td>' . $row_soil . '</td>
					<td>' . $row_lux . '</td>
					<td>' . $row_rain . '</td>
					<td>' . $row_water . '</td>
					<td>' . $row_rssi . '</td>
				</tr>'; 
			}
			echo '</table>';
			$result->free();
		}
?>

<script>
    var value1 = <?php echo $last_reading_temp; ?>; //set last reading of variables to value1-8
    var value2 = <?php echo $last_reading_humi; ?>;
    var value3 = <?php echo $last_reading_press; ?>;
    var value4 = <?php echo $last_reading_soil; ?>;
    var value5 = <?php echo $last_reading_lux; ?>;
    var value6 = <?php echo $last_reading_rain; ?>;
	var value7 = <?php echo $last_reading_water; ?>;
	var value8 = <?php echo $last_reading_rssi; ?>;
    setTemperature(value1); //functions to set last read value and to change gauge appearance accordingly (move bar according to value)
    setHumidity(value2);
    setPressure(value3);
    setSoil(value4);
    setLux(value5);
	setRain(value6);
	setWater(value7);
    setRSSI(value8);

    function setTemperature(curVal){
    	var minTemp = -20.0; //set min and max value of temperature
    	var maxTemp = 45.0;

    	var newVal = scaleValue(curVal, [minTemp, maxTemp], [0, 180]); //scale range of variable from 0 to 180 degrees of gauge movement
    	$('.gauge--1 .semi-circle--mask').attr({ //transform color of gauge according to value of rotation due to variable value
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#temp").text(curVal + ' ºC'); //set value in paragraph with id temp
    }

    function setHumidity(curVal){
    	//set range for Humidity percentage 0 % to 100 %
    	var minHumi = 0;
    	var maxHumi = 100;

    	var newVal = scaleValue(curVal, [minHumi, maxHumi], [0, 180]);
    	$('.gauge--2 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#humi").text(curVal + ' %');
    }
    function setPressure(curVal){
    	//set range for Pressure 950 hPa to 1050 hPa
    	var minPress = 950;
    	var maxPress = 1050;

    	var newVal = scaleValue(curVal, [minPress, maxPress], [0, 180]);
    	$('.gauge--3 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#press").text(curVal + ' hPa');
    }
    function setSoil(curVal){
    	//set range for soil humidity in % 0 to 100
    	var minSoil = 0;
    	var maxSoil = 100;

    	var newVal = scaleValue(curVal, [minSoil, maxSoil], [0, 180]);
    	$('.gauge--4 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#soil").text(curVal + ' %');
    }
    function setLux(curVal){
    	//set range for lux in lux 0 to 15000
    	var minLux = 0;
    	var maxLux = 15000;

    	var newVal = scaleValue(curVal, [minLux, maxLux], [0, 180]);
    	$('.gauge--5 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#lux").text(curVal + ' lux');
    }
	function setRain(curVal){
    	//set range for rain 0 to 1
    	var minRain = 0;
    	var maxRain = 1;

    	var newVal = scaleValue(curVal, [minRain, maxRain], [0, 180]);
    	$('.gauge--6 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#rain").text(curVal + ' ');
    }
	function setWater(curVal){
    	//set range for water in litres 0 to 1100
    	var minWater = 0;
    	var maxWater = 1100;

    	var newVal = scaleValue(curVal, [minWater, maxWater], [0, 180]);
    	$('.gauge--7 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#water").text(curVal + ' litres');
    }
    function setRSSI(curVal){
    	//set range for rssi in dBm 0 to 100
    	var minRssi = -100;
    	var maxRssi = 0;

    	var newVal = scaleValue(curVal, [minRssi, maxRssi], [0, 180]);
    	$('.gauge--8 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#rssi").text(curVal + ' dBm');
    }
    function scaleValue(value, from, to) { //function for scaling variable to rotation in degrees
        var scale = (to[1] - to[0]) / (from[1] - from[0]);
        var capped = Math.min(from[1], Math.max(from[0], value)) - from[0];
        return ~~(capped * scale + to[0]);
    }
</script>
<script> //script for style definition of graphs
var val1 = <?php echo $val1; ?>; //load variables with datasets
var val2 = <?php echo $val2; ?>;
var val3 = <?php echo $val3; ?>;
var val4 = <?php echo $val4; ?>;
var val5 = <?php echo $val5; ?>;
var val6 = <?php echo $val6; ?>;
var val7 = <?php echo $val7; ?>;
var val8 = <?php echo $val8; ?>;
var val9 = <?php echo $val9; ?>;
var read_time = <?php echo $read_time; ?>;

var chartT = new Highcharts.Chart({ //create graph with highcharts style
  chart:{ renderTo : 'chart-temperature' }, //id of division in body of page, where graph will be placed
  title: { text: 'Temperature' }, //name of graph
  series: [{
    showInLegend: false,
    data: val1 //dataset with temperature values
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true } //enable data labels
    },
    series: { color: '#ff0000' } //color of data points and graph line 
  },
  xAxis: { //defined values for x axis
    type: 'datetime',
    categories: read_time
  },
  yAxis: { 
    title: { text: 'Temperature (°C)' } //name of y axis values
  },
  credits: { enabled: true }
});

var chartH = new Highcharts.Chart({ //same graphs for all visualised variables
  chart:{ renderTo:'chart-humidity' },
  title: { text: 'Humidity' },
  series: [{
    showInLegend: false,
    data: val2
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true }
    },
    series: { color: '#059e8a' }
  },
  xAxis: {
    type: 'datetime',
    categories: read_time
  },
  yAxis: {
    title: { text: 'Humidity (%)' }
  },
  credits: { enabled: false }
});


var chartP = new Highcharts.Chart({
  chart:{ renderTo:'chart-pressure' },
  title: { text: 'Pressure' },
  series: [{
    showInLegend: false,
    data: val3
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true }
    },
    series: { color: '#9c0092' }
  },
  xAxis: {
    type: 'datetime',
    categories: read_time
  },
  yAxis: {
    title: { text: 'Pressure (hPa)' }
  },
  credits: { enabled: false }
});

var chartS = new Highcharts.Chart({
  chart:{ renderTo:'chart-soil' },
  title: { text: 'Soil humidity' },
  series: [{
    showInLegend: false,
    data: val5
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true }
    },
    series: { color: '#9c4100' }
  },
  xAxis: {
    type: 'datetime',
    categories: read_time
  },
  yAxis: {
    title: { text: 'Humidity (%)' }
  },
  credits: { enabled: false }
});

var chartL = new Highcharts.Chart({
  chart:{ renderTo:'chart-lux' },
  title: { text: 'Light intensity' },
  series: [{
    showInLegend: false,
    data: val6
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true }
    },
    series: { color: '#ffb300' }
  },
  xAxis: {
    type: 'datetime',
    categories: read_time
  },
  yAxis: {
    title: { text: 'Light intensity (lux)' }
  },
  credits: { enabled: false }
});
var chartRa = new Highcharts.Chart({
  chart:{ renderTo:'chart-rain' },
  title: { text: 'Rain' },
  series: [{
    showInLegend: false,
    data: val7
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true }
    },
    series: { color: '#062a59' }
  },
  xAxis: {
    type: 'datetime',
    categories: read_time
  },
  yAxis: {
    title: { text: 'Rain (Y/N)' }
  },
  credits: { enabled: false }
});
var chartW = new Highcharts.Chart({
  chart:{ renderTo:'chart-water' },
  title: { text: 'Water level' },
  series: [{
    showInLegend: false,
    data: val8
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true }
    },
    series: { color: '#00d0f5' }
  },
  xAxis: {
    type: 'datetime',
    categories: read_time
  },
  yAxis: {
    title: { text: 'Water level (litres)' }
  },
  credits: { enabled: false }
});
var chartR = new Highcharts.Chart({
  chart:{ renderTo:'chart-rssi' },
  title: { text: 'RSSI' },
  series: [{
    showInLegend: false,
    data: val9
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true }
    },
    series: { color: '#33ff00' }
  },
  xAxis: {
    type: 'datetime',
    categories: read_time
  },
  yAxis: {
    title: { text: 'RSSI (dBm)' }
  },
  credits: { enabled: false }
});

</script>
</body>
</html>