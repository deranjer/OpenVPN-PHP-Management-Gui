<?php 
session_start();
include 'session.php';
include 'functions.php';
include 'sidebar.php';
//error_reporting(E_ALL); 
//ini_set('display_errors', 'on'); 
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
		include('Net/SSH2.php');
?>
<!DOCTYPE HTML>
<html>
<head>
        <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
</head>
<!-- set body id for bootstrap.css to determine which menu item to highlight -->
<body id="bconfigs">

        <div class="span8">
		<h2> Config File Settings</h2>
		<?php
		//read the settings we created in install.php
		read_config_file();
		$config_file_with_path = $config_dir.$config_file;
		read_openvpn_config($config_file_with_path);
		
		if ((isset($_GET['action'])) and ($_GET['action'] == "update")){
			
			$config_file_temp = $config_file;
			file_put_contents($config_file_temp, "port " . $_POST['port_value'].PHP_EOL);
			file_put_contents($config_file_temp, "proto " . $_POST['proto_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "dev " . $_POST['dev_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "ca " . $_POST['ca_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "cert " . $_POST['crt_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "key " . $_POST['key_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "dh " . $_POST['dh_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "server " . $_POST['server_value']. " " . $_POST['server_value2'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "ifconfig-pool-persist " . $_POST['ifconfig_pool_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "keepalive " . $_POST['keepalive_value']. " " . $_POST['keepalive_value2'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "status " . $_POST['status_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "user " . $_POST['user_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "group " . $_POST['group_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($config_file_temp, "verb " . $_POST['verb_value'].PHP_EOL, FILE_APPEND | LOCK_EX);
			//file_put_contents($config_file_temp, "management " . $_POST['management_value']. " " . $_POST['management_value2'].PHP_EOL, FILE_APPEND | LOCK_EX);
			//creating array with just the "extra settings" that will delete any post values that are empty.
			$a_extra_config_settings_write = array();
			foreach ($_POST as $key => $value) {
				if ((in_array($value, $a_extra_config_settings)) and ($value != "")){//cleaning post of stuff not in the extra settings array
					if ($key == $value){
						array_push($a_extra_config_settings_write, $value);//if this is not done will print key and value ie comp-lzo comp-lzo, since I throw away settings that start with integers
					}else{$a_extra_config_settings_write[$key] = $value;
					Continue;}
				}
			}
			
			if ((isset($_POST['new_setting_name'])) and (!empty($_POST['new_setting_name'])) and ($_POST['new_setting_name'] != "New Setting Name (if needed)")) {
				$a_extra_config_settings_write[$_POST['new_setting_name']] = $_POST['new_setting_value'];
			}else{
			if ((isset($_POST['new_setting_value'])) and ($_POST['new_setting_value'] != "New Setting Data")){
				array_push($a_extra_config_settings_write, $_POST['new_setting_value']);
				}
			} 
			//Checking for empty values
			//match POST values to $a_extra_config_settings array, create new array with updated post values.
			foreach ($a_extra_config_settings_write as $key => $value) {
				if (!is_numeric($key)){  //if the value that was added appears to have an option
					file_put_contents($config_file_temp, $key. " " . $value.PHP_EOL, FILE_APPEND | LOCK_EX);
				} else {
				if ($value == ""){Continue;}else{file_put_contents($config_file_temp, $value.PHP_EOL, FILE_APPEND | LOCK_EX);}
				}
			}
		$cwdir = getcwd();
		echo "<h3> Config file written to $cwdir/$config_file_temp.</h3><br />";
		echo "Shall I attempt to copy config file to $config_file_with_path?<br />";
		echo "This will require openvpn to be restarted..... continue?<br />";
		echo "<a class='btn btn-danger' href='config.php?action=restart'>Restart Openvpn</a>";
		echo "       ";
		echo "<a class='btn btn-primary' href='index.php'>I will restart manually</a>";
		exit;
		}
		if ((isset($_GET['action'])) and ($_GET['action'] == "restart")){
			if (! (isset($_SESSION['password']))){
					//will start the session if needed, and return to config.php?action=restart
				   start_session('config.php?action=restart');
				}
				$password = stripslashes(trim($_SESSION['password']));
				$username = stripslashes(trim($_SESSION['username']));
				if ($username == ""){
					$username = "root";
				}
				$ssh = new Net_SSH2('localhost');
				if (!$ssh->login($username, $password)) {
					exit('Login Failed');
				}
				read_config_file();
				$config_file_temp = $config_file;
				$cwdir = getcwd();
				echo "<pre>";
				echo $ssh->exec("sudo killall -v openvpn");
				echo "</pre>";
				echo str_repeat(' ',1024*64);
				echo "<pre>Creating backup of current (working) config! sudo cp $config_dir$config_file $config_dir$config_file.bak";
				echo $ssh->exec("sudo cp $config_dir$config_file $config_dir$config_file.bak");
				echo "</pre>";
				echo str_repeat(' ',1024*64);
				echo "<pre>executing sudo mv $cwdir/$config_file_temp $config_dir$config_file<br />";
				echo $ssh->exec("sudo mv $cwdir/$config_file_temp $config_dir$config_file");
				echo "</pre>";
				echo str_repeat(' ',1024*64);
				$start_openvpn = $ssh->exec("/etc/init.d/openvpn start");
				if (stristr($start_openvpn, 'failed')){
					echo "<pre>$start_openvpn</pre>";
					echo "<pre><font color='B22222'>Whoops</font>... openvpn failed to start!  Most likely an invalid configuration file!</pre>";
					echo "<a class='btn btn-primary' href='config.php?action=revert'>Revert to old config!</a>";
					echo "                  ";
					echo "<a class='btn' href='index.php'>I will fix it manually</a>";
					exit;
				}
				echo "<pre>$start_openvpn</pre>";
				echo "<br />";
				echo "Apparent Success...<br />";
				echo "<a class='btn' href='index.php'>Back to Home</a>";
				exit;
				}
		if ((isset($_GET['action'])) and ($_GET['action'] == "revert")){
				//TODO Session variables
				$password = 'RHB12+ADMIN';
				if (empty($_POST['username'])){
					$username="root";
				}else{$username = stripslashes(trim($_POST['username']));} 
				$ssh = new Net_SSH2('localhost');
				if (!$ssh->login($username, $password)) {
					exit('Login Failed');
				}
				read_config_file();
				$config_file_temp = $config_file;
				$cwdir = getcwd();
				echo "<pre>";
				echo $ssh->exec("sudo killall -v openvpn");
				echo "</pre>";
				echo str_repeat(' ',1024*64);
				echo "<pre>Restoring backup(working) config! sudo mv $config_dir$config_file.bak $config_dir$config_file";
				echo $ssh->exec("sudo mv $config_dir$config_file.bak $config_dir$config_file");
				echo "</pre>";
				echo str_repeat(' ',1024*64);
				$start_openvpn = $ssh->exec("/etc/init.d/openvpn start");
				if (stristr($start_openvpn, 'failed')){
					echo "<pre>$start_openvpn</pre>";
					echo "<pre><font color='B22222'>Whoops</font>... openvpn failed to start again!  View log file for more information...</pre>";
					//TODO Show them log file....
					echo "<a class='btn' href='index.php'>Home Page</a>";
					exit;
				}
				echo "<pre>$start_openvpn</pre>";
				echo "Apparent Success...<br />";
				echo "<a class='btn' href='index.php'>Back to Home</a>";
				exit;
			}
		
		
		echo "<form class='well span6' action='config.php?action=update' method='post'>";
		//Let user change openvpn settings
		//cant do for loop.. some are dropdown selections, some have multiple inputs
		//Since not all values are required... check to see if they are empty first
		if (!(empty($port_values[0]))){
			echo "$port_values[0] : ";
			echo "<input type='text' name='port_value' value='$port_values[1]'><br />";
		}
		if (!(empty($proto_values[0]))){
			echo "$proto_values[0] : ";
			//creating default select options
			if ($proto_values[1] == "udp"){
				$device_choice = "tcp";
			} else { $device_choice = "udp";}
			echo "<select name='proto_value'>";
			echo "<option value='$proto_values[1]' selected='selected'>$proto_values[1]</option>";
			echo "<option value='$device_choice'>$device_choice</option>";
			echo "</select><br />";
		}
		
		if (!(empty($dev_values[0]))){
			echo "$dev_values[0] : ";
			//creating default select options
			if ($dev_values[1] == "tun"){
				$dev_choice = "tap";
			} else { $dev_choice = "tun";}
			echo "<select name='dev_value'>";
			echo "<option value='$dev_values[1]' selected='selected'>$dev_values[1]</option>";
			echo "<option value='$dev_choice'>$dev_choice</option>";
			echo "</select><br />";
		}
		
		if (!(empty($ca_values[0]))){
		echo "$ca_values[0] : ";
		echo "<input type='text' name='ca_value' value='$ca_values[1]'><br />";
		}
		
		if (!(empty($crt_values[0]))){
		echo "$crt_values[0] : ";
		echo "<input type='text' name='crt_value' value='$crt_values[1]'><br />";
		}
		
		if (!(empty($key_values[0]))){
		echo "$key_values[0] : ";
		echo "<input type='text' name='key_value' value='$key_values[1]'><br />";
		}
		
		if (!(empty($dh_values[0]))){
		echo "$dh_values[0] : ";
		echo "<input type='text' name='dh_value' value='$dh_values[1]'><br />";
		}
		
		if (!(empty($server_values[0]))){
		echo "$server_values[0] : ";
		echo "<input type='text' name='server_value' value='$server_values[1]'>";
		echo "<input type='text' name='server_value2' value='$server_values[2]'><br />";
		}
		
		if (!(empty($ifconfig_pool_values[0]))){
		echo "$ifconfig_pool_values[0] : ";
		echo "<input type='text' name='ifconfig_pool_value' value='$ifconfig_pool_values[1]'><br />";
		}
		
		if (!(empty($keepalive_values[0]))){
		echo "$keepalive_values[0] : ";
		echo "<input type='text' name='keepalive_value' value='$keepalive_values[1]'>";
		echo "<input type='text' name='keepalive_value2' value='$keepalive_values[2]'><br />";
		}
		
		if (!(empty($status_values[0]))){
		echo "$status_values[0] : ";
		echo "<input type='text' name='status_value' value='$status_values[1]'><br />";
		}
		
		if (!(empty($user_values[0]))){
		echo "$user_values[0] : ";
		echo "<input type='text' name='user_value' value='$user_values[1]'><br />";
		}
		
		if (!(empty($group_values[0]))){
		echo "$group_values[0] : ";
		echo "<input type='text' name='group_value' value='$group_values[1]'><br />";
		}
		
		if (!(empty($verb_values[0]))){
		echo "$verb_values[0] : ";
		echo "<input type='text' name='verb_value' value='$verb_values[1]'><br />";
		}
		
		//if (!(empty($management_values[0]))){
		//echo "$management_values[0] : ";
		//echo "<input type='text' name='management_value' value='$management_values[1]'>";
		//echo "<input type='text' name='management_value2' value='$management_values[2]'><br />";
		//}
		
		//print_r($a_extra_config_settings);
		
		foreach ($a_extra_config_settings as $key => $value) {
		echo "<input type='text' name='$value' value='$value'> <br />";
		}
		if ((isset($_GET['action'])) and ($_GET['action'] == "add-line")){
			echo "<input type='text' name='new_setting_name' value='New Setting Name (if needed)'>";
			echo "<input type='text' name='new_setting_value' value='New Setting Data'> <br />";
			//array_push($a_extra_config_settings, "New Setting");
		}
		echo "<br /><br />";
		if (!(isset($_GET['action'])) or ($_GET['action'] != "add-line")){
			echo "<a class='btn btn-primary' href='config.php?action=add-line'>Add 1 Line</a>";
		}
		if ((isset($_GET['action'])) and ($_GET['action'] == "add-line")){
			echo "<a class='btn btn-primary' href='config.php?action=del-line'>Remove 1 Line</a>";
		}
		echo "<br /><br />";
		echo "<button type='submit' class='btn btn-danger'>Update Config</button>";
		echo "</form>";
		//echo "Test!";
		//echo "<pre>";
		//print_r($a_extra_config_settings);
		//echo "</pre>";
		
		?>
            <!-- Body content -->
      
			
	
			
			
			
			
			
			
			
        </div>
        

    
<!-- Required to have 2 divs at the end to close sidebar.php -->        

</div>

</body>
</html>
