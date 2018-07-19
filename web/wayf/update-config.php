<?php
// This script reads an existing config.php and generates
// a new file config.new.php merging the default configuration file 
// config.dist.php with the (customized) config.php

if (isset($_SERVER['REMOTE_ADDR'])){
	exit('No direct script access allowed');
}

if (!file_exists('config.dist.php')) {
	die('The default configuration file config.dist.php does not exist in this directory!');
}

if (!file_exists('config.php')) {
	die('The configuration file config.php does not exist in this directory!');
}
require_once('config.php');

echo "Parsing current configuration and default configuration...\n";
$fp = fopen('config.new.php', 'w') or die("Cannot open file 'config.new.php' for writing!") ;
$distConfigFile = file('config.dist.php');
$currentConfigFile = file('config.php');

$configSettings = Array();
foreach ($currentConfigFile as $line){
	if (preg_match('|^\s*(\$(.+?)\s*=.+;)|', $line, $matches)){
		$var = $matches[2];
		$configSettings[$var] = $matches[1];
	}
}

echo "Merging configurations...\n";
foreach ($distConfigFile as $line){
	fwrite($fp, $line);
	
	if (preg_match('|^(\s*)\/\/\$(.+) =|', $line, $matches)){
		$indent = $matches[1];
		$var = $matches[2];
		if (isset($configSettings[$var])){
			echo "* Using from current configuration: ";
			echo $configSettings[$var]."\n";
			$config = $indent.$configSettings[$var]."\n";
			fwrite($fp, $config);
		}
	}

}

fclose($fp);

echo <<<NOTE

A new configuration file 'config.new.php' was created. Please replace the
current configuration file 'config.php' with this new file after reviewing it.

NOTE;

?>