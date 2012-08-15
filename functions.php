<?php
error_reporting(E_ALL); 
ini_set('display_errors', 'on'); 
//session_start();
include 'session.php';
//include 'functions2.php';
//Contains most the functions called by openvpn gui
function read_openvpn_config($config_file_name){
	//Global $a_config_lines, $port_values, $proto_values, $dev_values, $ca_values, $key_values, $crt_values, $key_values, $group_values, $user_values, $dh_values, $server_values, $ifconfig_pool_values, $keepalive_values, $comp_values, $verb_values, $status_values, $management_values, $a_extra_config_settings;
 	$a_config_lines = file($config_file_name);//read file to array
	//push the values that have no "config settings" like "client-to-client" to an array
	Global $a_extra_config_settings;
    $a_extra_config_settings = array();
	$i = 0;
	foreach ($a_config_lines  as $line_num => $line) {
		if (stristr($line, "cert")){
			$crt_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "port")){
			$port_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "proto")){
			$proto_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "dev")){
			$dev_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "ca")){
			$ca_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "key ")){
			$key_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "dh")){
			$dh_values = explode(" ", trim($line));
			continue;
		}	
		if (stristr($line, "server")){
			$server_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "ifconfig-pool-persist")){
			$ifconfig_pool_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "keepalive")){
			$keepalive_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "status")){
			$status_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "verb")){
			$verb_values = explode(" ", trim($line));
			continue;
		}	
		if (stristr($line, "management")){
			$management_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "user ")){
			$user_values = explode(" ", trim($line));
			continue;
		}
		if (stristr($line, "group ")){
			$group_values = explode(" ", trim($line));
			continue;
		}
		else{array_push($a_extra_config_settings, trim($line));}
	$i++;
	}  
Global $num_settings; 
$num_settings = $i;
//return value will be array of arrays
$return_value = array('a_config_lines' => $a_config_lines, 'port_values' => $port_values, 'proto_values' => $proto_values, 'dev_values' => $dev_values, 'ca_values' => $ca_values, 'key_values' => $key_values, 'crt_values' => $crt_values, 'key_values' => $key_values, 'group_values' => $group_values, 'user_values' => $user_values, 'dh_values' => $dh_values, 'server_values' => $server_values, 'ifconfig_pool_values' => $ifconfig_pool_values, 'keepalive_values' => $keepalive_values, 'comp_values' => $comp_values, 'verb_values' => $verb_values, 'status_values' => $status_values, 'management_values' => $management_values, 'a_extra_config_settings' => $a_extra_config_settings);
return $return_value;
}

function read_config_file(){
	//typical config file	
	// bin_file:/usr/sbin/openvpn
	// config_dir:/etc/openvpn/
	// config_file:openvpn.conf
	// server_crt_file:DMZ-Server.crt
	// server_key_file:DMZ-Server.key
	// ca_crt_name:ca.crt
	// ca_key_name:ca.key
	Global $bin_file, $config_dir, $config_file, $server_crt_file, $server_key_file, $ca_crt_name, $ca_key_name, $key_dir_name, $remote_value;
	//$trimmed_file = file("settings.conf", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$a_settings_lines = file("settings.conf");
	foreach ($a_settings_lines  as $line_num => $line) {
		if (stristr($line, "bin_file")){
			$bin_file_values = explode(":", trim($line));
			$bin_file = $bin_file_values[1];
			continue;
		}
		if (stristr($line, "config_dir")){
			$config_dir_values = explode(":", trim($line));
			$config_dir = $config_dir_values[1];
			continue;
		}
		if (stristr($line, "config_file")){
			$config_file_values = explode(":", trim($line));
			$config_file = $config_file_values[1];
			continue;
		}
		if (stristr($line, "server_crt_file")){
			$server_crt_values = explode(":", trim($line));
			$server_crt_file = $server_crt_values[1];
			continue;
		}
		if (stristr($line, "server_key_file")){
			$server_key_values = explode(":", trim($line));
			$server_key_file = $server_key_values[1];
			continue;
		}
		if (stristr($line, "ca_crt_name")){
			$ca_crt_values = explode(":", trim($line));
			$ca_crt_name = $ca_crt_values[1];
			continue;
		}
		if (stristr($line, "ca_key_name")){
			$ca_key_values = explode(":", trim($line));
			$ca_key_name = $ca_key_values[1];
			continue;
		}
		if (stristr($line, "key_dir")){
			$key_dir_values = explode(":", trim($line));
			$key_dir_name = $key_dir_values[1];
			continue;
		}
		//TODO... actually write a var dir.... right now just using $var_dir = $config_dir . "easy-rsa/2.0/";
		if (stristr($line, "var_dir")){
			$var_dir_values = explode(":", trim($line));
			$var_dir = $var_dir_values[1];
			continue;
		}
		if (stristr($line, "remote_value")){
			$remote_values = explode(":", trim($line));
			$remote_value = $remote_values[1];
			continue;
		}
	}
}

