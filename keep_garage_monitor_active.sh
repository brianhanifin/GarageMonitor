#!/bin/sh
##################
# Run this script with cron every 5 minutes or so to keep the Garage Monitor running!
# Source: http://www.anyexample.com/linux_bsd/bash/check_if_program_is_running_with_bash_shell_script.xml
# 
# Run this script every 5 minutes in crontab (crontab -e)
# */5 * * * * bash /usr/share/adafruit/webide/repositories/my-pi-projects/Garage_Monitor/keep_garage_monitor_active.sh
###

# Define the name of the python script
SERVICE='garagemonitor.py'

# Check to see if the garagemonitor service is still running...
if ps ax | grep -v grep | grep $SERVICE > /dev/null
then
	# IGNORE: the service is running
	echo > /dev/null
else
     # RESTART: /etc/init.d/garagemonitor is not running
     #echo "/etc/init.d/garagemonitor stopped running"
     /etc/init.d/garagemonitor start  &
fi