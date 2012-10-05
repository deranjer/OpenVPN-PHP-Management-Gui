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
?>
<?
//error_reporting(E_ALL); 
//ini_set('display_errors', 'on'); 
//functions.php includes session_start()
			//will need to do the pkitool test now.... TODO move pkitool to previous heading so don't have to test for it twice :(
			//Will reach this if the "initial-setup" button is pressed in code block after this
			If ($_GET['action'] == "initial-setup"){
			if (! (isset($_SESSION['password']))){
					start_session('install.php&action=initial-setup');
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