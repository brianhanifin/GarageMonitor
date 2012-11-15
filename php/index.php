<?php
# Define background colors.
$BG_OPEN_COLOR     = "#900";
$BG_OPEN_COLOR2    = "#f00";
$BG_BETWEEN_COLOR  = "#990";
$BG_BETWEEN_COLOR2 = "#ff0";
$BG_CLOSED_COLOR   = "#060";
$BG_CLOSED_COLOR2  = "#0f0";

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
		$bgcolor  = $BG_OPEN_COLOR;
		$bgcolor2 = $BG_OPEN_COLOR2;
		break;

	case "between":
		$bgcolor  = $BG_BETWEEN_COLOR;
		$bgcolor2 = $BG_BETWEEN_COLOR2;
		break;

	case "closed":
		$bgcolor  = $BG_CLOSED_COLOR;
		$bgcolor2 = $BG_CLOSED_COLOR2;
		break;

}
?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
	<link rel="icon" type="image/png" href="garage.png" />
	<link rel="apple-touch-icon" href="apple-touch-icon-iphone.png"  sizes="57x57">
	<link rel="apple-touch-icon" href="apple-touch-icon-ipad.png"  sizes="72x72">
	<link rel="apple-touch-icon" href="apple-touch-icon-iphone4.png" sizes="114x114">
	<link rel="apple-touch-icon" href="apple-touch-icon-ipad3.png" sizes="144x144">
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
	
	#notification {margin:0.5em;}
	</style>
</head>
<body id="body">
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

<div id="notification">&nbsp;</div>
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
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.2.min.js"><\/script>')</script>
<script>window.jQuery.ui || document.write('<script src="js/vendor/jquery-ui-1.9.1.min.js"><\/script>')</script>
<script src="js/plugins.js"></script>

<script>
$(document).ready(function() {
	backgroundPulse("<?php echo $bgcolor2 ?>", "<?php echo $bgcolor ?>")
});

function backgroundPulse(color1, color2) {
	$('body').animate({backgroundColor: color1}, 1000 );
	$('body').animate({backgroundColor: color2}, 1000 );
}

lastStatus = "<?php echo $data ?>";
function checkForStatusChange(){
	// Reload the page when the status has changed.
	$.get('read_status.php', function(currentStatus) {
		if (lastStatus != currentStatus) {
		    flashNotify("Page is reloading...");
			location.reload();
		}
	});
}
setInterval( "checkForStatusChange()", 5000 );

function submitRequest(a) {
  $.post("pi_request.php",
  {action:a},
  function(data,status){
    flashNotify(data);
  });
}

function flashNotify(data) {
	$("#notification").empty().append(data).animate({backgroundColor: "<?php echo $bgcolor2 ?>", color: "#000"}, 1000 )
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