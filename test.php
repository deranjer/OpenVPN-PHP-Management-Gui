<?php
error_reporting(E_ALL); 
ini_set('display_errors', 'on'); 
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Net/SSH2.php');
define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);
?>
<head>
        <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
</head>
<?php
$var_file = "/etc/openvpn/easy-rsa/2.0/vars";
$key_dir = "/etc/openvpn/easy-rsa/2.0/keys";
$key_country = "EN";
$key_province = "TEST2";
$var_dir = "/etc/openvpn/easy-rsa/2.0/";
$password = "RHB12admin";
$ssh = new Net_SSH2('localhost');
		if (!$ssh->login('root', 'RHB12+ADMIN')) {
			exit('Login Failed');
		}

			echo "<h2>Check for errors, then continue to <a href='index.php'>Home</a></h2>";
				
				
				
				
				exit;
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
		//now checking on dh1024.pem to see if process is done
		//first searching $dh_result to get the background pid
		$dh_pid = stristr($dh_background, ']');
		//stripping the "]" and " " from the beginning of $dh_pid
		$dh_pid = substr($dh_pid, 2);
		//need to strip out anything after the " " after the pid (usually user@server after a carriage return)
		$dh_pid = stristr($dh_pid, "\n", true); //double quotes needed for substituion
		//finally trim all special char from string
		$dh_pid = trim(preg_replace('/\s+/', ' ', $dh_pid));
		echo "Start.$dh_pid.END<br />";
		//$dh_pid = "11192";
		//$dh_pid_result = $ssh->exec("pgrep -fl ".$dh_pid."");
		//echo "<hr>";
		//echo $ssh->exec("ls");
		//echo "<hr>";
		$dh_pid_result = $ssh->exec("ps aux | grep $dh_pid | grep -v grep");
		$ssh->setTimeout(10);
		//$dh_pid_result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		echo "<pre>$dh_pid_result</pre>";
		echo str_repeat(' ',1024*64);
		//need SECOND occurance of $needle since the result will include the actual command
		//$dh_after_first_result = stristr($dh_pid_result, $dh_pid);
		if(stristr($dh_pid_result, $dh_pid) === TRUE) {
			echo "DH not completed.... waiting...";
			sleep(15);
			echo str_repeat(' ',1024*64);
			if(stristr($dh_pid_result, $dh_pid) === TRUE) {
				echo "DH STILL not completed.... waiting...";
				sleep(15);
				if(stristr($dh_pid_result, $dh_pid) === TRUE) {
					echo "<hr>DH STILL NOT COMPLETED.... manually run <br />source ./vars and ./build-dh in $var_dir then <br />copy dh1024.pem from $key_dir to config directory (usually /etc/openvpn)<hr>";
					echo $ssh->getLog();
					exit;
				}
			}
		}



echo str_repeat(' ',1024*64);
echo $ssh->getLog();
exit;
?>