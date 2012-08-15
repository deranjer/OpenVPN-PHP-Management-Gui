<!DOCTYPE HTML>
<?php include 'sidebar.php';
//error_reporting(E_ALL); 
//ini_set('display_errors', 'on'); 
include 'functions.php';
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Net/SSH2.php');
?>
<html>
<head>
        <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
</head>

<body id="bcerts">
	<div class="span8 row">
	<?php
	
	read_config_file(); //So i can send the variables to the key creation/moving/updating functions
	//Setup button row
	
	echo "<div></div><br />";
	echo "<div class='span2 row-fluid'><a class='btn btn-primary' href='certs.php?action=create_client'>Create Client Key</a></div>";
	echo "<div class='span2 row-fluid'><a class='btn btn-primary' href='certs.php?action=create_server'>Create Server Key</a></div>";
	echo "<div class='span2 row-fluid'><a class='btn btn-primary' href='certs.php?action=create_ca'>Create CA Key</a></div>";
	echo "<br /><hr>";
	//Placing this out front, so it will write it to the file.. don't have to mess with get values multiple times....

	//Server cert already created in functions.php
	//Now need to know what to do with it....
	if ($_GET['action'] == "send_cert"){
		//good.. now what to do with cert!??
		//seeing if we know the hostname that we will need for client config
		read_config_file();
		if ((!(isset($remote_value))) and (!(isset($_GET['remote_value'])))){
			//getting remote value...
			//TODO... use some website like ipchicken to automatically get users' IP... but seriously.. who has a static IP in this age?
			//dump our GET values so they survive the post... really nead a cleaner way to do this.. I bet session would be a good way
			//Bet an actual programmer would know...
			?>
			Well.... we need to know your IP Address (if static) or dynamic host name (dyndns.org or similar)<br />
			for the client config....
			<form class="" action="certs.php" method="get" onsubmit="">
			<h3>Static IP address or hostname</h3>
			<br />
			<br />
			<input type="text" name="remote_value" class="span3" placeholder="Hostname">
			<input type="hidden" name="action" value="send_cert">
			<input type="hidden" name="type" value="<?php echo $_GET['type'];?>">
			<input type="hidden" name="cert_name"  value="<?php echo $_GET['cert_name'];?>">
			<button type="submit" class="btn btn-primary">Submit</button>
			</form>	
			<?php
			exit;
		}
		if (isset($_GET['remote_value'])){ //If we just set the remote hostname in the form above... will come here next.
			$remote_value = $_GET['remote_value'];
			$settings_file = "settings.conf";
			file_put_contents($settings_file, "remote_value:" . $_GET['remote_value'].PHP_EOL, FILE_APPEND | LOCK_EX); //writting for future use...
		}
		
		//thats done... now to create the files for transfer/download
		if($_GET['type'] == "download"){
		read_config_file();
			echo "Keydir:$key_dir_name<br />";
			echo "Type DOWNLOAD Selected... Preparing files...<br />";
			//create tar file.... since it is still in the keys dir will need phpseclib for the first part
			//Getting the cert name....
			$cert_name = $_GET['cert_name'];
			//first to generate a client config file!

			//need to get the config_dir so function can read the server conf file...
			//next need to get the ip address or host name of the server... required to create client config...
			
			$zip_file_name = create_client_config_and_send($cert_name, $config_dir, $config_file, $remote_value, $send_type, $key_dir_name);
			//now that we have the name of the file.. send it to our download page
			echo "<a href=Downloads/download.php?file=$zip_file_name>Download Page</a>";
			
			exit;
		}
	
	
	
	}
	//Are we creating a server cert??
	if (isset($_GET['server_cert_name'])){
		$server_cert_name = $_GET['server_cert_name'];
		//need to pass $config_dir as well.. since it is in functions.php... can't grab variable from there.
		create_server_key($server_cert_name, $config_dir);
		//now need to know what to do with this newly generated key, send to another server, copy it to $config_dir... w/e
		choose_key_send_method($server_cert_name, $key_dir, $config_dir);
		
		//now need to update the key list
		update_key_list($key_dir);
		exit;
	}
	if ($_GET['action'] == "create_server"){
	?>
	<form class="" action="certs.php?action=create_server&" method="get" onsubmit="">
		<h3>Cert name</h3>
		<span>Note: Names CANNOT contain spaces!</span>
		<br />
		<br />
		<input type="text" name="server_cert_name" class="span3" placeholder="Name of Server Cert">
		<button type="submit" class="btn btn-primary">Submit</button>
	</form>
	</div>
	<?php
	exit;
	}

	?>
		<div class="span9"> 
			<div class="row-fluid">
				<?php


				if (file_exists("keys.conf")){
					read_key_file();
					echo "<table class=table table-striped>";
					echo "<tr>";
					echo "<th>Key Name</th>";
					echo "<th>Revoke Key</th>";
					echo "<th>Last Connected</th>";
					echo "<th>Delete Key From List</th>";
					echo "</tr>";
					foreach($a_key_list as $key => $value){
						echo "<tr>";
						echo "<td>$value</td>";
						echo "<td><a class='btn btn-danger' href='certs.php?action=revoke'>Revoke Key</a></td>";
						if (isset($last_connected)){
							echo "<td>$last_connected</td>";
						} else {echo "<td>Unknown</td>";}
						echo "<td><a class='btn btn-danger' href='certs.php?action=remove_list'>Remove Key</a></td>";
						echo "</tr>";
					}
				}
				?>
				<!-- Body content -->
				
				
				
				
			</div>
		</div>
				
				
				
				

        

    
<!-- Required to have 2 divs at the end to close sidebar.php -->        
    </div>
</div>

</body>
</html>
