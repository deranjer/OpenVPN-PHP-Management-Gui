<!DOCTYPE HTML>
<?php include 'sidebar.php';?>
<html>
<head>
        <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
</head>

<body id="bhome">

		<div class="span9">
			<div class="row-fluid">
				<?php
				if (file_exists('install.php') or file_exists('install-openvpn.php')){
					echo "Please delete install files! (install.php and install-openvpn.php)";
				}
				$system_string = shell_exec("lsb_release -a"); //Seeing if the distro is debian
                if ((strpos($system_string, 'Debian') !== false) or (strpos($system_string, 'Ubuntu') !== false)){ // Searching system string for "Debian"
                    echo "<br />";
					echo "<font color='OOFFOO'>Okay!</font> Debian or Debian flavor found, checking status of openvpn...";
					echo "<br />";
					$ovpn_status = shell_exec("service openvpn status");
					if (empty($ovpn_status)){
						$ovpn_status = shell_exec("/etc/init.d/openvpn status");
					}
					echo $ovpn_status;
					echo "<br />";
					if (strpos($ovpn_status, 'not running') !== false){
						echo "Openvpn is apparently not running...";
						echo "<br />";
					}
					if (strpos($ovpn_status, 'is running') !== false){
						echo "Openvpn appears to be running....";
						echo "<br />";
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