function read_key_file(){
	Global $a_key_list;
	$a_key_list = array();
	$a_key_file_lines = file("keys.conf");
	foreach ($a_key_file_lines  as $line_num => $line) {
	array_push($a_key_list, trim($line));
	}
}

function create_server_key($server_name, $config_dir){
	//creating a server key... first, make sure i have root priv.
	 if (! (isset($_SESSION['password']))){
		 start_session('certs.php?action=create_server');
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
	$var_dir = $config_dir . "easy-rsa/2.0/";
	//first, source vars
	echo "<pre>Running . ./vars</pre>";
	echo str_repeat(' ',1024*64);//purge buffer
	$ssh->write("cd ".$var_dir.";source ./vars\n");
	$ssh->setTimeout(10);
	$output = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
	echo "<pre>$output</pre>";
	echo str_repeat(' ',1024*64);
	
	//building server key
	$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
	echo "<pre>Running Server Key Command... ./pkitool --server $server_name</pre>";
	$ssh->write("cd ".$var_dir.";./pkitool --server ".$server_name."\n");
	$ssh->setTimeout(10);
	$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
	echo "<pre>$result</pre>";	
}

function update_key_list($key_dir){
	 if (! (isset($_SESSION['password']))){
		 start_session('certs.php?action=create_server'); //TODO add cert name to line so don't have to refill form (see create_client_config_and_send)
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
	//Now scanning the key directory for keys, and adding the names to keys.conf
	$num_keys = 0;
	//need to read index.txt and throw it in openvpngui dir so we can read and so we can extract key names.
	$ssh->write("cat $key_dir\index.txt > key_list.txt\n");
	$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
	//list should now be stored in curr_work_dir, php will read, extract key names.
	$key_filename = "key_list.txt";
	$key_list = file($key_filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	echo "<h3>Reading list of keys.... </h3><br />";
	//Counting the keys in the keydir
	echo str_repeat(' ',1024*64);
	foreach ($key_list as $current_key){
		//TODO!! prolly don't need this... each line on index.txt SHOULD be a key... but hey for now leave it... 
		//Since in the future I will need to do some REGEX to get the actual key names 
		if (fnmatch("CN=", $current_key)){
			//first setup, so overwrite old keys.conf if it exists, and start again.
			if ($num_keys == 0){
				file_put_contents("keys.conf", $current_key.PHP_EOL);
			} else{file_put_contents("keys.conf", $current_key.PHP_EOL, FILE_APPEND | LOCK_EX);}
			$key_array[$num_keys] = $current_key; //each line of file will be stored in this array
			$num_keys++;
		}        
	}
}

function choose_key_send_method($cert_name, $key_dir, $config_dir){
	//TODO add bundle option to send config as .ovpn -- see create_client_config func... maybe add code in there?
	//TODO check certs.php... maybe do away with this function?
	?>
	What do you want to do with your new key <?php echo $cert_name;?> ?<br />
	These actions will bundle <?php echo $cert_name;?>.crt, <?php echo $cert_name;?>.key, your ca.crt and <?php echo $cert_name;?>.conf into a zip file.<br />
	<div class="span 2">
	<a class='btn btn-primary' href='certs.php?action=send_cert&type=nothing&cert_name=<?php echo $cert_name;?>'>Do Nothing</a><br /><br />
	<a class='btn btn-primary' href='certs.php?action=send_cert&type=scp&cert_name=<?php echo $cert_name;?>'>SCP to another box</a><br /><br />
	<a class='btn btn-primary' href='certs.php?action=send_cert&type=download&cert_name=<?php echo $cert_name;?>'>Give me a Download Link</a><br /><br />	
	<?php
	exit;
}

function create_client_config_and_send($cert_name, $config_dir, $config_file, $remote_value, $send_type, $key_dir_name){
	//will need phpseclib later.... so...
	if (! (isset($_SESSION['password']))){
		 start_session('certs.php?action=send_cert&type=$send_type&cert_name=$cert_name');
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
	//Creating a default client config file
	//TODO have a check to make sure /etc/openvpn.conf exists....
	//If none exists... then you should prolly do some checks when the webui is first launched.... duh
	if (!(file_exists("openvpn-client-default.conf"))){
		?>
		There is no default client configuration file!<br />
		Will now create default client configuration file!<br />
		<br />
		Reading Server config file to generate client config file......<br />
		<?php
		//creating the default config file...
		$client_config_file_default = "openvpn-client-default.conf";
		//grabbing the settings we can from the server conf file
		//TODO make sure openvpn is even running....
		if ((file_exists($config_dir.$config_file)) and !(file_exists("openvpn-client-default.conf"))){
			//read_config_file($config_dir.$config_file); //read config file....
			$vpn_config = read_openvpn_config($config_dir.$config_file);
			extract($vpn_config);
			echo $config_dir.$config_file;
			echo str_repeat(' ',1024*64); // flushing buffer
			//Now writing the file.... 
			//now the only lines we need to add to the default are the cert and key lines.....
			file_put_contents($client_config_file_default, "client".PHP_EOL);
			file_put_contents($client_config_file_default, "dev " . $dev_values[1].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "proto " . $proto_values[1].PHP_EOL, FILE_APPEND | LOCK_EX);	
			file_put_contents($client_config_file_default, "remote " . $remote_value." ".$port_values[1].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "resolv-retry infinite" .PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "nobind" .PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "persist-key" .PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "persist-tun" .PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "ca " . $ca_values[1].PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "comp-lzo" .PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "verb " . $verb_values[1].PHP_EOL, FILE_APPEND | LOCK_EX);
			echo "Default file openvpn-client-default.conf created!<br />";
		} 	
		if (!(file_exists($config_dir.$config_file))){	//no config file for openvpn exists...
			echo "Whoops.. no config found at $configdir.$config_file!<br />Exiting.....try the install file again??<br />";
		}	
	}
	if (file_exists("openvpn-client-default.conf")){
	$vpn_config = read_openvpn_config($config_dir.$config_file);
			extract($vpn_config);
		echo "Default client file exists... <br />";
		echo "Now copying to new config $cert_name.conf<br />";
		copy("openvpn-client-default.conf", "$cert_name.conf");
		//New Conf file should exist.... Now edit specifics...
		if (file_exists("$cert_name.conf")){
			file_put_contents($client_config_file_default, "cert " . $cert_name . ".crt".PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents($client_config_file_default, "key " . $cert_name . ".key".PHP_EOL, FILE_APPEND | LOCK_EX);
		} else {echo "Error... client config file not found?<br />"; exit;}
		//Finding what dir 
		$curr_work_dir = getcwd();
		echo "Copying needed files...<br />";
		//Now copying all the necc. sh!t to the root folder... then zip it? tar it? idk.. prolly zip
		$ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		$ssh->write("cd $key_dir_name;cp $cert_name.key $ca_values[1] $cert_name.crt $curr_work_dir\n");
		$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		echo "<pre>$result</pre>";
		//HAVE to change the permissions on *.key... or php can't touch it
		echo "Have to change permissions on the *.key file.. or php can't touch..<br />";
		$ssh->write("cd $curr_work_dir;chmod 555 $cert_name.key\n");
		$result = $ssh->read('/.*@.*[$|#]/', NET_SSH2_READ_REGEX);
		echo "<pre>$result</pre>";
		echo str_repeat(' ',1024*64);
		//create array of files to zip
		$files_to_zip = array(
		"$cert_name.crt",
		"$cert_name.key",
		"$ca_values[1]",
		"$cert_name.conf",
		);
		echo "Creating zip file $cert_name.zip<br />";
		echo str_repeat(' ',1024*64);
		$result = create_zip($files_to_zip, "$cert_name.zip", $cert_name);
		// unlink every other file...
		unlink("$cert_name.crt");
		unlink("$cert_name.key");
		unlink("$ca_values[1]");
		unlink("$cert_name.conf");
		
		echo "<br />";
		echo "Result:$result";
		echo str_repeat(' ',1024*64);
		sleep(5);
		// return name of zip file, will use that to generate download link
		return $result;
		
		exit;

	}
	echo "FINAL HERE!<br />";
}
	
//Credit to David Walsh (davidwalsh.name/create-zip-php) for this function
/* creates a compressed zip file */
function create_zip($files = array(),$destination = '', $cert_name, $overwrite = true) {
	//will need phpseclib later.... so...
	if (! (isset($_SESSION['password']))){
		 start_session('certs.php?action=send_cert&type=$send_type&cert_name=$cert_name');
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
  //if the zip file already exists and overwrite is false, return false
  if(file_exists($destination) && !$overwrite) { echo "Will not overwrite!<br />";return false; }
  //vars
  $valid_files = array();
  //if files were passed in...
  if(is_array($files)) {
    //cycle through each file
    foreach($files as $file) {
	  //make sure the file exists
      if(file_exists($file)) {
		
        $valid_files[] = $file;
      }
    }
  }
  //if we have good files...
  if(count($valid_files)) {
    //create the archive
    $zip = new ZipArchive();
    if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
	 return false;
    }
    //add the files
    foreach($valid_files as $file) {
	 $zip->addFile($file,$file);
      echo "File: $file<br />";
    }
    //debug
    echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
	echo "<br />";
	echo "Zero is good....<br />";
	$zip->addFile($file);
	
    //close the zip -- done!
    $zip->close();
	$curr_work_dir = getcwd();
	
	echo "$curr_work_dir/Downloads/$destination<br />";
	//now move the file to our Downloads folder
	if (copy("$destination","$curr_work_dir/Downloads/$destination")) {
		unlink("$destination");
	}
	//and update destination
	$zip_download = "$destination";
	//TODO check if file exists
	return $zip_download;

	}else{return false;}
}


?>
