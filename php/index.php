<?php
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
?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
		<meta http-equiv="refresh" content="10; URL=./">
		<link rel="icon" type="image/png" href="remote.png" />
		<meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>Garage Monitor</title>
        <meta name="viewport" content="width=device-width" />
		<style type="text/css">
		body {background:<?php echo $bgcolor ?>; color:#fff; text-align:center;}
		#status {font-size:5em;}
		.temp {font-size:2.5em; margin:1em;}
		.label {font-size:0.5em;}
		#timestamp {font-size:1em; margin:1em;}
		button {font-size:1em;)

		#notification {display:none; margin:0.5em;}
		</style>
    </head>
    <body>
<div id="status"><?php echo "$doorStatus" ?></div>
<?php
if ($temp1 != "0") {
	echo "<div class=\"temp\">";
	echo $temp1."&deg; F";
	echo "<div class=\"label\">Garage Temp</div>";
	echo "</div>";
}
?>
<div id="timestamp">
	<?php echo "$timestamp" ?>
	<div class="label">Last Update</div>
</div>

<div id="notification"></div>
<div id="buttons">
	<button id="refresh_request">Status Refresh</button>
<?php
// Disable this button unless we want to allow this functionality.
if ($doorStatus == "open") {
	//echo "<button id=\"close_request\">Close Door</button>";
}
?>
</div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.2.min.js"><\/script>')</script>

        <script src="js/plugins.js"></script>
<script>
function submitRequest(a) {
  $.post("pi_request.php",
  {action:a},
  function(data,status){
    $("#notification").empty().append(data).show().fadeIn().delay(5000).fadeOut();
  });
}

$("#refresh_request").click(function(){
	submitRequest("refresh");
});

$("#close_request").click(function(){
	submitRequest("close");
});
</script>
    </body>
</html>