<?php
session_start();
require 'session.php';
//error_reporting(E_ALL); 
//ini_set('display_errors', 'on'); 
include 'functions.php';
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Net/SSH2.php');
?>
<!DOCTYPE HTML>
<html>
<head>
        <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
</head>
<?php
//TODO: Functionize this entire page... or at least parts of this... could save a lot of lines and simplify
read_config_file();
$default_pkitool_location = $config_dir . "/easy-rsa/2.0/pkitool";
$var_file = $config_dir . "easy-rsa/2.0/vars";
$var_dir = $config_dir . "easy-rsa/2.0/";
$key_dir = $var_dir . "keys/";
//$a_var_lines = file($var_file);
if ($_GET['action'] == "initial-setup"){
	if (! (isset($_SESSION['password']))){
			start_session('create_certs.php?action=initial-setup');
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
	//Check if we have submitted vars variables
		//edit vars file with user settings
	echo "<h2> Editing vars file....Getting Required Information </h2>";
	
	?>
	<form class="well span5" action="create_certs.php?vars=yes" method="post" onsubmit="">
	  <span>Note:  Server and Client names CANNOT contain spaces!</span>
	  <br />
	  <br />
	  <label><b>Key Country</b></label>
	  <input type="text" class="span3" name="key_country" placeholder="US">
	  <span class="help-block">Country Key is created in...</span>
	  <label><b>Key Province</b></label>
	  <input type="text" class="span3" name="key_province" placeholder="CA">
	  <span class="help-block">Key Province or State...</span>
	  <label><b>Key City</b></label>
	  <input type="text" class="span3" name="key_city" placeholder="SanFrancisco">
	  <span class="help-block">Key City...</span>
	  <label><b>Key Org</b></label>
	  <input type="text" class="span3" name="key_org" placeholder="Fort-Funston">
	  <span class="help-block">What Organization/Company key is for..</span>
	  <label><b>Key Email</b></label>
	  <input type="text" class="span3" name="key_email" placeholder="me@myhost.mydomain">
	  <span class="help-block">Email responsible for key...</span>
	  <br />
	  <label><b>Server Name</b></label>
	  <input type="text" class="span3" name="server_name" placeholder="myserver">
	  <span class="help-block">For signing the server key name of key...</span>
	  <br />
	  <label><b>Client Name</b></label>
	  <input type="text" class="span3" name="client_name" placeholder="myclient">
	  <span class="help-block">If you want to create client key now, enter name.</span>
	  <span class="help-block">If not, leave empty.</span>
	  <br />
	  <button type="submit" class="btn">Submit</button>
	</form>
	<?php
	}
	if ($_GET['vars'] == "yes"){
		$key_country = $_POST['key_country'];
		$key_province = $_POST['key_province'];
		$key_city = $_POST['key_city'];
		$key_org = $_POST['key_org'];
		$key_email = $_POST['key_email'];
		$server_name = $_POST['server_name'];
		$client_name = $_POST['client_name'];
		//need to use bash commands to do this... permission issues.
		//editing var file using sed
		//define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);
		if (! (isset($_SESSION['password']))){
			start_session('create_certs.php?vars=yes');
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
		echo $ssh->read('/.*@.*/', NET_SSH2_READ_REGEX);
		$ssh->write("sudo killall -v openvpn\n");
		$ssh->setTimeout(10);
		$output = $ssh->read('/.*@.*[$|#]|.*[P|p]assword.*/', NET_SSH2_READ_REGEX);
		//$ssh->read();
		if (preg_match('/.*[P|p]assword.*/', $output)) {
			$ssh->write($password."\n");
			$ls = $ssh->read('/.*@.*/', NET_SSH2_READ_REGEX);
			echo "<pre>$ls</pre>";
		}
		echo $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		echo str_repeat(' ',1024*64);
		echo "<pre>Editing vars file...$var_file</pre>";
		if ($username != "root"){
			echo "NOT ROOT!  Currently root is REQUIRED!<br />";
			echo "ATTEMPTING WORKAROUND (BETA)<br />";

			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX); //start by reading for the command prompt using regex.. we COULD use our username variable in here to make it even better..
			$ssh->write("sudo sed -i 's/KEY_PROVINCE=.*/KEY_PROVINCE=\"$key_province\"/g' $var_file\n");
			$ssh->setTimeout(10); //right before the read set timeout so php don't crash/timeout on unexpected output
			$output = $ssh->read('/.*@.*[$|#]|.*[P|p]assword.*/', NET_SSH2_READ_REGEX); //reading for either the password or the command prompt
			echo "$output<br />";
			if (preg_match('/.*[P|p]assword.*/', $output)) { //if we read a prompt asking for the sudo password
				$ssh->write($password."\n"); //write our password (Stored in $_SESSION) to the prompt and "hit" enter (\n)
				$sed_output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX); //make sure our password worked, read for the command prompt.....
				echo "<pre>Entering SUDO Password... Check for errors....: $sed_output</pre>"; //echo the output of the command... this usually will print errors/output of command...
			}
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);//Both reads' appear to be required! 
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);//Both reads' appear to be required!
			echo str_repeat(' ',1024*64);//purging output to the browser
			$ssh->write("sudo sed -i 's/KEY_COUNTRY=.*/KEY_COUNTRY=\"$key_country\"/g' $var_file\n");
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*@.*[$|#]|.*[P|p]assword.*/', NET_SSH2_READ_REGEX);
			echo "$output<br />";			
			if (preg_match('/.*[P|p]assword.*/', $output)) {
				$ssh->write($password."\n");
				$sed_output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX); //reading for the prompt....
				echo "<pre>Entering SUDO Password... Check for errors....: $sed_output</pre>";
			}
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo str_repeat(' ',1024*64);
			$ssh->write("sudo sed -i 's/KEY_CITY=.*/KEY_CITY=\"$key_city\"/g' $var_file\n");
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*@.*[$|#]|.*[P|p]assword.*/', NET_SSH2_READ_REGEX);
			echo "$output<br />";			
			if (preg_match('/.*[P|p]assword.*/', $output)) {
				$ssh->write($password."\n");
				$sed_output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX); //reading for the prompt....
				echo "<pre>Entering SUDO Password... Check for errors....: $sed_output</pre>";
			}
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo str_repeat(' ',1024*64);
			$ssh->write("sudo sed -i 's/KEY_ORG=.*/KEY_ORG=\"$key_org\"/g' $var_file\n");
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*@.*[$|#]|.*[P|p]assword.*/', NET_SSH2_READ_REGEX);
			echo "$output<br />";			
			if (preg_match('/.*[P|p]assword.*/', $output)) {
				$ssh->write($password."\n");
				$sed_output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX); //reading for the prompt....
				echo "<pre>Entering SUDO Password... Check for errors....: $sed_output</pre>";
			}
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			$hostname = exec('hostname -f'); //Getting to hostname, need to defeat '@' symbol in email address.
			echo str_repeat(' ',1024*64);
			$ssh->write("sudo sed -i 's/KEY_EMAIL=.*/KEY_EMAIL=\"$key_email\"/g' $var_file\n");
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*'.$hostname.'.*|.*[P|p]assword.*/', NET_SSH2_READ_REGEX);
			echo "$output<br />";
			if (preg_match('/.*[P|p]assword.*/', $output)) {
				$ssh->write($password."\n");
				$sed_output = $ssh->read('/.*'.$hostname.'.*/', NET_SSH2_READ_REGEX); //reading for the prompt....
				echo "<pre>Entering SUDO Password... Check for errors....: $sed_output</pre>";
			}
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			
		
			//now that var file is edited, continue with new setup with sudo, not root
			//TODO.. add support for password when generating keys

			//todo: change the following block to ssh->exec
			echo "<pre>Running . ./vars</pre>";
			echo str_repeat(' ',1024*64);//purge buffer
			$ssh->write("cd ".$var_dir.";source ./vars\n");
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$output</pre>";
			echo str_repeat(' ',1024*64);
		
			//still have to use read/write, since I need the session variables to remain the same.....
			echo "<pre>Running ./clean-all</pre>";
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			$ssh->write("cd ".$var_dir.";./clean-all\n");
			echo "<pre>$output</pre>";
			echo str_repeat(' ',1024*64);
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*@.*[$|#]|.*[P|p]lease.*|.*denied.*/', NET_SSH2_READ_REGEX);
			//If get permission denied, attempt a work around.
			echo "OUTPUT: $output";
			if (stristr($output, 'denied')){//If clean-all gets "permission denied"
				echo "Running command!";
				echo str_repeat(' ',1024*64);
				$ssh->write("cd ".$var_dir.";sudo rm -rf keys\n"); //attempting to remove old key dir
				$output = $ssh->read('/.*@.*[$|#]|.*[P|p]assword.*/', NET_SSH2_READ_REGEX); //Checking if asking for password.
				if (preg_match('/.*[P|p]assword.*/', $output)) {
					$ssh->write($password."\n");
					$rm_output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX); //reading for the prompt....
					echo "<pre>Entering SUDO Password... Check for errors....: $rm_output</pre>";
				}
				$keys = "keys/serial";
				
				$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);

				$ssh->write("sudo rm -rf keys && mkdir keys && sudo chmod go-rwx keys && sudo touch keys/index.txt && sudo echo 01 >".$keys."\n");
				$ssh->setTimeout(10);
				$output = $ssh->read('/.*@.*[$|#]|.*[P|p]assword.*/', NET_SSH2_READ_REGEX); //Checking if asking for password.
				if (preg_match('/.*[P|p]assword.*/', $output)) {
					$ssh->write($password."\n");
					$rm_output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX); //reading for the prompt....
					echo "<pre>Entering SUDO Password... Check for errors....: $rm_output</pre>";
				}
				//TODO Error checking...
				echo "<pre>Clean-all workaround command result: $output</pre>";
			}
			if (stristr($output, 'Please edit')){
				echo "VARS WAS NOT SOURCED..... ABORTING<br />";
				exit;
			}
			$output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$output</pre>";

			
			echo str_repeat(' ',1024*64);
			
			//building the ca --- again, running exec since sudo will not work, and need to string them together under same user

			$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$result</pre>";
			echo str_repeat(' ',1024*64);
			
			
			//building server key
			if ($server_name != ""){
				echo "<pre>Running Server Key Command... ./pkitool --server $server_name</pre>";
				$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
				$ssh->write("cd ".$var_dir.";./pkitool --server ".$server_name."\n");
				$ssh->setTimeout(10);
				$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
				echo "<pre>$result</pre>";	
			} else { echo "<pre>Server Name BLANK!  Skipping.... </pre>";}
			echo str_repeat(' ',1024*64);
			//building client key
			if ($client_name != ""){
				echo "<pre>Running Client Key Command...</pre>";
				$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
				$ssh->write("cd ".$var_dir."; ./pkitool ".$client_name."\n");
				$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
				echo "<pre>$result</pre>";
			} else { echo "<pre>Client Name BLANK!  Skipping.... </pre>";}
			echo str_repeat(' ',1024*64);
			//building dh... TODO...may need to background?  PHP may time out...
			echo "<pre>Building DH... ./build-dh</pre>";
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			$ssh->write("cd ".$var_dir."; ./build-dh\n");
			$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$result</pre>";
			
			echo str_repeat(' ',1024*64);
			//now to copy stuff from pki keys dir to /etc/openpvn ie config_dir
			echo "<pre>Copying keys to $config_dir</pre>";
			//TODO.. multiple commands if server key is blank		
			$keys_dir = $var_dir."keys";
			$server_crt = $server_name.".crt";
			$server_key = $server_name.".key";
			echo "<pre>Running Command: sudo cd $keys_dir;cp ca.crt ca.key dh1024.pem $server_crt $server_key $config_dir</pre>";
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo $ssh->write("sudo cd $keys_dir;cp ca.crt ca.key dh1024.pem $server_crt $server_key $config_dir\n");
			$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$result</pre>";
			echo str_repeat(' ',1024*64);
			//END BETA (NON ROOT) LOOP
		} else { //Root user!
			//Can now run commmands as root, easiest method
			
			$ssh->exec("sed -i 's/KEY_COUNTRY=.*/KEY_COUNTRY=\"$key_country\"/g' $var_file");
			$ssh->exec("sed -i 's/KEY_PROVINCE=.*/KEY_PROVINCE=\"$key_province\"/g' $var_file");
			$ssh->exec("sed -i 's/KEY_CITY=.*/KEY_CITY=\"$key_city\"/g' $var_file");
			$ssh->exec("sed -i 's/KEY_ORG=.*/KEY_ORG=\"$key_org\"/g' $var_file");
			$ssh->exec("sed -i 's/KEY_EMAIL=.*/KEY_EMAIL=\"$key_email\"/g' $var_file");
		
			$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			
			echo "<pre>Running . ./vars</pre>";
			echo str_repeat(' ',1024*64);//purge buffer
			$ssh->write("cd ".$var_dir.";source ./vars\n");
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$output</pre>";
			echo str_repeat(' ',1024*64);


			//still have to use read/write, since I need the session variables to remain the same.....
			echo "<pre>Running ./clean-all</pre>";
			echo str_repeat(' ',1024*64);//purge buffer
			$ssh->write("cd ".$var_dir.";./clean-all\n");
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$output</pre>";
			echo str_repeat(' ',1024*64);
			
						
			//building dh...
			echo "<pre>Building DH... ./build-dh &</pre>";
			$ssh->write("cd ".$var_dir."; ./build-dh &\n");
			//$OPENSSL dhparam -out ${KEY_DIR}/dh${KEY_SIZE}.pem ${KEY_SIZE}
			$dh_background = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$dh_background</pre>";
			//need to get the PID of the background build-dh process so we can make sure it completes
			echo str_repeat(' ',1024*64);

			//building ca
			echo "<pre>Running ./pkitool --initca</pre>";
			echo str_repeat(' ',1024*64);//purge buffer
			$ssh->write("cd ".$var_dir.";./pkitool --initca\n");
			$ssh->setTimeout(10);
			$output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$output</pre>";
			echo str_repeat(' ',1024*64);

			
			//building server key
			if ($server_name != ""){
				echo "<pre>Running Server Key Command... ./pkitool --server $server_name</pre>";
				$ssh->write("cd ".$var_dir.";./pkitool --server ".$server_name."\n");
				$ssh->setTimeout(10);
				$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
				echo "<pre>$result</pre>";	
			} else { echo "<pre>Server Name BLANK!  Skipping.... </pre>";}
			echo str_repeat(' ',1024*64);
		
			
			
			//building client key
			if ($client_name != ""){
				echo "<pre>Running Client Key Command...</pre>";
				$ssh->write("cd ".$var_dir."; ./pkitool ".$client_name."\n");
				$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
				echo "<pre>$result</pre>";
			} else { echo "<pre>Client Name BLANK!  Skipping.... </pre>";}
			echo str_repeat(' ',1024*64);
			
			
			//now to copy stuff from pki keys dir to /etc/openpvn ie config_dir
			echo "<h2>Copying keys to $config_dir</h2>";
			//TODO.. multiple commands if server key is blank		
			$keys_dir = $var_dir."keys";
			$server_crt = $server_name.".crt";
			$server_key = $server_name.".key";
			//can't copy dh.pem over now.. need to ensure process is completed
			echo "Running Command: cd $keys_dir;cp ca.crt ca.key $server_crt $server_key $config_dir";
			echo "<hr>";
			$ssh->write("cd $keys_dir;cp ca.crt ca.key $server_crt $server_key $config_dir\n");
			$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$result</pre>";
			echo str_repeat(' ',1024*64);
		}
		
		
		//now we will create default .conf file
		//getting current working dir (where the php script is.. this is not our phpseclib session)
		$curr_work_dir = getcwd();
		echo "<pre> Creating a default openvpn.conf file....</pre>";
		file_put_contents("openvpn.conf", "port 1194".PHP_EOL);
		file_put_contents("openvpn.conf", "proto udp".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "dev tun".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "ca ca.crt".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "cert ".$server_crt.PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "key ".$server_key.PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "dh dh1024.pem".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "server 172.17.0.0 255.255.255.0".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "ifconfig-pool-persist ipp.txt".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "keepalive 10 120".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "status openvpn-status.log".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "user nobody".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "group users".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "verb 3".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "persist-key".PHP_EOL, FILE_APPEND | LOCK_EX);
		file_put_contents("openvpn.conf", "persist-tun".PHP_EOL, FILE_APPEND | LOCK_EX);
		echo str_repeat(' ',1024*64);
		echo "<pre>Backing up default conf file, copying file to $config_dir</pre>";
		$ssh->write("cd ".$curr_work_dir."; cp openvpn.conf $config_dir\n");
		$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		echo "<pre>$result</pre>";
		$ssh->write("mv openvpn.conf openvpn-default.conf\n");
		$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		echo "<pre>$result</pre>";
		//Now updating config file with the new location of our config file.
		$config_file = "openvpn.conf";
		file_put_contents("settings.conf", "config_file:" . $config_file.PHP_EOL, FILE_APPEND | LOCK_EX);

		//Now scanning the key directory for keys, and adding the names to keys.conf
		//we know the key dir now, since this is a new setup
		$num_keys = 0;
		//need to export list of files so we can extract key names.
		$ssh->write("ls $key_dir > key_list.txt\n");
		$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		//list should now be stored in curr_work_dir, php will read, extract key names.
		$key_filename = "key_list.txt";
		$key_dir_files = file($key_filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		echo "<h3>Reading list of keys.... </h3><br />";
		//Counting the keys in the keydir
		echo str_repeat(' ',1024*64);
		foreach ($key_dir_files as $current_file){
			if (fnmatch("*.csr", $current_file)){
				//first setup, so overwrite old keys.conf if it exists, and start again.
				if ($num_keys == 0){
					file_put_contents("keys.conf", $current_file.PHP_EOL);
				} else{file_put_contents("keys.conf", $current_file.PHP_EOL, FILE_APPEND | LOCK_EX);}
				$key_array[$num_keys] = $current_file;
				$num_keys++;
			}        
		}
		
		//now checking on dh1024.pem to see if process is done
		echo "Now waiting for DH to complete.....<br />";
		echo "<br /><br /><br /><br /><br />";
	
		echo str_repeat(' ',1024*64);
		sleep(10);
		//using pgrep to search for the ./build-dh string in a process....
		exec("pgrep -fl ./build-dh", $output, $return);
		//print_r($output);
		echo "<br />";
		echo str_repeat(' ',1024*64);
		//need to purge pgrep calling itself from the array, so loop through, unset that one, then count array
		foreach($output as $oi=>$o) {
			if(strpos($o,'pgrep')!==false) {
				unset($output[$oi]);
			}
		}
		$return = count($output); //Count should now be zero or 1... if one, that should be DH, continue waiting
		unset($output);//deleting array
		if ($return == 1){
			echo "DH not completed.... waiting...<br />";
			echo "<br /><br /><br /><br /><br />";
			echo str_repeat(' ',1024*64);
			sleep(25);
			exec("pgrep -fl ./build-dh", $output, $return);
			//print_r($output);
			foreach($output as $oi=>$o) {
				if(strpos($o,'pgrep')!==false) {
					unset($output[$oi]);
				}
			}
			$return = count($output);
			unset($output);
			if ($return == 1){
				echo "DH STILL not completed.... waiting...<br />";
				echo "<br /><br /><br /><br /><br />";
				echo str_repeat(' ',1024*64);
				sleep(35);
				exec("pgrep -fl ./build-dh", $output, $return);
				//	print_r($output);
				foreach($output as $oi=>$o) {
					if(strpos($o,'pgrep')!==false) {
						unset($output[$oi]);
					}
				}
				$return = count($output);
				unset($output);					
				if ($return == 1){
					echo "<hr>DH STILL NOT COMPLETED.... manually run <br />source ./vars and ./build-dh in $var_dir then <br />copy dh1024.pem from $key_dir to $config_dir<hr>";
					exit;
				}
			}
		}
		//$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		echo "Process appears to be completed..... reading dir to confirm.<br />";
		echo "<br /><br /><br /><br /><br />";
		echo str_repeat(' ',1024*64);
		sleep(5);
		$ssh->write("cd $keys_dir;ls\n");
		$directory_list = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);//getting the listing of the keys dir looking for dh1024.pem
		echo "<pre>$directory_list</pre>";
		echo str_repeat(' ',1024*64);
		if (!stristr($directory_list, 'dh1024.pem')){//check if the directory listing contains dh1024.pem
			echo "Oops... dh 1024.pem not found?";
			echo "<hr>DH NOT FOUND!.... manually run <br />source ./vars and ./build-dh in $var_dir then <br />copy dh1024.pem from $key_dir to $config_dir<hr>";
			exit;
		}
		echo str_repeat(' ',1024*64);
		echo "<pre>Found dh1024.pem..... Now copying dh1024.pem to $config_dir</pre>";
		$ssh->write("cd $keys_dir; cp dh1024.pem $config_dir\n");
		$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		echo "<pre>$result</pre>";
		echo str_repeat(' ',1024*64);
	
		echo "<pre>Should be ready to start OpenVPN!</pre>";
		echo str_repeat(' ',1024*64);
		$ssh->write("/etc/init.d/openvpn start\n");
		$ssh->setTimeout(10);
		$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
			echo "<pre>$result</pre>";
		echo "<h2>Check for errors, then continue to <a href='index.php'>Home</a></h2>";
		exit;

	}