#include <ESP8266WiFi.h> //NodeMCU necessary library
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h> //NodeMCU HTTP and WIFI client library
#include <MedianFilter.h> //library for median filtering
#include <Wire.h> //library for comunication with I2C sensors
#include <MAX44009.h> //library for light intensity sensor MAX44009
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h> //libraries for sensor BME280
#include "ArduinoJson.h" //library for json data parsing and decoding
#define ELEVATION 143 //elevation for pressure calculation
#define ECHOPIN 15// NodeMCU pin to receive echo pulse
#define TRIGPIN 13// NodeMCU pin to send trigger pulse
#define RELAY1 14// NodeMCU pin for relay1-irrigation valve
#define RELAY2 12// NodeMCU pin for relay2-water tank filling pump
#define RAIN 2// NodeMCU pin for rain sensor
//global variables
float litres, lastlitres, dist, soil, rain;
double lastmillis, lastmillis2, lastmillis3;
int automatic = 0;
int error = 0;
int rel1 = 0, rel2 = 0, counter1 = 0, counter2 = 0;
const int sleepTimeS = 15; //data send interval to database
//Network credentials
const char* ssid     = "TP-Link_289D";
const char* password = "simon1997";

//IP addresses and paths to php code running on server
const char* serverName = "http://192.168.0.110/post-esp-data.php"; //POST data
const char* serverName2 = "http://192.168.0.110/post-esp-data.php?action=tPmAT5Ab3j7F8"; //GET data

//API Key values to be compatible with the PHP code
String apiKeyValue1 = "tPmAT5Ab3j7F9";
String apiKeyValue2 = "tPmAT5Ab3j7F8";
String outputsState;

Adafruit_BME280 bme; //BME280 definition
MedianFilter filter(3, 0);//Median filter definition - compute median of 3 samples
MAX44009 light; //MAX44009 definition

