################################################################################
# Garage Monitor
# 
# This goal of this project is to provide the state of the garage to a personal
# web server.
# 
# Raspberry Pi GPIO Header
# * Reed switches:                GPIO 2, 3            (pins 3, 5)
# * Indicator LEDs:               GPIO 17, 27          (pins 11, 13)
# * Analog to Digital Converter:  GPIO 18, 23, 24, 25  (pins 12, 16, 18, 22) 
# 
# MCP3008 Analog to Digital Converter (ADC)
# * Temperature Sensor #1:        CH 0                 (pin 1)
# 
# created May 15, 2012
# updated November 09, 2012
# by Brian Hanifin
# 
# http://example.com/garage
# http://example.com/GarageAPI/store.php?s=[open,closed,between]&t1=[temp]
################################################################################
# How to run at startup
# /etc/init.d/garagemonitor start
# 
# Kill the process via SSH
# /etc/init.d/garagemonitor stop
# * which runs "sudo killall python"
################################################################################

import RPi.GPIO as GPIO
import datetime, time, sys
import httplib, urllib

# Define basic settings.
OPEN_BTN_MISSING = True    # Is the open button missing?
ENABLE_UPDATES   = True    # To you want to send updates to the web server?
DEBUG_MODE       = False   # Do you want to output debugging information to the console?

# Define GPIO pins used
LED_RED_GPIO    = 2   # "Open" Red LED
LED_GREEN_GPIO  = 3   # "Closed" Green LED
BTN_OPEN_GPIO   = 17  # "Open" Red Switch
BTN_CLOSED_GPIO = 27  # "Closed" Green Switch

# Define the GPIO pins use for the ADC
SPICLK  = 18
SPIMISO = 23
SPIMOSI = 24
SPICS   = 25

# Define the ADC Channel pins used
TEMP1_ADC_CH = 0   # CH0 pin #1

# Define web service settings
SERVICE_HOST       = "example.com"                         # Hostname the service is hosted on.
SERVICE_URL        = "/GarageAPI/store.php"                # Path to the service script.
USER_AGENT         = "Garage Door Monitor (Raspberry Pi)"  # User Agent reported to the service.
HTTP_RETRIES       = 5   # How many times should we retry, should we be unable to contact the service.
HTTP_RETRY_DELAY   = 15  # How many seconds to delay before trying to contact the service again.   

# Define other settings
DOOR_STATE_OPEN    = "open"
DOOR_STATE_CLOSED  = "closed"
DOOR_STATE_BETWEEN = "between"
LED_OFF            = 0
LED_ON             = 1
SENSOR_READ_DELAY  = 1   # How many seconds should we delay before taking our next reading?
LED_START_FLASHES  = 10  # How many times should the LED flash on startup?

# Adjust the settings for debug mode
if DEBUG_MODE: LED_START_FLASHES = 2

# Initialze global variables
doorClosed     = False
doorOpen       = False
doorBetween    = True
lastDoorStatus = "1st_Run"
lastHTTPStatus = 0
temp1          = 0


def changeStatusLEDs(doorClosed, doorOpen):
	GPIO.output(LED_GREEN_GPIO, doorClosed)
	GPIO.output(LED_RED_GPIO, doorOpen)


def convertMillivoltsToFahrenheit(value):
	# convert analog reading to millivolts = ADC * ( 3300 / 1024 )
	millivolts = value * ( 3300.0 / 1024.0)
	
	# 10 mv per degree 
	temp_C = ((millivolts - 100.0) / 10.0) - 40.0
	
	# convert celsius to fahrenheit 
	temp_F = ( temp_C * 9.0 / 5.0 ) + 32
	
	# remove decimal point from millivolts
	millivolts = "%d" % millivolts
	
	# show only one decimal place for temprature and voltage readings
	temp_C = "%.1f" % temp_C
	temp_F = "%.1f" % temp_F
	
	if DEBUG_MODE:
		print "read_adc0:\t", read_adc0
		print "millivolts:\t", millivolts
		print "temp_C:\t\t", temp_C
		print "temp_F:\t\t", temp_F
		print	
	
	return temp_F


def doorStateUpdateFailed():
	print("TODO: Insert doorStateUpdateFailed() reporting code.")


def getButtonStatus(button):
	# Retrieve the closed swtich.
	if button == BTN_CLOSED_GPIO:
		return (not GPIO.input(BTN_CLOSED_GPIO))
	
	# If the open button is not installed ...
	if OPEN_BTN_MISSING:
		# ... assume the door is open when the door is NOT closed.
		if not doorClosed:
			return True
		else:
			return False
	else:
		# Retrieve the state of the door open switch when it is installed.
		return (not GPIO.input(BTN_OPEN_GPIO))


def getDoorStatus(doorClosed, doorOpen):
	if DEBUG_MODE:	print("getDoorStatus()")
	if DEBUG_MODE:	print('doorClosed: {0}    doorOpen: {1}'.format(doorClosed, doorOpen))
	
	if doorOpen:
		currentState = DOOR_STATE_OPEN
		doorBetween = False;
	elif doorClosed:
		currentState = DOOR_STATE_CLOSED
		doorBetween = False;
	else:
		currentState = DOOR_STATE_BETWEEN
		doorBetween = True
	
	if DEBUG_MODE:	print("doorStatus: {0}".format(currentState))
	
	return currentState


