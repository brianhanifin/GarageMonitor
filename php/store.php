<?php
/*
This script will store the values passed to it in a file.
 */

// Set the timestamp.
date_default_timezone_set('America/Los_Angeles');
$date = new DateTime();
$timestamp = $date->format('h:i:s a, D');

// Retrieve the submitted status.
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
  $doorStatus = $_POST["s"];
  $temp1 = $_POST["t1"];
} else {
  $doorStatus = $_GET["s"];
  $temp1      = $_GET["t1"];
  echo "doorStatus: ".$doorStatus;
}

// Write the value to the status file.
$myFile = "/home1/brianhan/data/Garage.txt";
$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh, $timestamp."\t".$doorStatus."\t".$temp1);
fclose($fh);

// Clear the request file.
$myFile = "/home1/brianhan/data/Garage_PiRequest.txt";
$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh, "");
fclose($fh);
?>