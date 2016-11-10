<?php

	$a=shell_exec("sh /var/www/html/getarp.sh");
	$file_read = fopen("getarp.txt", "r") or die("Unable to open the file");
	//$processedIP = explode(" ", $ip);
	$value = array();
	while(!feof($file_read)){
	$line_of_file = fgets($file_read);
//	echo $line_of_file.'<br />';
	$processed = preg_replace('!\s+!', ' ', $line_of_file);
	if(strpos($processed, 'incomplete') === false){
		$exploded = explode(" ", $processed);
		if($exploded[2]!=null)
			array_push($value, $exploded[2]);
	}
}
echo(json_encode($value));
?>
