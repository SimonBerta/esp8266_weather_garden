#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <MedianFilter.h>
#include <Wire.h>
#include <MAX44009.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include "ArduinoJson.h"
#define ELEVATION 143 //316
#define ECHOPIN 15// Pin to receive echo pulse
#define TRIGPIN 13// Pin to send trigger pulse
#define RELAY1 14// Pin to relay1
#define RELAY2 12// Pin to relay2
#define RAIN 2// Pin to rain sensor
float litres, dist;
double lastmillis, lastmillis2;
int automatic = 0;
int rel1=0, rel2=0;
const int sleepTimeS = 15;
// Replace with your network credentials
const char* ssid     = "TP-Link_289D";
const char* password = "simon1997";

// REPLACE with your Domain name and URL path or IP address with path
const char* serverName = "http://192.168.0.110/post-esp-data.php";
const char* serverName2 = "http://192.168.0.110/post-esp-data.php?action=tPmAT5Ab3j7F9";

// Keep this API Key value to be compatible with the PHP code provided in the project page.
// If you change the apiKeyValue value, the PHP file /post-esp-data.php also needs to have the same key
String apiKeyValue1 = "tPmAT5Ab3j7F9";
String apiKeyValue2 = "tPmAT5Ab3j7F8";
String outputsState;

Adafruit_BME280 bme;
MedianFilter filter(3, 0);
MAX44009 light;

void setup() {
  Serial.begin(115200);
  if (!bme.begin(0x77)) {
    Serial.println("Could not find a valid BME280 sensor, check wiring or change I2C address!");
    while (1);
  }
  if (light.begin())
  {
    Serial.println("Could not find a valid MAX44009 sensor, check wiring!");
    while (1);
  }
  WiFi.hostname("ESP-host");
  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());

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
String httpGETRequest() {
  HTTPClient http;

  // Your IP address with path or Domain name with URL path
  http.begin(serverName2);

  // Send HTTP POST request
  int httpResponseCode = http.GET();

  String payload = "{}";

  if (httpResponseCode > 0) {
    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);
    payload = http.getString();
  }
  else {
    Serial.print("Error code: ");
    Serial.println(httpResponseCode);
  }
  // Free resources
  http.end();

  return payload;
}
void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    if (millis() - lastmillis > sleepTimeS * 1000) {
      lastmillis = millis();
      HTTPClient http;
      // Your Domain name with URL path or IP address with path
      http.begin(serverName);

      // Specify content-type header
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");
      float t = bme.readTemperature();
      float p = (((bme.readPressure()) / pow((1 - ((float)(ELEVATION)) / 44330), 5.255)) / 100.0F);
      float h = bme.readHumidity();
      float lux = light.get_lux();
      double gamma = log(h / 100) + ((17.62 * t) / (243.12 + t));
      float dp = 243.12 * gamma / (17.62 - gamma);
      float rssi = WiFi.RSSI();
      float soil = map(analogRead(A0), 370, 830, 100, 0);
      boolean rain = !digitalRead(RAIN);
      String httpRequestData = "api_key=" + apiKeyValue1 + "&value1=" + String(t) + "&value2=" + String(h) + "&value3=" + String(p) + "&value4=" + String(dp) + "&value5=" + String(soil) + "&value6=" + String(lux) + "&value7=" + String(rain) + "&value8=" + String(litres) + "&value9=" + String(rssi) + "";
      Serial.print("httpRequestData: ");
      Serial.println(httpRequestData);

      // Send HTTP POST request
      int httpResponseCode = http.POST(httpRequestData);


      if (httpResponseCode > 0) {
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
      }
      else {
        Serial.print("Error code: ");
        Serial.println(httpResponseCode);
      }
      // Free resources
      http.end();
    }
    if (millis() - lastmillis2 > 1000) {
      lastmillis2 = millis();
      outputsState = httpGETRequest();
      Serial.println(outputsState);
      StaticJsonDocument<200> doc;
      DeserializationError error = deserializeJson(doc, outputsState);
      if (error) {
        Serial.println("deserializeJson() failed: ");
        return;
      }
      int val1 = doc["relay1"];
      int val2 = doc["relay2"];
      automatic = doc["automatic"];

      Serial.print("Relay: 1");
      Serial.print(" - SET to: ");
      Serial.println(val1);
      Serial.print("Relay: 2");
      Serial.print(" - SET to: ");
      Serial.println(val2);
      Serial.print("Automatic");
      Serial.print(" - SET to: ");
      Serial.println(automatic);
      if (automatic == 0) {
        digitalWrite(RELAY1, !val1);
        digitalWrite(RELAY2, !val2);
      }
      else {
        HTTPClient http;
        http.begin(serverName);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        String httpRequestData = "api_key=" + apiKeyValue2 + "&relay1=" + String(!rel1) + "&relay2=" + String(!rel2) + "";
        Serial.print("httpRequestData: ");
        Serial.println(httpRequestData);

        int httpResponseCode = http.POST(httpRequestData);

        if (httpResponseCode > 0) {
          Serial.print("HTTP Response code: ");
          Serial.println(httpResponseCode);
        }
        else {
          Serial.print("Error code: ");
          Serial.println(httpResponseCode);
        }
        // Free resources
        http.end();
      }
    }
  }
  else {
    Serial.println("WiFi Disconnected");
  }
  digitalWrite(TRIGPIN, LOW); // Set the trigger pin to low for 2uS
  delayMicroseconds(2);
  digitalWrite(TRIGPIN, HIGH); // Send a 10uS high to trigger ranging
  delayMicroseconds(30);
  digitalWrite(TRIGPIN, LOW); // Send pin low again
  float duration = pulseIn(ECHOPIN, HIGH); // Read in times pulse
  filter.in(duration);
  duration = filter.out();
  dist = (duration / 28) / 2;
  litres = (((100 - dist) / 8.9) * 100);
  if (litres > 1100) {
    litres = 1100;
  }
  if (litres < 0) {
    litres = 0;
  }
  Serial.println(dist);
  Serial.println(litres);
  delay(100);
  if (automatic == 1) {
    if (litres > 850) {
      digitalWrite(RELAY1, LOW);
      rel1 = 0;
    }
    else {
      digitalWrite(RELAY1, HIGH);
      rel1 = 1;
    }
    if (litres < 250) {
      digitalWrite(RELAY2, LOW);
      rel2 = 0;
    }
    else {
      digitalWrite(RELAY2, HIGH);
      rel2 = 1;
    }
  }
}
