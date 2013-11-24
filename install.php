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
//Debug - Set to true to get full php error messages
$_SESSION['debug'] = False;
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
        //Make sure folder is writable
        $cwd = getcwd();
        if (!is_writable($cwd)){
            echo "<font color='B22222'>Fatal Error!</font> Cannot write to " . getcwd() . "<br />";
            echo "Make sure the web directory containing install.php is writable to your web server user.  (Usually www-data)<br />";
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
			$bin_file_found="no";
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
                    echo "Checking for config file......<br /><br />";
                    echo "Checking for key directory......<br /><br />";
                    echo "Checking for ca keys......<br /><br />";
                    echo "Checking for server keys.....<br /><br /><br /><br />";
                    echo "Checking for pkitool.....<br /><br />";
                    echo "Checking for openssl.cnf.....<br /><br /><br />";
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
                        } else {$key_dir_found = "no";}
                        if ($ca_crt_name == $current_file){
                                $ca_crt_file = $current_file;
                                $ca_crt_found = "yes";
                                file_put_contents("settings.conf", "ca_crt_name:" . $ca_crt_name.PHP_EOL, FILE_APPEND | LOCK_EX);
                        } else {$ca_crt_found = "no";}
                        if ($ca_key_name == $current_file){
                                $ca_key_file = $current_file;
                                $ca_key_found = "yes";
                                file_put_contents("settings.conf", "ca_key_name:" . $ca_key_name.PHP_EOL, FILE_APPEND | LOCK_EX);
                        } else {$ca_key_found = "no";}
                        //AND if there aren't any client keys located in config_dir
                        if ($server_key_name == $current_file){
                                $server_key_file = $current_file;
                                $server_key_found = "yes";
                                file_put_contents("settings.conf", "server_key_file:" . $server_key_file.PHP_EOL, FILE_APPEND | LOCK_EX);
                        } else {$server_key_found = "no";}
                        if ($server_crt_name == $current_file){
                                $server_crt_file = $current_file;
                                $server_crt_found = "yes";
                                $server_crt_message = "<font color='OOFFOO'>Success!</font> Server crt found: $current_file <br />";
                                file_put_contents("settings.conf", "server_crt_file:" . $server_crt_file.PHP_EOL, FILE_APPEND | LOCK_EX);
                        } else {$server_crt_found = "no";}
                    }
					//Looking for config file		
                    if ($config_file_found == "yes"){
                        echo "<font color='OOFFOO'>Okay!</font> Found config file <b>$config_file</b> in <b>$config_dir</b><br /><br />";
						   //if we can't find any files ending in .conf
                    } else {echo "<font color='B22222'>Error!</font> Unable to locate config file in <b>$config_dir</b><br /><br />";}
					
                    //key dir default is actually $config_dir . "/easy-rsa/2.0/keys" so will check there next
                    if ($key_dir_found != "yes"){
                        if (file_exists($config_dir . "easy-rsa/2.0/keys")){
                            $key_dir = $config_dir . "easy-rsa/2.0/keys";
                            $key_dir_found = "yes";
                            file_put_contents("settings.conf", "key_dir:" . $key_dir.PHP_EOL, FILE_APPEND | LOCK_EX);
                            }else{echo "<font color='B22222'>Warn!</font> Non Critical.... Unable to locate key directory in <b>$config_dir</b> or <b>" . $config_dir . "easy-rsa/2.0/keys</b><br /><br />";}
                    }	
									
                    if ($key_dir_found == "yes"){
                        echo "<font color='OOFFOO'>Okay!</font> Found key dir <b>$key_dir</b><br /><br />";
					}	
					
                    //TODO remove debug
                    //TODO add key dir setting for /easy-rsa/2.0
                    //$ca_key_found = "no";
                    // $ca_crt_found = "no";
                     //$server_crt_found = "no";
                     //$server_key_found = "no";

                    if ($ca_key_found == "yes"){
                        echo "<font color='OOFFOO'>Success!</font> CA key found: <b>$ca_key_file</b><br /><br />";
                    }else{echo "<font color='B22222'>Warn!</font>  Not Critical! CA key not found.<br /><br />";}
					
                    if ($ca_crt_found == "yes"){
                            echo "<font color='OOFFOO'>Success!</font> CA crt found: <b>$ca_crt_file</b> <br />";
                    }else{echo "<font color='B22222'>Error!</font>  CA crt file NOT FOUND!.<br />";}
					
                    if ($server_key_found == "yes"){
                            echo "<font color='OOFFOO'>Success!</font> Server key found: <b>$server_key_file</b> <br />";
                    }else{echo "<font color='B22222'>Error!</font> Server key NOT FOUND!<br />";}
					
                    if ($server_crt_found == "yes"){
                            echo "<font color='OOFFOO'>Success!</font> Server crt found: <b>$server_crt_file</b> <br /><br />";
                    }else{echo "<font color='B22222'>Error!</font> Server crt NOT FOUND!<br /><br />";}
					
                    //Looking for PKITOOL and OPENSSL.CNF
                    $default_pkitool_location = $config_dir . "easy-rsa/2.0/pkitool";
                    if (file_exists($default_pkitool_location)){
                            $pki_tool_found = "yes";
                            echo "<font color='OOFFOO'>Success!</font> Found: <b>$default_pkitool_location</b><br /><br />";
                    } else {
                            echo "<font color='B22222'>Error!</font> Not found... attempting to copy from $easy_rsa_dir to $config_dir<br /><br />";
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
                                    echo "<font color='OOFFOO'>Success!</font> Found: <b>$default_pkitool_location</b><br /><br />";
                            } else {
                                    echo "<font color='B22222'>Fatal Error!</font> PKITOOL copy failed! PKITOOL NOT FOUND!<br /><br />";
                                    echo "You need to manually discover what the issue is and copy PKITOOL to $config_dir" . "easy-rsa/2.0/<br />";
                                    echo "Then re-run this script.<br />";
                                    exit;
                            }
                    }
                    $default_openssl_location = $config_dir . "easy-rsa/2.0/openssl.cnf";
                    if (file_exists($default_openssl_location)){
                        echo "<font color='OOFFOO'>Success!</font> Found: <b>openssl.cnf</b><br />";
                        $found_openssl = "yes";
                    }
                    if ($found_openssl != "yes"){ // If openssl is not found, then start searching
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
                        echo "<font color='B22222'>Error!</font> OPENSSL.CNF not found... required, should have been copied in with PKITOOL, searching... <br /><br />";
                        //Checking SSl version to see which one is installed, then try and match that to a CNF file (usually they have 1.0.1 or something in the cnf filename)
                        echo "<pre>Running openssl version</pre>";
                        echo str_repeat(' ',1024*64);//purge buffer
                        $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
                        //Finding installed openssl version
                        $ssh->write("openssl version\n");
                        $ssh->setTimeout(10);
                        $output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
                        echo $output;
                        echo "<br />";
                        echo str_repeat(' ',1024*64);//purge buffer
                        //Looking for openssl version files
                        $pattern = "/([0-9]+\.+[0-9]+\.+[0-9]+)/";
                        if ($c=preg_match($pattern, $output, $matches)){
                                echo "<pre> Version Found: $matches[0] </pre><br />";
                        } else {echo "No openssl version found? May need openssl installed!<br />";}
                        echo str_repeat(' ',1024*64);
                        //now find the cnf file to match the version
                        //$match[0] = '1.0.0';
                        echo "Searching for exact match to openssl version<br />";
                        echo "$config_dir" . "easy-rsa/2.0/<br /><br />";
                        $easyrsa_dir_files = scandir($config_dir . "easy-rsa/2.0/");
                        foreach($easyrsa_dir_files as $current_file){
                            if ((fnmatch("openssl-$match[0].cnf", $current_file)) or (fnmatch("openssl-$match[0].cnf.gz", $current_file))){ //searching for files ending in .cnf
                                echo "Found exact match:  $current_file<br />";
                                $found_openssl = "yes";
                                $openssl_file = $current_file;
                                echo str_repeat(' ',1024*64);
                            }
                        }

                        if ($found_openssl == "yes"){
                                print "HEre";
                                echo "<font color='OOFFOO'>Success!</font> Found: <b>$openssl_file</b><br /><br />";
                        } else {
                                echo "Error, no exact match found, going to try guessing..<br />";
                                foreach($easyrsa_dir_files as $current_file){
                                    if ((fnmatch("*.cnf", $current_file)) or (fnmatch("*.cnf.gz", $current_file))){ //searching for files ending in .cnf
                                        echo "Found $current_file<br />";
                                        $cnf_files[] = $current_file; //Storing them to an array.
                                        echo str_repeat(' ',1024*64);//purge buffer
                                    }
                                }
                                if (empty($cnf_files[0])){
                                    echo "<font color='B22222'>Fatal Error!</font> OPENSSL.CNF (or variants) not found!<br /><br />";
                                    echo "You need to manually discover what the issue is and copy OPENSSL.CNF (or variants) to $config_dir" . "easy-rsa/2.0/<br />";
                                    echo "Then re-run this script.<br />";
                                    exit;
                                }
                                
                                // Running version compare on my array to find the highest version
                                foreach ($cnf_files as $current_file) {
                                        if (isset($last)) {
                                                $comp = version_compare($last, $current_file);
                                                if (version_compare($last, $current_file, '>')) {
                                                    $higest = $last;
                                                } else { $highest = $current_file;}
                                                $last = $current_file;
                                        } else { $last = $current_file; }
                                }
                                $latest_openssl = $highest;
                                echo "<br />Highest version found is:  $latest_openssl<br />";
                                $openssl_strip = pathinfo($latest_openssl);
                                if ($openssl_strip['extension'] == "gz") {
                                        echo "File is compressed, attempting to decompress with gunzip...<br />";
                                        $easyrsa_dir = $config_dir . "easy-rsa/2.0/";
                                        $output = $ssh->exec("cd $easyrsa_dir; sudo gunzip -v $latest_openssl\n");
                                        $ssh->setTimeout(10);
                                        echo "<pre>$output</pre>";

                                        foreach($easyrsa_dir_files as $current_file){
                                                if (fnmatch($openssl_strip['filename'], $current_file)) {
                                                        $latest_openssl = $current_file;
                                                        echo "Unizp appears to have completed, file name:  $latest_openssl<br />";
                                                } else { "Unzip appears to have failed? " . $openssl_strip['filename'] . " not found!<br />";}
                                        }
                                }
                                echo "Using $latest_openssl! Copying to openssl.cnf<br />";
                                //TODO add this variable $easyrsa_dir far above
                                $easyrsa_dir = $config_dir . "easy-rsa/2.0/";
                                $output = $ssh->exec("cd $easyrsa_dir; sudo cp -v $latest_openssl openssl.cnf\n");
                                echo "<pre>$output</pre>";

                                echo "<font color='OOFFOO'>Success!</font> Found: <b>openssl.cnf</b><br /><br />";	
                                }
                                echo "</div>";
                                //TODO!! Remove this to read the index file in key dir... as root
                      }  //END openssl.cnf not found if statement
					if ($key_dir_found == "yes"){
						echo "Number of keys found in key directory is: $num_keys</div>";
						if ($num_keys == 0){
                                                    echo "<br /><br />Recommend keep at least .csr files in this dir... for records<br />use caution with .key files!</br>"; 
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
		
			//Will run initial setup if ca or server certs are not set up...
			if (($server_crt_found != "yes") or ($server_key_found != "yes") or ($ca_crt_found != "yes") or ($ca_key_found != "yes") and ($_GET['action'] != "force-continue") and ($pki_tool_found == "yes") and ($openssl_found == "yes")){
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
					echo "<a class='btn btn-danger' href='create_certs.php?action=initial-setup&source=installer'>Create Certs</a>";
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
