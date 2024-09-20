<?php
	session_start();
	if(!isset($_SESSION['receptionist']))
	{
		header("Location: index.html");
		exit();
	}
	include 'config.php';
	if(!($dbconn = @mysqli_connect($dbhost, $dbuser, $dbpass, $db))) exit('Error connecting to database: ' . mysqli_connect_error());

	require_once 'notifications.php';
	$notificationsManager = new Notifications($dbconn);
	$notifications = $notificationsManager->getNotifications();
?>
<html>
	<head>
		<link href='http://fonts.googleapis.com/css?family=Graduate' rel='stylesheet' type='text/css'>
		<![if !IE]>
		<link href='css/style.css' rel='stylesheet' type='text/css'>
		<![endif]>
		<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>

		<title>Medical Store Management</title>
	</head>

	<body>

	<div id="notifications">
    <div id="lowStockNotifications" class="notification-section">
        <h4>Low Stock Alerts:</h4>
        <ul></ul>
    </div>
    <div id="expiringNotifications" class="notification-section">
        <h4>Expiring Items:</h4>
        <ul></ul>
    </div>
</div>
</div>

<form id="medbuy" method="POST" action="buymeds.php">
	<center>
		<h1>Medical Store Management</h1>
		<hr />
	</center>

	<label for="purchase" style="font-size:25px;">Medicine Purchase:</label>
	<br /><br />

	<label for="name">Name:</label>
	<input type="text" name="name" required/>

	<label for="expirydate" class="desc">Expiry Date(YYYY-MM-DD):</label>
	<input type="text" name="expirydate" pattern="[0-9][0-9][0-9][0-9][-][0-9][0-9][-][0-9][0-9]" required/>
	<br />

	<label for="chemamt">Chemical Amount:</label>
	<input type="text" name="chemamt" required/>

	<label for="qty" class="desc">Quantity:</label>
	<input type="text" name="qty" pattern="[0-9]+" required/>
	<br />

	<label for="cp">Cost Price:</label>
	<input type="text" name="cp" pattern="[0-9]+" required/>

	<label for="sp" class="desc">Selling Price:</label>
	<input type="text" name="sp" pattern="[0-9]+" required/>
	<br />

	<label for="c1">Compound 1:</label>
	<input type="text" name="c1" required/>

	<label for="c2" class="desc">Compound 2:</label>
	<input type="text" name="c2" />
	<br />

	<label for="c3">Compound 3:</label>
	<input type="text" name="c3" />

	<label for="ph" class="desc">Pharma Co.:</label>
	<input type="text" name="ph" required/>
	<br />

	<label for="notes">Notes:</label>
	<input type="text" name="notes" />

	<label for="ex" class="desc">Existing Supplier:</label>
	<select name="ex">
		<option value="N">No</option>
		<option value="Y">Yes</option>
	</select>
	<br />

	<label for="sname" class="desc">Supplier Name:</label>
	<input type="text" name="sname" required/>

	<label for="saddr" class="desc">Supplier Address:</label>
	<input type="text" name="saddr" required/>
	<br />

	<label for="sem" class="desc">Supplier Email:</label>
	<input type="email" name="sem" required/>

	<label for="stel" class="desc">Supplier Tel. No.(only numbers):</label>
	<input type="text" name="stel" pattern="[0-9]+" required/>
	<br />

	<input type="submit" name="submit" value="Submit" class="submit" id="buysub"/>
</form>

<form id="sellmeds"  method="POST" action="sellmeds.php">

	<label for="question" style="font-size:25px;">Medicine Sale:</label>
	<br /><br />

	<label for="medno" class="desc">Select number of different medicines being sold:</label>
	<select name="medno">
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
	</select>
	<br />

	<script	type="text/javascript">
		function getQty(str,idx)
		{
			var xmlhttp;
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					var select = document.getElementById("noof"+idx);
					select.options.length=0;
					var retqty = xmlhttp.responseText;
					var i=0;
					for(i=1;i<=retqty;i++)
					{
						var option = document.createElement("option");
						option.text = i;
						option.value = i;
						var select = document.getElementById("noof"+idx);
						select.appendChild(option);
					}
				}
			}
			xmlhttp.open("GET","getMedsDetails.php?q="+str,true);
			xmlhttp.send();
		}
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function updateNotifications(data) {
    const lowStockList = $('#lowStockNotifications ul');
    const expiringList = $('#expiringNotifications ul');

    lowStockList.empty();
    expiringList.empty();

    if (data.lowStock.length === 0 && data.expiring.length === 0) {
        $('#notifications').hide();
        return;
    }

    $('#notifications').show();

    if (data.lowStock.length > 0) {
        $('#lowStockNotifications').show();
        data.lowStock.forEach(item => {
            lowStockList.append(`<li>${item.name} - Quantity: ${item.qty}</li>`);
        });
    } else {
        $('#lowStockNotifications').hide();
    }

    if (data.expiring.length > 0) {
        $('#expiringNotifications').show();
        data.expiring.forEach(item => {
            expiringList.append(`<li>${item.name} - Expires on: ${item.expiry_date}</li>`);
        });
    } else {
        $('#expiringNotifications').hide();
    }
}

function checkNotifications() {
    $.ajax({
        url: 'get_notifications.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            updateNotifications(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error fetching notifications:", textStatus, errorThrown);
        },
        complete: function() {
            // Check again in 5 minutes
            setTimeout(checkNotifications, 300000);
        }
    });
}

$(document).ready(function() {
    checkNotifications();
});
</script>
</html>
