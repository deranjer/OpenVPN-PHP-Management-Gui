<?php
// error_reporting(E_ALL); 
// ini_set('display_errors', 'on'); 
 $filename = $_GET['file'];
if(!file_exists($filename)){
    die('Error: File not found.');
}
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
	header("Content-disposition: attachment; filename=$filename");
	header('Content-type: application/zip');
	header("Content-Transfer-Encoding: binary");
	readfile("$filename");

?>