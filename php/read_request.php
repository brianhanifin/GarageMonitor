<?php
// Read the value from the status file.
$myFile = "/home1/brianhan/data/Garage_PiRequest.txt";
if (file_exists($myFile)) {
	$data = file_get_contents($myFile);
	//$vars = explode("\t", $data);
	//$timestamp = $vars[0];
	//$action = $vars[1];
}
echo $data;
?>