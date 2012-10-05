<?php
//NOTES:
//Currently assuming you are running this on the server that is your CA
//and (if already running) have ca.crt and ca.key in openvpn config dir
//PLEASE EDIT LOCATIONS IF THEY ARE NOT DEFAULT FOR DEBIAN
//Default locations:
//
// bin/executable location:     /usr/sbin/openvpn
// config directory:   /etc/openvpn/
// easy rsa dir: /usr/share/doc/openvpn/examples/easy-rsa
//MAKE SURE YOU KEEP THE "/" AT THE BEGINNING AND END OF ALL THE VARIABLES
$bin_file='/usr/sbin/openvpn';
$config_dir='/etc/openvpn/';
$easy_rsa_dir = '/usr/share/doc/openvpn/examples/easy-rsa/';
//END USER EDITS


//include files, error_reporting
//session_start HAS TO BE CALLED on every page reload... I THINK is more secure than storing session in global variable?
//TODO see if we can store password securely OUTSIDE of session variable
//Also, implement https for session variables
//session_start();
//require 'session.php'; //sets up session, gets username/password
require 'functions.php';
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Net/SSH2.php');
//if (!(isset($_SESSION['password']))){
//	start_session('install.php'); //getting username and password for phpseclib ssh terminal
//}

//error_reporting(E_ALL); 
//ini_set('display_errors', 'on'); 
//functions.php includes session_start()

?>
<!DOCTYPE HTML>
<!--Setting up webpage -->
<head>
	<link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