def ledFlash(flashes, finishState):
	if DEBUG_MODE: print("ledFlash({0},{1})".format(flashes, finishState))
	
	# Turn off the red LED.
	GPIO.output(LED_RED_GPIO, LED_OFF);
	
	# Subtract one from the flashes loop when we end on a solid LED.
	if finishState == True: flashes = flashes-1;
	
	for i in range(flashes):
		GPIO.output(LED_GREEN_GPIO, LED_OFF);
		time.sleep(0.25);
		GPIO.output(LED_GREEN_GPIO, LED_ON);
		time.sleep(0.25)
	
	# Leave the LED on when requested.
	if finishState == True: GPIO.output(LED_GREEN_GPIO, True);
 

# Read SPI data from MCP3008 chip, 8 possible adc's (0 thru 7)
# * Source: http://learn.adafruit.com/send-raspberry-pi-data-to-cosm/python-script
def readadc(adcnum, clockpin, mosipin, misopin, cspin):
        if ((adcnum > 7) or (adcnum < 0)):
                return -1
        GPIO.output(cspin, True)
	
        GPIO.output(clockpin, False)  # start clock low
        GPIO.output(cspin, False)     # bring CS low
	
        commandout = adcnum
        commandout |= 0x18  # start bit + single-ended bit
        commandout <<= 3    # we only need to send 5 bits here
        for i in range(5):
                if (commandout & 0x80):
                        GPIO.output(mosipin, True)
                else:   
                        GPIO.output(mosipin, False)
                commandout <<= 1
                GPIO.output(clockpin, True)
                GPIO.output(clockpin, False)
	
        adcout = 0
        # read in one empty bit, one null bit and 10 ADC bits
        for i in range(12):
                GPIO.output(clockpin, True)
                GPIO.output(clockpin, False)
                adcout <<= 1
                if (GPIO.input(misopin)):
                        adcout |= 0x1
	
        GPIO.output(cspin, True)
	
        adcout /= 2       # first bit is 'null' so drop it
        return adcout


def sendUpdate(doorStatus, temp1):
	if DEBUG_MODE:	print("sendUpdate()")
	
	params = {'s' : doorStatus, 't1' : temp1, 'time' : datetime}
	params = urllib.urlencode(params)
	headers = {"User-Agent": USER_AGENT, "Content-type": "application/x-www-form-urlencoded", "Accept": "text/plain"}
	
	# Keep trying to to submit the status to the server until the retry count expires.
	for i in range(HTTP_RETRIES):
		conn = httplib.HTTPConnection(SERVICE_HOST)
		conn.request("POST", SERVICE_URL, params, headers)
		if DEBUG_MODE: conn.set_debuglevel(1)
		response = conn.getresponse()
		data = response.read()
		conn.close()
		
		# Store the last HTTP status for later comparison.
		lastHTTPStatus = response.status
		
		# When the update is successful we can stop.
		if response.status == 200:
			break
		# But, if the server isn't responding ..
		else:
			# ... and we have maxed out our retry count, then send a report ...
			if i == HTTP_RETRIES: doorStateUpdateFailed()
			
			#  ... but, if we have more retries, then we set a delay before trying again.
			time.sleep(HTTP_RETRY_DELAY)


def setup():
	GPIO.setmode(GPIO.BCM)

	# Define output pins
	GPIO.setup(LED_GREEN_GPIO, GPIO.OUT) # green LED (door closed indicator)
	GPIO.setup(LED_RED_GPIO, GPIO.OUT)   # red LED   (door open indicator)
	
	# Define input pins
	GPIO.setup(BTN_CLOSED_GPIO, GPIO.IN) # closed switch
	GPIO.setup(BTN_OPEN_GPIO, GPIO.IN)   # open switch
	
	# Define SPI interface pins (for the ADC)
	GPIO.setup(SPIMOSI, GPIO.OUT)
	GPIO.setup(SPIMISO, GPIO.IN)
	GPIO.setup(SPICLK, GPIO.OUT)
	GPIO.setup(SPICS, GPIO.OUT)
	
	# Turn the LEDs off to start.
	GPIO.output(LED_GREEN_GPIO, LED_OFF);
	GPIO.output(LED_RED_GPIO, LED_OFF);
	
	# Indicate the Pi has booted up and the system is ready to monitor the garage.
	ledFlash(LED_START_FLASHES, LED_OFF)


# Do setup type stuff like define the GPIO pins types.
setup()

# Main program loop
while True:
#for i in range(1):
	# Retrieve the button statuses.
	doorClosed = getButtonStatus(BTN_CLOSED_GPIO)
	doorOpen   = getButtonStatus(BTN_OPEN_GPIO)
	
	# Retrieve the door status as text.
	doorStatus = getDoorStatus(doorClosed, doorOpen)
	
	# Change the indicator light letting us know what position the door is in.
	changeStatusLEDs(doorClosed, doorOpen)
	
	# IMPORTANT! Only send a status update when the status has changed.
	if lastDoorStatus != doorStatus:
		# Read the analog pin (temperature sensor LM36)
		read_adc0 = readadc(TEMP1_ADC_CH, SPICLK, SPIMOSI, SPIMISO, SPICS)
		temp1 = convertMillivoltsToFahrenheit(read_adc0)
		
		# Send the current state of the garage to the server.
		if ENABLE_UPDATES:	sendUpdate(doorStatus, temp1)
	
	# Store the last door state for later comparison.
	lastDoorStatus = doorStatus
	
	# Delay in between sensor readings.
	time.sleep(SENSOR_READ_DELAY)  