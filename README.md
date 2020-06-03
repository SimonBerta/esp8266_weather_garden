# ESP8266 (NodeMCU v1.0) Weather station with irrigation control
## Description:
NodeMCU control unit is used for data acquisition and relay control. Server is running on Windows machine (WAMP server). Data is being stored in SQL database. Client can be opened on any device with internet browser on the same network. Automatic/manual control of irrigation and water tank refilling can be switched from website. Data logging can be turned on and off. Data is visualised with graphs, gauges and a list table on client with an option for selecting the number of last records in database to be showed.
## Changes:
- 2020-02-06 Added schematic of hardware (Schematic.png) made in Fritzing tool (NodeMCU_Weather_Station.fzz).
- 2020-01-06 Added button for logging (data from sensors to database) control (logging start/stop).
- 2020-30-05 Minor changes in code, Added comments to lines in code.
- 2020-28-05 Added full automatic control of irrigation (solenoid valve) and water tank filling (water pump), added fail-safe functions for pump nad valve.
- 2020-08-05 Added Relay control on web client, implementation in arduino code on NodeMCU.
- 2020-07-05 Added client. 
- 2020-03-05 v1.0