<title>Install</title>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="span 10">
        <h2> Checking Required Elements</h2>
        <br />
        <?php

		//Setting global

        Global $config_file;
        //Making sure the server is running linux
        if (php_uname('s') != "Linux"){
            echo "<h2><font color='B22222'>Error!</font> Web Gui currently only supports Linux servers!";
            exit;    
        }

        //Checking to see if Openvpn is installed
        if (file_exists($bin_file)){
            $bin_file_found="yes";
            $bin_file_status="<font color='OOFFOO'>Okay!</font> Bin file found at <b>$bin_file</b>";
			//Should create file at this time... every other time will need to append.
			file_put_contents("settings.conf", "bin_file:" . $bin_file.PHP_EOL);
        }else{
            $bin_file_status="<font color='B22222'>Error!</font> Unable to locate bin file at <b>$bin_file</b>";
        }
        if (file_exists($config_dir)){ //if openvpn config dir exists
            $config_folder_found="yes";
            $config_folder_status="<font color='OOFFOO'>Okay!</font> Config folder found at <b>$config_dir</b>";
			file_put_contents("settings.conf", "config_dir:" . $config_dir.PHP_EOL, FILE_APPEND | LOCK_EX);
        }else{
            $config_folder_status="<font color='B22222'>Error!</font> Unable to locate config folder at <b>$config_dir</b>";
        }
        ?>
            <div class="row">
                <div class="span3">
                Checking bin file status...... <br />
                Checking config folder status......  <br /> 
                </div>
                <div class="span5">
                <?php echo $bin_file_status; echo "<br />"?>
                <?php echo $config_folder_status; echo "<br />"?>  
                </div> 
            </div>
        </div>
     </div>

    
            <br />
            <br />
            <br />
            <?php if (($bin_file_found == "yes") and ($config_folder_found == "yes")){ //We have everything we need to continue, will now pull config
                    echo "<div class='row'>";
                    echo "<div class='span10'>";
                    echo "<h2>Config File and Key Operations</h2>";
                    
					
					//Calling function to scan config dir, find config file, keys dir.
                    $config_dir_files = scandir($config_dir); //getting array of files in dir
                    echo "<div class='row'>";
                    echo "<div class='span3'>";
                    echo "Checking for config file......<br />";
					echo "Checking for key directory......<br />";
					echo "Checking for ca keys......<br /><br />";
					echo "Checking for server keys.....<br /><br />";
                    echo "</div>";
                    echo "<div class='span5'>";
					//Just need to find the config file to read from it...
					foreach($config_dir_files as $current_file){
						if (fnmatch("*.conf", $current_file)){ //searching for files ending in .conf
                            $config_file = $current_file;
                            $config_file_found = "yes";
							file_put_contents("settings.conf", "config_file:" . $config_file.PHP_EOL, FILE_APPEND | LOCK_EX);
							//Reading config file to get values
							$config_file_full_path = $config_dir . $config_file;
							$vpn_config = read_openvpn_config($config_file_full_path); //in functions.php, creates arrays of config file
							//TODO.. once again, doing a var dump... need to change to call directly..
							extract($vpn_config);
						}
					}		
					//Getting some values from config file
					$ca_crt_name = trim($ca_values[1]);
					$ca_key_name_no_ext = preg_replace("/\\.[^.\\s]{3,4}$/", "", $ca_crt_name);//purging ext from file
					$ca_key_name = $ca_key_name_no_ext . '.key';
					$server_key_name = trim($key_values[1]);
					$server_crt_name = trim($crt_values[1]);						
					//setting default values as no for some variables
					$key_dir_found = "no";
					//Next loop will loop through files again, looking for values found in config file (validity check)
                    foreach($config_dir_files as $current_file){ //looping through files in config dir
					   if (fnmatch("keys", $current_file)){
                            $key_dir = $config_dir . "$current_file";
                            $key_dir_found = "yes";
							file_put_contents("settings.conf", "key_dir:" . $key_dir.PHP_EOL, FILE_APPEND | LOCK_EX);
                        }
						if ($ca_crt_name == $current_file){
							$ca_crt_file = $current_file;
							$ca_crt_found = "yes";
							file_put_contents("settings.conf", "ca_crt_name:" . $ca_crt_name.PHP_EOL, FILE_APPEND | LOCK_EX);
						}
						if ($ca_key_name == $current_file){
							$ca_key_file = $current_file;
							$ca_key_found = "yes";
							file_put_contents("settings.conf", "ca_key_name:" . $ca_key_name.PHP_EOL, FILE_APPEND | LOCK_EX);
						}
						//AND if there aren't any client keys located in config_dir
						if ($server_key_name == $current_file){
							$server_key_file = $current_file;
							$server_key_found = "yes";
							file_put_contents("settings.conf", "server_key_file:" . $server_key_file.PHP_EOL, FILE_APPEND | LOCK_EX);
						}
						if ($server_crt_name == $current_file){
							$server_crt_file = $current_file;
							$server_crt_found = "yes";
							$server_crt_message = "<font color='OOFFOO'>Success!</font> Server crt found: $current_file <br />";
							file_put_contents("settings.conf", "server_crt_file:" . $server_crt_file.PHP_EOL, FILE_APPEND | LOCK_EX);
                        }
                    }
					//key dir default is actually $config_dir . "/easy-rsa/2.0/keys" so will check there next
					if ($key_dir_found != "yes"){
						if (file_exists($config_dir . "easy-rsa/2.0/keys")){
							$key_dir = $config_dir . "easy-rsa/2.0/keys";
                            $key_dir_found = "yes";
							file_put_contents("settings.conf", "key_dir:" . $key_dir.PHP_EOL, FILE_APPEND | LOCK_EX);
						}else{echo "<font color='B22222'>Warn!</font> Non Critical.... Unable to locate key directory in <b>$config_dir</b> or <b>" . $config_dir . "easy-rsa/2.0/keys</b><br />";}
					}	
		
                    if ($config_file_found == "yes"){
                        echo "<font color='OOFFOO'>Okay!</font> Found config file <b>$config_file</b> in <b>$config_dir</b><br />";
						   //if we can't find any files ending in .conf
                    } else {echo "<font color='B22222'>Error!</font> Unable to locate config file in <b>$config_dir</b><br />";}
					
									
					if ($key_dir_found == "yes"){
                        echo "<font color='OOFFOO'>Okay!</font> Found key dir <b>$key_dir</b><br />";
					}	
					
					//TODO remove debug
					//TODO add key dir setting for /easy-rsa/2.0
					//$ca_key_found = "no";
					// $ca_crt_found = "no";
					 //$server_crt_found = "no";
					 //$server_key_found = "no";
					
					if ($ca_key_found == "yes"){
						echo "<font color='OOFFOO'>Success!</font> CA key found: <b>$ca_key_file</b> <br />";
                    }else{echo "<font color='B22222'>Warn!</font>  Not Critical! CA key not found.<br />";}
					
					if ($ca_crt_found == "yes"){
						echo "<font color='OOFFOO'>Success!</font> CA crt found: <b>$ca_crt_file</b> <br />";
                    }else{echo "<font color='B22222'>Error!</font>  CA crt file NOT FOUND!.<br />";}
					
					if ($server_key_found == "yes"){
						echo "<font color='OOFFOO'>Success!</font> Server key found: <b>$server_key_file</b> <br />";
                    }else{echo "<font color='B22222'>Error!</font> Server key NOT FOUND!<br />";}
					
					if ($server_crt_found == "yes"){
						echo "<font color='OOFFOO'>Success!</font> Server crt found: <b>$server_crt_file</b> <br />";
                    }else{echo "<font color='B22222'>Error!</font> Server crt NOT FOUND!<br />";}
					
                    echo "</div>";
					//TODO!! Remove this to read the index file in key dir... as root
					if ($key_dir_found == "yes"){
						echo "Number of keys found in key directory is: $num_keys</div></div></div>";
							if ($num_keys == 0){
								echo "<br />Recommend keep at least .csr files in this dir... for records<br />use caution with .key files!</br>"; 
							echo "</div></div></div>";
							}
					}
            //$config_file= $config_dir . ""
            } else { // OpenVPN bin or config folder NOT found
                    echo "<div class='row'>";
                    echo "<div class='span10'>";
                    echo "<h2>Available Options</h2>";
                    
                   $system_string = shell_exec("lsb_release -a"); //Seeing if the distro is debian
                   if ((strpos($system_string, 'Debian') !== false) or (strpos($system_string, 'Ubuntu') !== false)){ // Searching system string for "Debian"
                        echo "<br />";
                        echo "<font color='OOFFOO'>Okay!</font> Debian or Debian flavor found, you may install";
                        echo "<br />";
                        echo "<br />";
                        echo str_repeat(' ',1024*64); // flushing buffer
                        echo "<div class='row'>";
                        echo "<div class='span3'>";
                        echo "<a class='btn btn-primary' href='install-openvpn.php'>Install OpenVPN Using apt-get</a>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
						exit;
                       
                   } else {
                       echo "<br />";
                       echo "<font color='B22222'>Error!</font> Debian or Debian flavor not found, please install OpenVPN from<br />";
                       echo "your Linux repo or compile from http://openvpn.net/index.php/download/community-downloads.html";
                       echo "<br />";
                       echo "<br />";
                       echo "<a class='btn btn-primary' href='install-openvpn.php'>My Distro uses apt-get, Install it!</a>";
                       
                   }
                   
            }
			
			//will need to do the pkitool test now.... TODO move pkitool to previous heading so don't have to test for it twice :(
			//Will reach this if the "initial-setup" button is pressed in code block after this
			If ($_GET['action'] == "initial-setup"){
			if (! (isset($_SESSION['password']))){
					start_session('install.php');
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
			$default_pkitool_location = $config_dir . "/easy-rsa/2.0/pkitool";
			if (!(file_exists($default_pkitool_location))){
				echo "<font color='B22222'>Error!</font> PKITOOL not found... required, will attempt to copy into $config_dir<br />";
				
				echo $ssh->exec("sudo cp -r $easy_rsa_dir $config_dir");
				echo "Check for errors!.. Proceeding...<br />";
			} else {echo "PKITOOL found, can create certs....<br />";}
				//pkitool found, now need to start generating all certs, starting clean.
				echo "</div>";
				echo "Ready to create certs!  If you continue all current certs will be<br />cleared and deleted. Continue with caution!</br>";
				echo "<a class='btn btn-danger' href='create_certs.php?action=initial-setup&source=installer'>Clean Start!</a>";
				exit;
			}
			
			
			//Will run initial setup if ca or server certs are not set up...
			if (($server_crt_found != "yes") or ($server_key_found != "yes") or ($ca_crt_found != "yes") or ($ca_key_found != "yes") and ($_GET['action'] != "force-continue")){
				//If only the CA key is not found, not critical if CA is set up elsewhere, can force continue or do initial setup.
				if (($ca_key_found != "yes") and ($_GET['action'] != "initial-setup") and ($server_crt_found == "yes") and ($server_key_found == "yes") and ($ca_crt_found == "yes")){
					echo "CA Key not found, not critical if you have a CA setup elsewhere... do you wish to force continue?<br />If you do not know, you most likely should NOT force continue.<br />";
					echo "<div class='span2'><a class='btn btn-danger' href='install.php?action=force-continue'>Force Continue</a></div>";
					echo "<a class='btn btn-danger' href='install.php?action=initial-setup'>Start Initial Setup</a>";
					exit;
					//However, if any other keys/crts missing... will need to force an initial setup, since openvpn should not work if these are missing....
				} elseif (($_GET['action'] != "initial-setup") and ($server_crt_found != "yes") or ($server_key_found != "yes") or ($ca_crt_found != "yes"))  {
					//TODO!!! session.php hangs on handoff, source issue
					echo "</div>";
					echo "One or more critical errors found, will need to run initial setup!<br />";
					echo "<a class='btn btn-danger' href='install.php?action=initial-setup&source=installer'>Start Initial Setup</a>";
					exit;
				}	
			}
			
			
			//If returning from initial setup, check for client name of certs
			if (isset($_GET['client_name'])){
				$client_name = $_GET['client_name'];
				if ($client_name != ""){
				echo "<br />Now you need to handle the client key that was created....<br />";
				echo "<div class='span2 row-fluid'><a class='btn btn-primary' href='certs.php?action=create_client&cert_name=$client_name'>Move Client Key</a></div>";
				exit;
				}
				echo "Continue to Home Page!  <a href='index.php>Home</a></h2>";
			}
			
			
			

			//Checking if openvpn server is set up...
			If (($bin_file_found == "yes") and ($server_crt_found == "yes") and ($server_key_found == "yes")and ($ca_crt_found == "yes") and ($config_folder_found == "yes") and ($config_file_found = "yes")){
			//echo "</div>";
			echo "<br />";
			echo "<br />";
			echo "<div class='row'>";
			echo "<div class='span10'>";
			echo "<h2>No Critical Errors Found....</h2>";
			echo "<br />";
			echo "<div class='row'>";
			echo "<div class='span3'>";
			echo "Checking for pkitool.....<br />";
			echo "<br />";
			echo "<br />";
			echo "<br />";
			echo "<br />";
			echo "Checking if openvpn is running......<br />";
			echo "<br />";
			echo "<br />";
			echo "<br />";
			echo "Printing your config file......<br />";
			echo "</div>";
			echo "<div class='span5'>";
			echo "Checking $config_dir" . "easy-rsa/2.0/....<br />";
			$default_pkitool_location = $config_dir . "/easy-rsa/2.0/pkitool";
				if (file_exists($default_pkitool_location)){
					echo "<font color='OOFFOO'>Okay!</font>    Found: " . $config_dir . "easy-rsa/2.0/<br /><br /><br /><br />";
				} else {
					echo "<font color='B22222'>Error!</font> Not found... attempting to copy from $easy_rsa_dir to $config_dir<br />";
					if (! (isset($_SESSION['password']))){
					   start_session('install.php');
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
					//copying pkitool to default location
					echo $ssh->exec("sudo cp -r $easy_rsa_dir $config_dir"); 
					echo "Checking for errors!..<br />";
					if (file_exists($default_pkitool_location)){
						echo "<font color='OOFFOO'>Okay!</font>    Found: " . $config_dir . "easy-rsa/2.0/<br /><br /><br /><br />";
					} else {echo "<font color='B22222'>Copy failed! Check for errors!</font><br />";
					$pki_tool_found = "no";
					}
				}	
			}
			
			
			
			//Next test... see if openvpn is running
			if (! (isset($_SESSION['password']))){
				   start_session('install.php');
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
			$openvpn_running = $ssh->exec("ps aux | grep openvpn");
				echo str_repeat(' ',1024*64);
				if (stristr($openvpn_running, $bin_file)){
					echo "Running from $bin_file<br />";
					if ($pki_tool_found == "no"){
						echo "<font color='B22222'>PKITOOL was not found.. no keys can be generated without it!!</font><br />";
						}
					echo "<font color='OOFFOO'>Success!</font> All major tests passed!  Continue to Main Page!<br />";
					echo "<a class='btn btn-primary' href='index.php'>Main Page</a>";
					//And.. send to start page
					
				}else{
					echo "Not running... Attempting to start...";
					$openvpn_start = $ssh->exec("/etc/init.d/openvpn start");
					$openvpn_running = $ssh->exec("ps aux | grep openvpn");
					echo "<pre>$openvpn_start</pre>";
					if (stristr($openvpn_running, $bin_file)){
						echo "Running from $bin_file<br />";
						//And.. send to start page
						echo "<font color='OOFFOO'>Success!</font> All major tests passed!  Continue to Main Page!<br />";
						if ($pki_tool_found == "no"){
						echo "<font color='B22222'>PKITOOL was not found.. no keys can be generated without it!!</font><br />";
						}
						echo "<a class='btn btn-primary' href='index.php'>Main Page</a>";
					}else { echo "<font color='B22222'>Error!</font> Unable to start openvpn, please log on and start openvpn service";}
					//Let user choose if I should start...
					}
					
			echo "<pre>";
			print_r($a_config_lines);
			echo "</pre>";
			echo "</div>";			
            ?> 
        
    
</div>  
</body>
</html>
