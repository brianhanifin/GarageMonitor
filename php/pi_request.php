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
  $action = $_POST["action"];
} else {
  $action = $_GET["action"];
  echo "action: ".$action;
}

// Take an appropiate action based on the request.
$recognizedAction = True;
switch ($action) {
	case "refresh":
		echo "A status refresh should be available in under 1 minute.";
		break;

	case "close":
		echo "This function has not been implimented yet.";
		break;

	default:
		$recognizedAction = False;
		echo "unknown request";
		break;
}

// Write the value to the status file.
if ($recognizedAction) {
	$myFile = "/home1/brianhan/data/Garage_PiRequest.txt";
	$fh = fopen($myFile, 'w') or die("can't open file");
	fwrite($fh, $timestamp."\t".$action);
	fclose($fh);
}
?>