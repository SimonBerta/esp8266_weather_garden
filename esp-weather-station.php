<?php
    $page = $_SERVER['PHP_SELF'];
    $sec = "15";

    include_once('esp-database.php');
    if (isset($_GET["readingsCount"])){
      $data = $_GET["readingsCount"];
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      $readings_count = $_GET["readingsCount"];
      setReadingsCount($readings_count);
    }
    // default readings count set to 20
    else {
	$result = getReadingsCount();
        if ($result) {
        	while ($row = $result->fetch_assoc()) {
            	$readings_count = $row["readings"];
            	//echo '<tr>
                    //<td>' . $readings_count . '</td>
                  //</tr>';
		}
	}
        //echo '</table>';
        $result->free();
    }
    if (isset($_GET["datemin"])){
      $data = $_GET["datemin"];
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      $datemin = $_GET["datemin"];
    } else {
      $datemin = "2020-01-01T00:00";
    }
    if (isset($_GET["datemax"])){
      $data = $_GET["datemax"];
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      $datemax = $_GET["datemax"];
    } else {
      $datemax = "2020-01-01T00:00";
    }

    $last_reading = getLastReadings();
    $last_reading_time = $last_reading["reading_time"];
    $last_reading_temp = $last_reading["temperature"];
    $last_reading_humi = $last_reading["humidity"];
    $last_reading_press = $last_reading["pressure"];
    $last_reading_dew_p = $last_reading["dew_point"];  
    $last_reading_soil = $last_reading["soil_humidity"];  
    $last_reading_lux = $last_reading["lux"];
    $last_reading_rain = $last_reading["rain"];
    $last_reading_rssi = $last_reading["rssi"];

    $min_temp = minReading($readings_count, 'temperature');
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

    $min_rssi = minReading($readings_count, 'rssi');
    $max_rssi = maxReading($readings_count, 'rssi');
    $avg_rssi = avgReading($readings_count, 'rssi');
?>

<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <style>
    body {
        min-width: 310px;
    	max-width: 1280px;
    	height: 500px;
        margin: 0 auto;
    }
    h2 {
      font-family: Arial;
      font-size: 2.5rem;
      text-align: center;
    }
  </style>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
        <link rel="stylesheet" type="text/css" href="esp-style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    </head>
    <header class="header">
        <h1>ESP8266 weather station with irrigation control</h1>
        <form method="get">
            <input type="number" name="readingsCount" min="1" placeholder="Number of readings (<?php echo $readings_count; ?>)">
            <input type="submit" value="UPDATE">
        </form>
	<form method="get">
            <label for="datemin">Start date and time:</label>
            <input type="datetime-local" name="datemin" (<?php echo $datemin; ?>)">
            <input type="submit" value="SET">
        </form>
	<form method="get">
            <label for="datemax">Stop date and time:</label>
            <input type="datetime-local" name="datemax" (<?php echo $datemax; ?>)">
            <input type="submit" value="SET">
        </form>
    </header>
<body>
    <p>Last reading: <?php echo $last_reading_time; ?></p>
    <section class="content">
	    <div class="box gauge--1">
	    <h3>TEMPERATURE</h3>
              <div class="mask">
			  <div class="semi-circle"></div>
			  <div class="semi-circle--mask"></div>
			</div>
		    <p style="font-size: 30px;" id="temp">--</p>
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
                    <td><?php echo $min_temp['min_amount']; ?> &deg;C</td>
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
    echo   '<h2> Graphs of Latest ' . $readings_count . ' Readings</h2>';
    $result = graphAllReadings($readings_count);
        if ($result) {
        while ($data = $result->fetch_assoc()) {
            $sensor_data[] = $data;
        }

	$val1 = json_encode(array_reverse(array_column($sensor_data, 'temperature')), JSON_NUMERIC_CHECK);
	$val2 = json_encode(array_reverse(array_column($sensor_data, 'humidity')), JSON_NUMERIC_CHECK);
	$val3 = json_encode(array_reverse(array_column($sensor_data, 'pressure')), JSON_NUMERIC_CHECK);
	$val4 = json_encode(array_reverse(array_column($sensor_data, 'dew_point')), JSON_NUMERIC_CHECK);
	$val5 = json_encode(array_reverse(array_column($sensor_data, 'soil_humidity')), JSON_NUMERIC_CHECK);
	$val6 = json_encode(array_reverse(array_column($sensor_data, 'lux')), JSON_NUMERIC_CHECK);
	$val7 = json_encode(array_reverse(array_column($sensor_data, 'rain')), JSON_NUMERIC_CHECK);
	$val8 = json_encode(array_reverse(array_column($sensor_data, 'rssi')), JSON_NUMERIC_CHECK);
	$read_time = json_encode(array_reverse(array_column($sensor_data, 'reading_time')), JSON_NUMERIC_CHECK);
	}
        $result->free();
