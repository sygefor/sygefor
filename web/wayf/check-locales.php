<?php
/*
Usage:

* php check-locales.php
  Checks all language strings against the reference language (Enlish).
  It is checked whether a string is missing, obsolete and whether it contains
  the same number of macros.

* php check-locales.php [langauge]
  e.g. php check-locales.php fr
  Outputs a HTML document that contains all strings of the reference language 
  (English) and [language]. This allows to easier spot discrepancies and 
  retranslate strings.

*/

// This script checks for missing and incomplete locales
if (isset($_SERVER['REMOTE_ADDR'])){
	exit('No direct script access allowed');
}

require_once('languages.php');
include('custom-languages.php');

if ($argc > 1){
	$refLang = $argv[1];
	echo <<<HEADER
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
	<h2>Output all "{$refLang}' language strings</h2>
	<dl>
HEADER;

	foreach ($langStrings['en'] as $k => $v){
		echo "<dt style='font-weigth: bold; color: purple;'>String: ".$k."</dt>\n<dd>\n";
		
		echo "<strong>[en]:</strong> <span style='color: gray'>";
		if (substr_count($langStrings['en'][$k], '%s') > 0) {
			echo sprintf($langStrings['en'][$k], 'https://example.value/1', 'https://example.value/2');
		} else {
			echo $langStrings['en'][$k];
		}
		echo "</span><br />\n";
		
		echo "<strong>[".$refLang."]:</strong> ";
		if (isset($langStrings[$refLang][$k]) && substr_count($langStrings[$refLang][$k], '%s') > 0){
			echo sprintf($langStrings[$refLang][$k], 'https://example.value/1', 'https://example.value/2');
		} elseif (isset($langStrings[$refLang][$k])) {
			echo $langStrings[$refLang][$k];
		} else {
			echo "---Missing---";
		}
		
		echo "\n</dd>\n";
	}

	
	echo <<<FOOTER
	</dl>
</html>
FOOTER;

} else {

	echo "The following problems were found:\n";
	$refLang = 'en';
	foreach(array_keys($langStrings) as $lang){
		if ($lang == 'en'){
			continue;
		}
		
		foreach ($langStrings['en'] as $k => $v){
			if (!isset($langStrings[$lang][$k])){
				echo "* In '$lang' missing locale '$k'\n";
			} else if (substr_count($langStrings['en'][$k], '%s') != substr_count($langStrings[$lang][$k], '%s')){
				echo "* In '$lang' the number of substitutions (%s) differ for '$k': ";
				echo substr_count($langStrings['en'][$k], '%s').' vs '.substr_count($langStrings[$lang][$k], '%s');
				echo "\n";
			}
			
		}
		
		foreach ($langStrings[$lang] as $k => $v){
			if (!isset($langStrings['en'][$k])){
				echo "In $lang obsolete locale $k\n";
			}
		}
	}
}

?>