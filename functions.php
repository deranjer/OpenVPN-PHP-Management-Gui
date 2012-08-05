<?php

function read_openvpn_config($config_file_name){
//Contains all the functions called by openvpn gui
	Global $a_config_lines, $port_values, $proto_values, $dev_values, $ca_values, $key_values, $crt_values, $key_values, $group_values, $user_values, $dh_values, $server_values, $ifconfig_pool_values, $keepalive_values, $comp_values, $verb_values, $status_values, $management_values, $a_extra_config_settings;
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
	Global $bin_file, $config_dir, $config_file, $server_crt_file, $server_key_file, $ca_crt_name, $ca_key_name, $key_dir_name;
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
	
?>