?>
    <div id="chart-temperature" class="container"></div>
    <div id="chart-humidity" class="container"></div>
    <div id="chart-pressure" class="container"></div>
    <div id="chart-soil" class="container"></div>
    <div id="chart-lux" class="container"></div>
    <div id="chart-rssi" class="container"></div>
<?php
    echo   '<h2> View Latest ' . $readings_count . ' Readings</h2>
            <table cellspacing="5" cellpadding="5" id="tableReadings">
                <tr>
                    <th>ID</th>
		    <th>Timestamp</th>
                    <th>Temperature (°C)</th>
                    <th>Humidity (%)</th>
                    <th>Pressure (hPa)</th>
		    <th>Dew point (°C)</th>
		    <th>Soil humidity (%)</th>
		    <th>Light intensity (lux)</th>
		    <th>Rain (Y/N)</th>
		    <th>RSSI (dBm)</th>
                </tr>';

    $result = getAllReadings($readings_count);
        if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row_id = $row["id"];
            $row_reading_time = $row["reading_time"];
            $row_temperature = $row["temperature"];
            $row_humidity = $row["humidity"];
            $row_pressure = $row["pressure"];
            $row_dew_p = $row["dew_point"];
            $row_soil = $row["soil_humidity"];
	    $row_lux = $row["lux"];
	    $row_rain = $row["rain"];
	    $row_rssi = $row["rssi"];

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
		    <td>' . $row_rssi . '</td>
                  </tr>';
        }
        echo '</table>';
        $result->free();
    }
?>

<script>
    var value1 = <?php echo $last_reading_temp; ?>;
    var value2 = <?php echo $last_reading_humi; ?>;
    var value3 = <?php echo $last_reading_press; ?>;
    var value4 = <?php echo $last_reading_soil; ?>;
    var value5 = <?php echo $last_reading_lux; ?>;
    var value6 = <?php echo $last_reading_rssi; ?>;
    setTemperature(value1);
    setHumidity(value2);
    setPressure(value3);
    setSoil(value4);
    setLux(value5);
    setRSSI(value6);

    function setTemperature(curVal){
    	//set range for Temperature in Celsius -5 Celsius to 38 Celsius
    	var minTemp = -20.0;
    	var maxTemp = 45.0;

    	var newVal = scaleValue(curVal, [minTemp, maxTemp], [0, 180]);
    	$('.gauge--1 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#temp").text(curVal + ' ºC');
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
    	//set range for lux in lux 0 to 30000
    	var minLux = 0;
    	var maxLux = 30000;

    	var newVal = scaleValue(curVal, [minLux, maxLux], [0, 180]);
    	$('.gauge--5 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#lux").text(curVal + ' lux');
    }
    function setRSSI(curVal){
    	//set range for rssi in dBm 0 to 100
    	var minRssi = -100;
    	var maxRssi = 0;

    	var newVal = scaleValue(curVal, [minRssi, maxRssi], [0, 180]);
    	$('.gauge--6 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#rssi").text(curVal + ' dBm');
    }
    function scaleValue(value, from, to) {
        var scale = (to[1] - to[0]) / (from[1] - from[0]);
        var capped = Math.min(from[1], Math.max(from[0], value)) - from[0];
        return ~~(capped * scale + to[0]);
    }
</script>
<script>

var val1 = <?php echo $val1; ?>;
var val2 = <?php echo $val2; ?>;
var val3 = <?php echo $val3; ?>;
var val4 = <?php echo $val4; ?>;
var val5 = <?php echo $val5; ?>;
var val6 = <?php echo $val6; ?>;
var val7 = <?php echo $val7; ?>;
var val8 = <?php echo $val8; ?>;
var read_time = <?php echo $read_time; ?>;

var chartT = new Highcharts.Chart({
  chart:{ renderTo : 'chart-temperature' },
  title: { text: 'Temperature' },
  series: [{
    showInLegend: false,
    data: val1
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true }
    },
    series: { color: '#ff0000' }
  },
  xAxis: { 
    type: 'datetime',
    categories: read_time
  },
  yAxis: {
    title: { text: 'Temperature (°C)' }
  },
  credits: { enabled: false }
});

var chartH = new Highcharts.Chart({
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
    //dateTimeLabelFormats: { second: '%H:%M:%S' },
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

var chartR = new Highcharts.Chart({
  chart:{ renderTo:'chart-rssi' },
  title: { text: 'RSSI' },
  series: [{
    showInLegend: false,
    data: val8
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