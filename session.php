<?php
if (($_GET['action'] == "back") and isset($_POST['password'])){
	session_start();
	$source = $_GET['source'];
	if ($_POST['username'] == ""){
		$_SESSION['username'] = "root";
	} else {$_SESSION['username'] = $_POST['username'];}
	$_SESSION['password'] = $_POST['password'];
	?>
	<script language='javascript'>setTimeout('window.location="<?php echo $source?>"', 10);</script>
	<?php
}


function start_session($source){
	session_start();
	if (!(isset($_SESSION['username'])) or !(isset($_SESSION['password']))){
		echo "<!DOCTYPE HTML>";
		echo "<head>";
		echo "<link type='text/css' rel='stylesheet' href='css/bootstrap.css'/>";
		echo "<title>Session</title>";
		echo "</head>";
		echo "<html>";
		echo "<body>";
		echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='span 10'>";
		if (!(isset($_POST['password']))){
		?>
		<h2>ROOT Access Required</h2>
		<br />
		<b>Heres the nitty gritty:</b>
		<br />
		In this development version OpenVPN Gui will require a root password.
		<br />
		<br />
		It will currently acquire the password and POST it to a session variable.
		<br />
		<br />
		This data CAN be sniffed on your LAN using tools like Wireshark
		<br />
		<br />
		Please Proceed with caution
		<br />
		<br />
		<form class="well" action="session.php?action=back&source=<?php echo $source ?>" method="post" onsubmit="">
			<label>User  -  If blank assume root</label>
			<input type="text" name="username" class="span3" placeholder="Username">
			<label>Root Password</label>
			<input type="password" name="password" class="span3" placeholder="Password">
			<br />
			<br />
			<button type="submit" class="btn btn-danger">Submit</button>
		</form>
		<?php
		echo "</div>";
		echo "</div>";
		echo "</div>";
		echo "</body>";
		echo "</html>";
		exit;
		}else{
		Return;
		}
	}
}
?>
