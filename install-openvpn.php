<!DOCTYPE HTML>
<!--Setting up webpage -->
<html>
    <head>
        <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
    <title>Install</title>
    </head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="span 10">
            <?php if (! (isset($_POST['password']))){
                ?>
                <h2>Sudo Access Required</h2>
                <br />
                <b>Heres the nitty gritty:</b>
                <br />
                In this development version OpenVPN Gui will require a sudo password.
                <br />
                <br />
                It will currently acquire the password in a form and POST it to the webpage.
                <br />
                <br />
                This data CAN be sniffed on your LAN using tools like Wireshark
                <br />
                <br />
                Please Proceed with caution
                <br />
                <br />
                <form class="well" action="install-openvpn.php" method="post" onsubmit="">
                    <label>User  -  If blank assume root</label>
                    <input type="text" name="username" class="span3" placeholder="Username">
                    <label>Root Password</label>
                    <input type="password" name="password" class="span3" placeholder="Password">
                    <br />
                    <br />
                    <button type="submit" class="btn btn-danger">Submit</button>
                    <a class="btn btn-primary" href="install.php">Self Install</a>
                </form>
            <?exit;} 
            
            ?>

            <h3>Attempting to install openvpn from the repository!</h3>
            <br />
                <?php 
                ob_implicit_flush(1);
                $password = stripslashes(trim($_POST['password']));
                 if (empty($_POST['username'])){
                    $username="root";
                }else{$username = stripslashes(trim($_POST['username']));} 
                set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
				include('Net/SSH2.php');
				define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);
                $ssh = new Net_SSH2('localhost');
                if (!$ssh->login($username, $password)) {
                    exit('Login Failed');
                }
                echo "Checking Logged In User:   ";
                $ssh_user = $ssh->exec('whoami');
                echo $ssh_user;
                echo str_repeat(' ',1024*64);
                echo "<br />";
                echo "Killing any apt-get processes already running....";
                echo "<br />";
		echo $ssh->exec('killall -v apt-get');
		echo "<br />";
                echo "Updating apt-get....";
		echo str_repeat(' ',1024*64);
                $apt_update = $ssh->exec('sudo apt-get update');
                echo "<pre>$apt_update</pre>";
				echo str_repeat(' ',1024*64);
				$install_openvpn_cmd = $ssh->exec("sudo apt-get -y install openvpn");
				echo "<br />";
				echo "<pre>$install_openvpn_cmd</pre>";
				echo str_repeat(' ',1024*64);
				 if (stristr($install_openvpn_cmd, 'openvpn is already the newest')){
					 echo "Apt-get reporting that openvpn is already the newest version!";
					 echo str_repeat(' ',1024*64);
					 $openvpn_location = $ssh->exec("which openvpn");
					 echo "<br />";
					 echo "Openvpn located at this path: $openvpn_location";
					 echo "<br />";
					 echo "Please edit install.php to include this path if needed and re-run install.php";
					 echo "<br />";
					 echo "http://localhost/openvpn_gui/install.php";
					 echo "<br />";
					 echo "<br />";
					 exit;
				}	elseif (stristr($install_openvpn_cmd, 'Setting up openvpn')){
					 echo str_repeat(' ',1024*64);
					 $openvpn_location = $ssh->exec("which openvpn");
					 echo "Installing openvpn.. checking bin location!";
					 echo "<br />";
					 echo "Openvpn located at this path: $openvpn_location";
					 echo "<br />";
					 echo "Please edit install.php to include this path if needed and re-run install.php";
					 echo "<br />";
					 //TODO... get hearder?  If possible.. if they are not using localhost
					 echo "http://localhost/openvpn_gui/install.php";
					 exit;
				} else
				echo "Unable to determine if openvpn is installing? Checking for bin....";
				 $openvpn_location = $ssh->exec("which openvpn");
				 if ($openvpn_location == "") {
					echo "<br />";
					echo "Openvpn bin NOT found.... please ssh into server and install openvpn manually!";
					exit;
				 } else {
					  echo "Openvpn located at this path: $openvpn_location";
					 echo "<br />";
					 echo "Please edit install.php to include this path if needed and re-run install.php";
					 echo "<br />";
					 echo "<a class='btn btn-primary' href='install.php'>Re-run installer!</a>";
					 exit;
				 }
		?>

            
        </div>        
    </div>
</div>
</body>
</html>
