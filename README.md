# GarageMonitor
This project uses a switch (or two) and temperature sensor connected to a [Raspberry Pi](http://raspberrypi.org) provide the state of the garage door to a personal web server. This allows my wife and I to check the status of the garage via a bookmark on our smartphones.

## Adafruit Occidentalis
At the time of this writing my Raspberry Pi is running [Adafruit's Educational Linux Distro Occidentalis](http://learn.adafruit.com/adafruit-raspberry-pi-educational-linux-distro/overview) v0.2. Occidentalis is built on Raspbian Wheezy *"but comes with hardware SPI, I2C, one wire, and WiFi support for our wifi adapters. It also has some things to make overall hacking easier such sshd on startup (with key generation on first boot) and  Bonjour (so you can simply ssh raspberrypi.local from any computer on the local network)"*

## Python Script
The software running on my Raspberry Pi is built in Python. The script runs at startup and checks every second (or so) for current status of the Closed/Open switches. When a change in state is detected, the current temperature is taken and the status is sent to a web service.

## Web Service
I created a simplified "web service" in PHP which accepts the current door status (closed, open, or between) and the current temperature. The service logs the status and a timestamp to a file on the server.

## Status Page
The current status is displayed by a PHP page which reads the last status from the server file and displays them. The background color of the entire page is changed to represent the current status (so the status can be known by a quick glance at my phone.

## Run At Startup Service
Copy the contents of garagemonitor-init.d.txt and pasted it into /etc/init.d/garagemonitor using:  
`sudo nano /etc/init.d/garagemonitor`

*Note to self: Adafruit's Webide uses restartd to run this as a service. Review adafruid-webide.sh and install.sh at [Adafruit-WebIDE/scripts](https://github.com/adafruit/Adafruit-WebIDE/tree/master/scripts)*

## 'Keep Garage Monitor Active' Bash Script
Run:  
`crontab -e`  

Then enter a line similar to the following, substituting the path to your bash script.   
`*/5 * * * * bash /usr/share/adafruit/webide/repositories/my-pi-projects/Garage_Monitor/keep_garage_monitor_active.sh`

## Custom Circuit Board
After getting the expansion circuit working on a breadboard, I picked up an *Experimenter Printed Circuit Board* at Radio Shack (Part [276-0170](http://http://www.radioshack.com/product/index.jsp?productId=2102846)). I choose this board because is closely mimics the layout of a breadboard, so it made it easier for me to build a duplicate copy without worrying about making extra connections.

## Photos and More Information
Visit [http://brianhanifin.com/tag/garage-monitor/](http://brianhanifin.com/tag/garage-monitor/) for photos, a circuit diagram, and project updates.