<!DOCTYPE HTML>
<?php include 'sidebar.php';
error_reporting(E_ALL); 
ini_set('display_errors', 'on'); 
include 'functions.php';
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Net/SSH2.php');
?>
<html>
<head>
        <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
</head>

<body id="bcerts">
		<?php
		echo "<div class='span2 row-fluid'><a class='btn btn-primary' href='certs.php?action=create_client'>Create Client Key</a></div>";
		echo "<div class='span2 row-fluid'><a class='btn btn-primary' href='certs.php?action=create_server'>Create Server Key</a></div>";
		echo "<div class='span2 row-fluid'><a class='btn btn-primary' href='certs.php?action=create_ca'>Create CA Key</a></div>";
		?>
		<div class="span9"> 
			<div class="row-fluid">
				<?php
				read_config_file("settings.conf");
				
				
				
				if (file_exists("keys.conf")){
					read_key_file();
					echo "<table class=table table-striped>";
					echo "<tr>";
					echo "<th>Key Name</th>";
					echo "<th>Revoke Key</th>";
					echo "<th>Last Connected</th>";
					echo "<th>Delete Key From List</th>";
					echo "</tr>";
					foreach($a_key_list as $key => $value){
						echo "<tr>";
						echo "<td>$value</td>";
						echo "<td><a class='btn btn-danger' href='certs.php?action=revoke'>Revoke Key</a></td>";
						if (isset($last_connected)){
							echo "<td>$last_connected</td>";
						} else {echo "<td>Unknown</td>";}
						echo "<td><a class='btn btn-danger' href='certs.php?action=remove_list'>Remove Key</a></td>";
						echo "</tr>";
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