void setup() {
  Serial.begin(115200); //start serial for debugging with baud rate 115200
  if (!bme.begin(0x77)) { //bme sensor inicialization
    Serial.println("Could not find a valid BME280 sensor, check wiring or change I2C address!");//for error debugging
    while (1);
  }
  if (light.begin()) //light sensor inicialization
  {
    Serial.println("Could not find a valid MAX44009 sensor, check wiring!");//for error debugging
    while (1);
  }
  WiFi.hostname("ESP-host");
  WiFi.begin(ssid, password);
  Serial.println("Connecting");//wifi connection establish
  while (WiFi.status() != WL_CONNECTED) { //while not connected print dots
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP()); //wifi connected successfully

  //pin direction definition
  pinMode(RELAY1, OUTPUT);
  pinMode(RELAY2, OUTPUT);
  pinMode(RAIN, INPUT);
  pinMode(ECHOPIN, INPUT_PULLUP);
  pinMode(TRIGPIN, OUTPUT);
  digitalWrite(RELAY1, LOW);
  digitalWrite(RELAY2, LOW);
  litres = 0;
  dist = 0;
  lastmillis = 0;
}
//function for acquiring relay and auto/man control data from database
String httpGETRequest() {
  HTTPClient http; //define http client

  //IP address with path
  http.begin(serverName2);

  //send HTTP GET request
  int httpResponseCode = http.GET();

  String payload = "{}";

  if (httpResponseCode > 0) {
    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode); //for debug
    payload = http.getString(); //string from requested data
  }
  else {
    Serial.print("Error code: ");
    Serial.println(httpResponseCode); //for error debug
  }
  //free resources
  http.end();

  return payload; //return string
}
void httpPOST1() {
  HTTPClient http; //define http client
  //IP address with path to open
  http.begin(serverName);
  //specifing content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  float t = bme.readTemperature();//read temperature from sensor
  float p = (((bme.readPressure()) / pow((1 - ((float)(ELEVATION)) / 44330), 5.255)) / 100.0F); //read and calculate absolute pressure from sensor
  float h = bme.readHumidity(); //read humidity from sensor
  float lux = light.get_lux(); //read light intensity
  double gamma = log(h / 100) + ((17.62 * t) / (243.12 + t));
  float dp = 243.12 * gamma / (17.62 - gamma); //calculate dew point from values
  float rssi = WiFi.RSSI(); //read wifi signal strenght
  soil = map(analogRead(A0), 370, 830, 100, 0); //read analog value from soil moisture sensor and remap it to 0-100%
  rain = !digitalRead(RAIN); //read digital value of rain sensor
  String httpRequestData = "api_key=" + apiKeyValue1 + "&value1=" + String(t) + "&value2=" + String(h) + "&value3=" + String(p) + "&value4=" + String(dp) + "&value5=" + String(soil) + "&value6=" + String(lux) + "&value7=" + String(rain) + "&value8=" + String(litres) + "&value9=" + String(rssi) + ""; //string with gathered data from sensors and api key value
  Serial.print("httpRequestData: "); //for debug
  Serial.println(httpRequestData);

  //send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

  //debug
  if (httpResponseCode > 0) {
    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);
  }
  else {
    Serial.print("Error code: ");
    Serial.println(httpResponseCode);
  }
  //free resources
  http.end();
}
void  httpPOST2() { //function to send states of relays and auto/man control to database
  HTTPClient http; //define http client
  http.begin(serverName); //IP adress with path to access
  http.addHeader("Content-Type", "application/x-www-form-urlencoded"); //define header
  String httpRequestData = "api_key=" + apiKeyValue2 + "&relay1=" + String(rel1) + "&relay2=" + String(rel2) + "&automan=" + String(automatic) + ""; //string with data to update in database
  Serial.print("httpRequestData: "); //debug
  Serial.println(httpRequestData);

  int httpResponseCode = http.POST(httpRequestData); //send POST request

  if (httpResponseCode > 0) { //debug
    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);
  }
  else {
    Serial.print("Error code: ");
    Serial.println(httpResponseCode);
  }
  //free resources
  http.end();
}
void loop() {
  if (WiFi.status() == WL_CONNECTED) { //if connected to wifi
    if (millis() - lastmillis > sleepTimeS * 1000) { //do this if every sleepTimeS defined seconds
      lastmillis = millis();
      httpPOST1();
    }
    if (millis() - lastmillis2 > 1000) { //do this if every second
      lastmillis2 = millis();
      if (counter1 > 0) { //count down counter with reset on zero
        counter1 = counter1 - 1;
        //Serial.println(counter1);
      }
      else {
        counter1 = 30;
      }
      outputsState = httpGETRequest();//get json string with relay and auto/man switches states
      Serial.println(outputsState);//for debug
      StaticJsonDocument<200> doc; //json object definition
      DeserializationError error = deserializeJson(doc, outputsState); //get data from json string
      if (error) { //debug info
        Serial.println("deserializeJson() failed: ");
        return;
      }
      int val1 = doc["relay1"]; //asign acquired values to variables
      int val2 = doc["relay2"];
      automatic = doc["automatic"];

      Serial.print("Relay: 1"); //print values on serial monitor for debug purposes
      Serial.print(" - SET to: ");
      Serial.println(val1);
      Serial.print("Relay: 2");
      Serial.print(" - SET to: ");
      Serial.println(val2);
      Serial.print("Automatic");
      Serial.print(" - SET to: ");
      Serial.println(automatic);
      if (automatic == 0) { //if manual control selected, than asign values to relays
        digitalWrite(RELAY1, !val1);
        digitalWrite(RELAY2, !val2);
      }
      else {
        httpPOST2(); //send states of relays and auto/man control
      }
    }
    if (millis() - lastmillis3 > 1 * 60 * 1000) { //reset error variable every 15 minutes - try pumping again when low water level in tank
      lastmillis3 = millis();
      error = 0;
      Serial.println(counter2);
      if (counter2 >= 3) { //if 3 times in row no water in well
        automatic = 0; //turn control to manual
        counter2 = 0; //reset counter
        httpPOST2(); //update database
      }
    }
  }
  else {
    Serial.println("WiFi Disconnected"); //debug
  }
  digitalWrite(TRIGPIN, LOW); //set the trigger pin to low for 2uS
  delayMicroseconds(2);
  digitalWrite(TRIGPIN, HIGH); //send a 30uS high to trigger ranging
  delayMicroseconds(30);
  digitalWrite(TRIGPIN, LOW); //send pin low again
  float duration = pulseIn(ECHOPIN, HIGH); //read arrived pulse
  filter.in(duration); //send variable to median filter
  duration = filter.out(); //read output of median filter
  dist = (duration / 28) / 2; //calculate distance in cm
  litres = (((100 - dist) / 8.9) * 100); //calculate water tank filled volume
  if (litres > 1100) { //error correction
    litres = 1100;
  }
  if (litres < 0) {
    litres = 0;
  }
  //Serial.println(dist); //debug
  //Serial.println(litres);
  delay(100);
  if (automatic == 1) { //if automatic mode selected
    //next if controls irrigation
    if (litres >= 150 && rain == 0 && soil <= 65) { //if all conditions are true
      if (soil <= 45) { //if soil moisture is under 45
        rel1 = 1; //enable irrigation
      }
      if (rel1 == 1) {
        digitalWrite(RELAY1, LOW);//irrigation is turned on
      }
      else {
        digitalWrite(RELAY1, HIGH); //irrigation is turned off
      }
    }
    else {
      digitalWrite(RELAY1, HIGH); //irrigation is turned off
      rel1 = 0;
    }
    //next if controls water tank filling
    if (litres <= 850) { //if volume is under 850 litres
      if (litres <= 200 && error == 0) { //if volume is under 200 litres and pump has not stopped recently due to lack of water in well
        rel2 = 1; //enable water tank fulfillment
      }
      if (counter1 == 0) {
        counter1 = sleepTimeS * 2; //counter reset
        lastlitres = litres; //save last value of filled volume
        if (error==1) {
          counter2 = counter2 + 1; //count error event every sleepTimeS*2 seconds
        }
      }
      if (counter1 == 1) {
        if (litres - lastlitres > 20 && error == 0) { //if water level is rising continue pumping
          rel2 = 1;
        }
        else { //otherwise stop pumping and asign 1 to error variable 
          digitalWrite(RELAY2, HIGH);
          rel2 = 0;
          error = 1;
        }
      }
      if (rel2 == 1) {
        digitalWrite(RELAY2, LOW); //turn pump on
      }
      else {
        digitalWrite(RELAY2, HIGH);//turn pump off
      }
    }
    else {
      digitalWrite(RELAY2, HIGH);//turn pump off
      rel2 = 0;
    }
  }
}
