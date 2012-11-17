<?php
# Define background colors.
$BG_CLOSED_COLOR   = "#060";
$BG_CLOSED_COLOR2  = "#0c0";
$BG_CLOSED_COLOR3  = "#100";

$BG_BETWEEN_COLOR  = "#990";
$BG_BETWEEN_COLOR2 = "#ff0";
$BG_BETWEEN_COLOR3 = "#330";

$BG_OPEN_COLOR     = "#900";
$BG_OPEN_COLOR2    = "#f00";
$BG_OPEN_COLOR3    = "#300";

// Read the value from the status file.
$myFile = "/home1/brianhan/data/Garage.txt";
if (file_exists($myFile)) {
	$data = file_get_contents($myFile);
	//$data = "04:00:00 pm, Fri	open	78.9";
	$vars = explode("\t", $data);
	$timestamp = $vars[0];
	$doorStatus = $vars[1];
	$temp1 = $vars[2];
}

switch ($doorStatus) {
	case "closed":
		$bgcolor  = $BG_CLOSED_COLOR;
		$bgcolor2 = $BG_CLOSED_COLOR2;
		$bgcolor3 = $BG_CLOSED_COLOR3;
		break;
	
	case "between":
		$bgcolor  = $BG_BETWEEN_COLOR;
		$bgcolor2 = $BG_BETWEEN_COLOR2;
		$bgcolor3 = $BG_BETWEEN_COLOR3;
		break;
	
	case "open":
		$bgcolor  = $BG_OPEN_COLOR;
		$bgcolor2 = $BG_OPEN_COLOR2;
		$bgcolor3 = $BG_OPEN_COLOR3;
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
	<style>
	body {background:<?php echo $bgcolor ?>; color:#fff; text-align:center;}
	#status {font-size:5em;}
	.temp {font-size:2.5em; margin:1em;}
	.label {font-size:0.5em;}
	.timestamp {font-size:1em; margin:1em;}
	#notification {color:#fff; margin:0.5em; padding:0.5em;}
	button {font-size:1em;)
	</style>
</head>
<body>
<div id="status"><?php echo "$doorStatus" ?></div>
<?php
if ($temp1 != "0") {
	echo "<div class=\"temp\">";
	echo "<span id=\"temp\">".$temp1."</span>&deg; F";
	echo "<div class=\"label\">Garage Temp</div>";
	echo "</div>";
}
?>
<div class="timestamp">
	<div id="timestamp"><?php echo "$timestamp" ?></div>
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
<script src="js/garagemonitor.js"></script>

<script>
function backgroundPulse() {
	var duration = 500;
	var colors   = arguments;
	var len      = colors.length;
	
	if (len >= 3) {
		$('body').animate( {backgroundColor: colors[2]}, duration );
		$('body').animate( {backgroundColor: colors[1]}, (duration * 2) );
	} else {
		duration = 750;
		$('body').animate( {backgroundColor: colors[1]}, duration );
	}
	$('body').animate( {backgroundColor: colors[0]}, duration );
}

function checkForStatusChange(){
	// Reload the page when the status has changed.
	$.get('read_status.php', function(currentStatus) {
		// Unremark for testing.
		//currentStatus = "04:00:00 pm, Fri	open	78.9";
		
		if (lastStatus != currentStatus) {
		    flashNotify("Status is updating...");
			
			// Exract the values and update the status.
			vals = currentStatus.split("	");
			statusUpdate(vals[1], vals[2], vals[0]);
			
			// Store the last status for later comparison.
			lastStatus = currentStatus;
		}
	});
}

function clearNotify() {
	$("#notification").empty().append("&nbsp;").css({'visibility':'hidden'});
}

function flashNotify(data) {
	$("#notification").css({'visibility':'visible', 'background':'#000'});
	$("#notification").empty().append(data).effect("highlight", {}, 2000);
}

function statusUpdate(status, temp, timeStamp) {
	//location.reload();

<?php
echo "	if (status == \"open\") {\n";
echo "		bg  = \"".$BG_OPEN_COLOR."\";\n";
echo "		bg2 = \"".$BG_OPEN_COLOR2."\";\n";
echo "	} else if (status == \"between\") {\n";
echo "		bg  = \"".$BG_BETWEEN_COLOR."\";\n";
echo "		bg2 = \"".$BG_BETWEEN_COLOR2."\";\n";
echo "	} else if (status ==\"closed\") {\n";
echo "			bg  = \"".$BG_CLOSED_COLOR."\";\n";
echo "			bg2 = \"".$BG_CLOSED_COLOR2."\";\n";
echo "	}";
?>
	
	// Clear the notification.
	clearNotify();
	
	// Update the background color representing the current status.
	//$("body").css({'background-color': bg});
	$("#status").html(status);
	if (temp != "0") $("#temp").html(temp);
	$("#timestamp").html(timeStamp);
	
	// Pulse the background to indicate the status change.
	backgroundPulse(bg, bg2);
}

function submitRequest(a) {
  $.post("pi_request.php",
  {action:a},
  function(data,status){
    flashNotify(data);
  });
}


var bg  = "<?php echo $BG_CLOSED_COLOR ?>";
var bg2 = "<?php echo $BG_CLOSED_COLOR2 ?>";


$("#refresh_request").click(function(){
	submitRequest("refresh");
});

$("#close_request").click(function(){
	submitRequest("close");
});

$(document).ready(function() {
	backgroundPulse("<? echo $bgcolor ?>", "<? echo $bgcolor2 ?>", "<? echo $bgcolor3 ?>")
});

lastStatus = "<?php echo $data ?>";
setInterval( "checkForStatusChange()", 5000 );

</script>

</body>
</html>