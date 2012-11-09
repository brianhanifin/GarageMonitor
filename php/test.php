<?php
date_default_timezone_set('America/Los_Angeles');

/*
This script will store the values passed to it in a file.
 */

// Set the timestamp.
$date = new DateTime();
$timestamp = $date->format('h:i:s a')." on ".$date->format('D');
echo $timestamp

?>