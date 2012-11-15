<?php
// Read the value from the status file.
$myFile = "/home1/brianhan/data/Garage.txt";
if (file_exists($myFile)) {
	$data = file_get_contents($myFile);
}
echo $data;
?>