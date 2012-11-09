<?php
/*
This script will store the values passed to it in a file.
 */


// Read the value from the status file.
$myFile = "/home1/brianhan/data/Garage.txt";
if (file_exists($myFile)) {
	$data = file_get_contents($myFile);
	$vars = explode("\t", $data);
	$timestamp = $vars[0];
	$doorStatus = $vars[1];
	$temp1 = $vars[2];
}

switch ($doorStatus) {
	case "open":
		$bgcolor = "#900";
		break;

	case "between":
		$bgcolor = "#990";
		break;

	case "closed":
		$bgcolor = "#060";
		break;

}
?>
<html>
<title>Garage Monitor</title>
<meta http-equiv="refresh" content="10; URL=./">
<link rel="icon" type="image/png" href="remote.png" />
<style type="text/css">
body {background:<?php echo $bgcolor ?>; color:#fff; text-align:center;}
#status {font-size:800%;}
.temp {font-size:400%; margin:1em;}
.label {font-size:50%;}
</style>
</html>
<body>
<div id="status"><?php echo "$doorStatus" ?></div>
<div class="temp">
	<?php echo "$temp1" ?>&deg; F
	<div class="label">Garage Temp</div>
</div>
<div id="timestamp">
	<?php echo "$timestamp" ?>
	<div class="label">Last Update</div>
</div>
</body>