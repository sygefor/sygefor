<?php // Copyright (c) 2016, SWITCH

/*
******************************************************************************
This file contains common functions of the SWITCHwayf
******************************************************************************
*/

// Initilizes default configuration options if they were not set already
function initConfigOptions(){
	global $defaultLanguage;
	global $commonDomain;
	global $cookieNamePrefix;
	global $redirectCookieName;
	global $redirectStateCookieName;
	global $SAMLDomainCookieName;
	global $SPCookieName;
	global $cookieSecurity;
	global $cookieValidity;
	global $showPermanentSetting;
	global $useImprovedDropDownList;
	global $disableRemoteLogos;
	global $useSAML2Metadata;
	global $SAML2MetaOverLocalConf;
	global $includeLocalConfEntries;
	global $enableDSReturnParamCheck;
	global $useACURLsForReturnParamCheck;
	global $useKerberos;
	global $useReverseDNSLookup;
	global $useEmbeddedWAYF;
	global $useEmbeddedWAYFPrivacyProtection;
	global $useEmbeddedWAYFRefererForPrivacyProtection;
	global $useLogging;
	global $exportPreselectedIdP;
	global $federationName;
	global $supportContactEmail;
	global $federationURL;
	global $organizationURL;
	global $faqURL;
	global $helpURL;
	global $privacyURL;
	global $imageURL;
	global $javascriptURL;
	global $cssURL;
	global $logoURL;
	global $smallLogoURL;
	global $organizationLogoURL;
	global $IDPConfigFile;
	global $backupIDPConfigFile;
	global $metadataFile;
	global $metadataIDPFile;
	global $metadataSPFile;
	global $metadataLockFile;
	global $WAYFLogFile;
	global $kerberosRedirectURL;
	global $instanceIdentifier;
	global $developmentMode;
	global $mainDomain;


	// Set independet default configuration options
	$defaults = array();
	$defaults['mainDomain'] = isset($_GET['entityID']) && $_GET['entityID'] === "https://aaa.sygefor.reseau-urfist.fr" ? "https://formation-rec.ifsem.cnrs.fr" : "https://formation.ifsem.cnrs.fr";
	$defaults['instanceIdentifier'] = 'wayf';
	$defaults['defaultLanguage'] = 'fr';
	$defaults['commonDomain'] = getTopLevelDomain($_SERVER['SERVER_NAME']);
	$defaults['cookieNamePrefix'] = '';
	$defaults['cookieSecurity'] = true;
	$defaults['cookieValidity'] = 100;
	$defaults['showPermanentSetting'] = true;
	$defaults['useImprovedDropDownList'] = false;
	$defaults['disableRemoteLogos'] = true;
	$defaults['useSAML2Metadata'] = true;
	$defaults['SAML2MetaOverLocalConf'] = true;
	$defaults['includeLocalConfEntries'] = true;
	$defaults['enableDSReturnParamCheck'] = true;
	$defaults['useACURLsForReturnParamCheck'] = false;
	$defaults['useKerberos'] = false;
	$defaults['useReverseDNSLookup'] = false;
	$defaults['useEmbeddedWAYF'] = false;
	$defaults['useEmbeddedWAYFPrivacyProtection'] = false;
	$defaults['useEmbeddedWAYFRefererForPrivacyProtection'] = false;
	$defaults['useLogging'] = true; 
	$defaults['exportPreselectedIdP'] = false;
	$defaults['federationName'] = 'Identity Federation';
	$defaults['organizationURL'] = 'http://www.'.$defaults['commonDomain'];
	$defaults['federationURL'] = '';//$defaults['organizationURL'].'/aai';
	$defaults['faqURL'] = '';//'https://sygefor.com/faq';
	$defaults['helpURL'] = '';//$defaults['federationURL'].'/help';
	$defaults['privacyURL'] = '';//$defaults['federationURL'].'/privacy';
	$defaults['supportContactEmail'] = 'support@conjecto.com';
	$defaults['imageURL'] = 'https://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/images';
	$defaults['javascriptURL'] = 'https://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/js';
	$defaults['cssURL'] = 'https://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/css';
	$defaults['IDPConfigFile'] = 'IDProvider.conf.php';
	$defaults['backupIDPConfigFile'] = 'IDProvider.conf.php';
//	$defaults['metadataFile'] = '/etc/shibboleth/shibboleth-sp/main-all-renater-metadata.xml';
	$defaults['metadataFile'] = '/etc/shibboleth/shibboleth-sp/cnrs-metadata.xml';
	$defaults['metadataIDPFile'] = 'IDProvider.metadata.php';
	$defaults['metadataSPFile'] = 'SProvider.metadata.php';
	$lockFileName = preg_replace('/[^-_\.a-zA-Z]/', '', $defaults['instanceIdentifier']);
	$defaults['metadataLockFile'] = (substr($_SERVER['PATH'],0,1) == '/') ? '/tmp/wayf_metadata-'.$lockFileName.'.lock' : 'C:\windows\TEMP\wayf_metadata-'.$lockFileName.'.lock';
	$defaults['WAYFLogFile'] = '/var/log/apache2/wayf.log'; 
	$defaults['kerberosRedirectURL'] = dirname($_SERVER['SCRIPT_NAME']).'kerberosRedirect.php';
//	$defaults['developmentMode'] = isset($_GET['entityID']) && $_GET['entityID'] === "https://aaa.sygefor.reseau-urfist.fr";
	$defaults['developmentMode'] = isset($_GET['entityID']) && $_GET['entityID'] === "https://formation-rec.ifsem.cnrs.fr";

	// Initialize independent defaults
	foreach($defaults as $key => $value){
		if (!isset($$key)){
			$$key = $value;
		}
	}
	
	// Set dependent default configuration options
	$defaults = array();
	$defaults['redirectCookieName'] = $cookieNamePrefix.'_redirect_user_idp';
	$defaults['redirectStateCookieName'] = $cookieNamePrefix.'_redirection_state';
	$defaults['SAMLDomainCookieName'] = $cookieNamePrefix.'_saml_idp';
	$defaults['SPCookieName'] = $cookieNamePrefix.'_saml_sp';
	$defaults['logoURL'] = $imageURL.'/cnrs.jpg';
	$defaults['smallLogoURL'] = $imageURL.'/small-federation-logo.png';
	$defaults['organizationLogoURL'] = $imageURL.'/renater.png';
	
	// Initialize dependent defaults
	foreach($defaults as $key => $value){
		if (!isset($$key)){
			$$key = $value;
		}
	}
}

/******************************************************************************/
// Generates an array of IDPs using the cookie value
function getIdPArrayFromValue($value){

	// Decodes and splits cookie value
	$CookieArray = preg_split('/ /', $value);
	$CookieArray = array_map('base64_decode', $CookieArray);
	
	return $CookieArray;
}

/******************************************************************************/
// Generate the value that is stored in the cookie using the list of IDPs
function getValueFromIdPArray($CookieArray){

	// Merges cookie content and encodes it
	$CookieArray = array_map('base64_encode', $CookieArray);
	$value = implode(' ', $CookieArray);
	return $value;
}

/******************************************************************************/
// Append a value to the array of IDPs
function appendValueToIdPArray($value, $CookieArray){
	
	// Remove value if it already existed in array
	foreach (array_keys($CookieArray) as $i){
		if ($CookieArray[$i] == $value){
			unset($CookieArray[$i]);
		}
	}
	
	// Add value to end of array
	$CookieArray[] = $value;
	
	return $CookieArray;
}

/******************************************************************************/
// Checks if the configuration file has changed. If it has, check the file
// and change its timestamp.
function checkConfig($IDPConfigFile, $backupIDPConfigFile){
	
	// Do files have the same modification time
	if (filemtime($IDPConfigFile) == filemtime($backupIDPConfigFile))
		return true;
	
	// Availability check
	if (!file_exists($IDPConfigFile))
		return false;
	
	// Readability check
	if (!is_readable($IDPConfigFile))
		return false;
	
	// Size check
	if (filesize($IDPConfigFile) < 200)
		return false;
	
	// Make modification time the same
	// If that doesnt work we won't notice it
	touch ($IDPConfigFile, filemtime($backupIDPConfigFile));
	
	return true;
}

/******************************************************************************/
// Checks if an IDP exists and returns true if it does, false otherwise
function checkIDP($IDP){
	
	global $IDProviders;
	
	if (isset($IDProviders[$IDP])){
		return true;
	} else {
		return false;
	} 
}

/******************************************************************************/
// Checks if an IDP exists and returns true if it exists and prints an error 
// if it doesnt
function checkIDPAndShowErrors($IDP){
	
	global $IDProviders;
	
	if (checkIDP($IDP)){
		return true;
	}
	
	// Otherwise show an error
	$message = sprintf(getLocalString('invalid_user_idp'), htmlentities($IDP))."</p><p>\n<code>";
	foreach ($IDProviders as $key => $value){
		if (isset($value['SSO'])){
			$message .= $key."<br>\n";
		}
	}
	$message .= "</code>\n";
	
	printError($message);
	exit;
}


/******************************************************************************/
// Validates the URL and returns it if it is valid or false otherwise 
function getSanitizedURL($url){
	
	$components = parse_url($url);
	
	if ($components){
		return $url;
	} else {
		return false;
	}
}

/******************************************************************************/
// Parses the hostname out of a string and returns it
function getHostNameFromURI($string){
	
	// Check if string is URN
	if (preg_match('/^urn:mace:/i', $string)){
		// Return last component of URN
		$components = explode(':', $string);
		return end($components);
	}
	
	// Apparently we are dealing with something like a URL
	if (preg_match('/([a-zA-Z0-9\-\.]+\.[a-zA-Z0-9\-\.]{2,6})/', $string, $matches)){
		return $matches[0];
	} else {
		return '';
	}
}

/******************************************************************************/
// Parses the domain out of a string and returns it
function getDomainNameFromURI($string){
	
	// Check if string is URN
	if (preg_match('/^urn:mace:/i', $string)){
		// Return last component of URN
		$components = explode(':', $string);
		return getTopLevelDomain(end($components));
	}
	
	// Apparently we are dealing with something like a URL
	if (preg_match('/[a-zA-Z0-9\-\.]+\.([a-zA-Z0-9\-\.]{2,6})/', $string, $matches)){
		return getTopLevelDomain($matches[0]);
	} else {
		return '';
	}
}

/******************************************************************************/
// Returns top level domain name from a DNS name
function getTopLevelDomain($string){
	$hostnameComponents = explode('.', $string);
	if (count($hostnameComponents) >= 2){
		return $hostnameComponents[count($hostnameComponents)-2].'.'.$hostnameComponents[count($hostnameComponents)-1];
	} else {
		return $string;
	}
}

/******************************************************************************/
// Parses the reverse dns lookup hostname out of a string and returns domain
function getDomainNameFromURIHint(){
	
	global $IDProviders;
	
	$clientHostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	if ($clientHostname == $_SERVER['REMOTE_ADDR']){
		return '-';
	}
	
	// Get domain name from client host name
	$clientDomainName = getDomainNameFromURI($clientHostname);
	if ($clientDomainName == ''){
		return '-';
	}
	
	// Return first matching IdP entityID that contains the client domain name
	foreach ($IDProviders as $key => $value){
		if (
			   preg_match('/^http.+'.$clientDomainName.'/', $key)
			|| preg_match('/^urn:.+'.$clientDomainName.'$/', $key)){ 
			return $key;
		}
	}
	
	// No matching entityID was found
	return '-';
}

/******************************************************************************/
// Get the user's language using the accepted language http header
function determineLanguage(){
	
	global $langStrings, $defaultLanguage;
	
	// Check if language is enforced by PATH-INFO argument
	if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])){
		foreach ($langStrings as $lang => $values){
			if (preg_match('#/'.$lang.'($|/)#',$_SERVER['PATH_INFO'])){
				return $lang;
			}
		}
	}
	
	// Check if there is a language GET argument
	if (isset($_GET['lang'])){
		$localeComponents = decomposeLocale($_GET['lang']);
		if (
		    $localeComponents !== false 
		    && isset($langStrings[$localeComponents[0]])
		    ){
			
			// Return language
			return $localeComponents[0];
		}
	}
	
	// Return default language if no headers are present otherwise
	if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
		return $defaultLanguage;
	}
	
	// Inspect Accept-Language header which looks like:
	// Accept-Language: en,de-ch;q=0.8,fr;q=0.7,fr-ch;q=0.5,en-us;q=0.3,de;q=0.2
	$languages = explode( ',', trim($_SERVER['HTTP_ACCEPT_LANGUAGE']));
	foreach ($languages as $language){
		$languageParts = explode(';', $language);
		
		// Only treat art before the prioritization
		$localeComponents = decomposeLocale($languageParts[0]);
		if (
		    $localeComponents !== false 
		    && isset($langStrings[$localeComponents[0]])
		    ){
			
			// Return language
			return $localeComponents[0];
		}
	}
	
	return $defaultLanguage;
}

/******************************************************************************/

// Splits up a  string (relazed) according to
// http://www.debian.org/doc/manuals/intro-i18n/ch-locale.en.html#s-localename
// and returns an array with the four components
function decomposeLocale($locale){
	
	// Locale name syntax:  language[_territory][.codeset][@modifier]
	if (!preg_match('/^([a-zA-Z]{2})([-_][a-zA-Z]{2})?(\.[^@]+)?(@.+)?$/', $locale, $matches)){
		return false;
	} else {
		// Remove matched string in first position
		array_shift($matches);
		
		return $matches;
	}
}

/******************************************************************************/
// Gets a string in a specific language. Fallback to default language and 
// to English.
function getLocalString($string, $encoding = ''){
	
	global $defaultLanguage, $langStrings, $language;
	
	$textString = '';
	if (isset($langStrings[$language][$string])){
		$textString = $langStrings[$language][$string];
	} elseif (isset($langStrings[$defaultLanguage][$string])){
		$textString = $langStrings[$defaultLanguage][$string];
	} else {
		$textString = $langStrings['en'][$string];
	}
	
	// Change encoding if necessary
	if ($encoding == 'js'){
		$textString = convertToJSString($textString);
	}
	
	return $textString;
}

/******************************************************************************/
// Converts string to a JavaScript format that can be used in JS alert
function convertToJSString($string){
	return addslashes(html_entity_decode($string, ENT_COMPAT, 'UTF-8'));
}

/******************************************************************************/
// Replaces all newlines with spaces and then trims the string to get one line
function trimToSingleLine($string){
	return trim(preg_replace("|\n|",' ',$string));
}

/******************************************************************************/
// Checks if entityID hostname of a valid IdP exists in path info
function getIdPPathInfoHint(){
	
	global $IDProviders;
	
	// Check if path info is available at all
	if (!isset($_SERVER['PATH_INFO']) || empty($_SERVER['PATH_INFO'])){
		return '-';
	}
	
	// Check for entityID hostnames of all available IdPs
	foreach ($IDProviders as $key => $value){
		// Only check actual IdPs
		if (
				isset($value['SSO']) 
				&& !empty($value['SSO'])
				&& $value['Type'] != 'wayf'
				&& isPartOfPathInfo(getHostNameFromURI($key))
				){
			return $key;
		}
	}
	
	// Check for entityID domain names of all available IdPs
	foreach ($IDProviders as $key => $value){
		// Only check actual IdPs
		if (
				isset($value['SSO']) 
				&& !empty($value['SSO'])
				&& $value['Type'] != 'wayf'
				&& isPartOfPathInfo(getDomainNameFromURI($key))
				){
			return $key;
		}
	}
	
	return '-';
}

/******************************************************************************/
// Joins localized names and keywords of an IdP to a single string
function composeOptionData($IdPValues){
	$data = '';
	foreach($IdPValues as $key => $value){
		if (is_array($value) && isset($value['Name'])){
			$data .= ' '.$value['Name'];
		} 
		
		if (is_array($value) && isset($value['Keywords'])) {
			$data .= ' '.$value['Keywords'];
		}
	}
	
	return $data;
}

/******************************************************************************/
// Parses the Kerbores realm out of the string and returns it
function getKerberosRealm($string){
	
	global $IDProviders;
	
	if ($string !='' ) {
		// Find a matching Kerberos realm
		foreach ($IDProviders as $key => $value){
			if ($value['Realm'] == $string) return $key;
		}
	}
	
	return '-';
}


/******************************************************************************/
// Determines the IdP according to the IP address if possible
function getIPAdressHint() {
	global $IDProviders;
	
	foreach($IDProviders as $name => $idp) {
		if (is_array($idp) && array_key_exists("IP", $idp)) {
			$clientIP = $_SERVER["REMOTE_ADDR"];
			
			foreach( $idp["IP"] as $network ) {
				if (isIPinCIDRBlock($network, $clientIP)) {
					return $name;
				}
			}
		}
	}
	return '-';
}

/******************************************************************************/
// Returns true if IP is in IPv4/IPv6 CIDR range
// and returns false otherwise
function isIPinCIDRBlock($cidr, $ip) {
	
	// Split CIDR notation
	list ($net, $mask) = preg_split ("|/|", $cidr);
	
	// Convert to binary string value of 1s and 0s
	$netAsBinary = convertIPtoBinaryForm($net);
	$ipAsBinary =  convertIPtoBinaryForm($ip);
	
	// Return false if netmask and ip are using different protocols
	if (strlen($netAsBinary) != strlen($ipAsBinary)){
		return false;
	}
	
	// Compare the first $mask bits
	for($i = 0; $i < $mask; $i++){
	
		// Return false if bits don't match
		if ($netAsBinary[$i] != $ipAsBinary[$i]){
			return false;
		}
	}
	
	// If we got here, ip matches net
	return true;
	
}

/******************************************************************************/
// Converts IP in human readable format to binary string
function convertIPtoBinaryForm($ip){
	
	//  Handle IPv4 IP
	if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false){
		return base_convert(ip2long($ip),10,2);
	}
	
	// Return false if IP is neither IPv4 nor a IPv6 IP
	if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false){
		return false;
	}
	
	// Convert IP to binary structure and return false if this fails
	if(($ipAsBinStructure = inet_pton($ip)) === false) {
		return false;
	}
	
	
	$numOfBytes = 16; 
	$ipAsBinaryString = '';
	
	// Convert IP to binary string
	while ($numOfBytes > 0){
		// Convert current byte to decimal number
		$currentByte = ord($ipAsBinStructure[$numOfBytes - 1]);
		
		// Convert currenty byte to string of 1 and 0
		$currentByteAsBinary = sprintf("%08b", $currentByte);
		
		// Prepend to rest of IP in binary string
		$ipAsBinaryString = $currentByteAsBinary.$ipAsBinaryString;
		
		// Decrease byte counter
		$numOfBytes--;
	}
	
	return $ipAsBinaryString;
}

/******************************************************************************/
// Returns URL without GET arguments
function getURLWithoutArguments($url){
	return preg_replace('/\?.*/', '', $url);
}

/******************************************************************************/
// Returns true if URL could be verified or if no check is necessary, false otherwise
function verifyReturnURL($entityID, $returnURL) {
	global $SProviders, $useACURLsForReturnParamCheck;
	
	// If SP has a <idpdisc:DiscoveryResponse>, check return param
	if (isset($SProviders[$entityID]['DSURL'])){
		$returnURLWithoutArguments = getURLWithoutArguments($returnURL);
		foreach($SProviders[$entityID]['DSURL'] as $DSURL){
			$DSURLWithoutArguments = getURLWithoutArguments($DSURL);
			if ($DSURLWithoutArguments == $returnURLWithoutArguments){
				return true;
			}
		}
		
		// DS URLs did not match the return URL
		return false;
	}
	
	// Return true if SP has no <idpdisc:DiscoveryResponse> 
	// and $useACURLsForReturnParamCheck is disabled (we don't check anything)
	if (!$useACURLsForReturnParamCheck){
		return true;
	}
	
	// $useACURLsForReturnParamCheck is enabled, so
	// check return param against host name of assertion consumer URLs
	
	// Check hostnames
	$returnURLHostName = getHostNameFromURI($returnURL);
	foreach($SProviders[$entityID]['ACURL'] as $ACURL){
		if (getHostNameFromURI($ACURL) == $returnURLHostName){
			return true;
		}
	}
	
	// We haven't found a matching assertion consumer URL, therefore we return false
	return false;
	
}

/******************************************************************************/
// Returns a reasonable value for returnIDParam
function getReturnIDParam() {
	
	if (isset($_GET['returnIDParam']) && !empty($_GET['returnIDParam'])){
		return $_GET['returnIDParam'];
	} else {
		return 'entityID';
	}
}

/******************************************************************************/
// Returns true if valid Shibboleth 1.x request or Directory Service request
function isValidShibRequest(){
	return (isValidShib1Request() || isValidDSRequest());
}

/******************************************************************************/
// Returns true if valid Shibboleth request
function isValidShib1Request(){
	if (isset($_GET['shire']) && isset($_GET['target'])){
		return true;
	} else {
		return false;
	}
}

/******************************************************************************/
// Returns true if request is a valid Directory Service request
function isValidDSRequest(){
	global $SProviders;
	
	// If entityID is not present, request is invalid
	if (!isset($_GET['entityID'])){
		return false;
	}
	
	// If entityID and return parameters are present, request is valid
	if (isset($_GET['return'])){
		return true;
	}
	
	// If no return parameter and no Discovery Service endpoint is available 
	// for SP, request is invalid
	if (!isset($SProviders[$_GET['entityID']]['DSURL'])){
		return false;
	}
	
	if (count($SProviders[$_GET['entityID']]['DSURL']) < 1){
		return false;
	}
	
	// EntityID is available and there is at least one DiscoveryService 
	// endpoint defined. Therefore, the request is valid
	return true;
}

/******************************************************************************/
// Sets the Location header to redirect the user's web browser
function redirectTo($url){
	header('Location: '.$url);
}

/******************************************************************************/
// Sets the Location that is used for redirect the web browser back to the SP
function redirectToSP($url, $IdP){
	if (preg_match('/\?/', $url) > 0){
		redirectTo($url.'&'.getReturnIDParam().'='.urlencode($IdP));
	} else {
		redirectTo($url.'?'.getReturnIDParam().'='.urlencode($IdP));
	}
}
/******************************************************************************/
// Logs all events where users were redirected to their IdP or back to an SP
// The log then can be used to approximately detect how many users were served
// by the SWITCHwayf
function logAccessEntry($protocol, $type, $sp, $idp, $return){
	global $WAYFLogFile, $useLogging;
	
	// Return if logging deactivated
	if (!$useLogging){
		return;
	}
	
	// Create log file if it does not exist yet
	if (!file_exists($WAYFLogFile) && !touch($WAYFLogFile)){
		// File does not exist and cannot be written to
		logFatalErrorAndExit('WAYF log file '.$WAYFLogFile.' does not exist and could not be created.');
	}
	
	// Ensure that the file exists and is writable
	if (!is_writable($WAYFLogFile)) {
		logFatalErrorAndExit('Current file permission do not allow WAYF to write to its log file '.$WAYFLogFile.'.');
	}
	
	// Compose log entry
	$entry = date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.$protocol.' '.$type.' '.$idp.' '.$return.' '.$sp."\n";
	
	// Open file in append mode
	if (!$handle = fopen($WAYFLogFile, 'a')) {
		logFatalErrorAndExit('Could not open file '.$WAYFLogFile.' for appending log entries.');
	}
	
	// Try getting the lock
	while (!flock($handle, LOCK_EX)){
		usleep(rand(10, 100));
	}
	
	// Write entry
	fwrite($handle, $entry);
	
	// Release the lock
	flock($handle, LOCK_UN);
	
	// Close file handle
	fclose($handle);
	
}

/******************************************************************************/
// Init connection to system logger
function initLogger(){
	global $instanceIdentifier;
	
	openlog($instanceIdentifier, LOG_NDELAY, LOG_USER);
}

/******************************************************************************/
// Logs an info message
function logInfo($infoMsg){
	global $developmentMode;
	
	initLogger();
	
	syslog(LOG_INFO, $infoMsg);
	
	if ($developmentMode && isRunViaCLI()){
		echo $infoMsg;
	}
}

/******************************************************************************/
// Logs an warnimg message
function logWarning($warnMsg){
	global $developmentMode;
	
	initLogger();
	
	syslog(LOG_WARNING, $warnMsg);
	
	if ($developmentMode && isRunViaCLI()){
		echo $warnMsg;
	}
}

/******************************************************************************/
// Logs an error message
function logError($errorMsg){
	global $developmentMode;
	
	initLogger();
	
	syslog(LOG_ERR, $errorMsg);
	
	if ($developmentMode){
		echo $errorMsg;
	}
}

/******************************************************************************/
// Logs an fatal error message
function logFatalErrorAndExit($errorMsg){
	logError($errorMsg);
	exit;
}

/******************************************************************************/
// Returns true if PATH info indicates a request of type $type
function isRequestType($type){
	// Make sure the type is checked at end of path info
	return isPartOfPathInfo($type.'$');
}

/******************************************************************************/
// Checks for substrings in Path Info and returns true if match was found
function isPartOfPathInfo($needle){
	if (
		isset($_SERVER['PATH_INFO']) 
		&& !empty($_SERVER['PATH_INFO'])
		&& preg_match('|/'.$needle.'|', $_SERVER['PATH_INFO'])){
		
		return true;
	} else {
		return false;
	}
}

/******************************************************************************/
// Converts to the unified datastructure that the Shibboleth DS will be using
function convertToShibDSStructure($IDProviders){
	
	$ShibDSIDProviders = array();
	
	foreach ($IDProviders as $key => $value){
		
		// Skip unknown and category entries
		if(
			!isset($value['Type']) 
			|| $value['Type'] == 'category'
			|| $value['Type'] == 'wayf'
			){
			continue;
		}
		
		// Init and fill IdP data
		$identityProvider = array();
		$identityProvider['entityID'] = $key;
		$identityProvider['DisplayNames'][] = array('lang' => 'en', 'value' => $value['Name']);
		
		// Add DisplayNames in other languages
		foreach($value as $lang => $name){
			if(
				   $lang == 'Name'
				|| $lang == 'SSO'
				|| $lang == 'Realm'
				|| $lang == 'Type'
				|| $lang == 'IP'
				
			){
				continue;
			}
			
			if (isset($name['Name'])){
				$identityProvider['DisplayNames'][] = array('lang' => $lang, 'value' => $name['Name']);
			}
		}
		
		// Add data to ShibDSIDProviders
		$ShibDSIDProviders[] = $identityProvider;
	}
	
	return $ShibDSIDProviders;
	
}

/******************************************************************************/
// Sorts the IDProviders array
function sortIdentityProviders(&$IDProviders){
	$orderedCategories = Array();
	
	// Create array with categories and IdPs in categories
	$unknownCategory = array();
	foreach ($IDProviders as $entityId => $IDProvider){
		// Add categories
		if ($IDProvider['Type'] == 'category'){
			$orderedCategories[$entityId]['data'] = $IDProvider;
		}
	}
	
	// Add category 'unknown' if not present
	if (!isset($orderedCategories['unknown'])){
		$orderedCategories['unknown']['data'] = array (
			'Name' => 'Unknown',
			'Type' => 'category',
		);
	}
	
	foreach ($IDProviders as $entityId => $IDProvider){
	
		// Skip categories
		if ($IDProvider['Type'] == 'category'){
			continue;
		}
		
		// Skip incomplete descriptions
		if (!is_array($IDProvider) || !isset($IDProvider['Name'])){
			continue;
		}
		
		// Sanitize category
		if (!isset($IDProvider['Type'])){
			$IDProvider['Type'] = 'unknown';
		}
		
		// Add IdP
		$orderedCategories[$IDProvider['Type']]['IdPs'][$entityId] = $IDProvider;
	}
	
	// Relocate all IdPs for which no category with a name was defined
	$toremoveCategories = array();
	foreach ($orderedCategories as $category => $object){
		if (!isset($object['data'])){
			foreach ($object['IdPs'] as $entityId => $IDProvider){
				$unknownCategory[$entityId] = $IDProvider;
			}
			$toremoveCategories[] = $category;
		}
	}
	
	// Remove categories without descriptions
	foreach ($toremoveCategories as $category){
		unset($orderedCategories[$category]);
	}
	
	// Recompose $IDProviders
	$IDProviders = Array();
	foreach ($orderedCategories as $category => $object){
		
		// Skip category if it contains no IdPs
		if (!isset($object['IdPs']) || count($object['IdPs']) < 1 ){
			continue;
		}
		
		// Add category
		$IDProviders[$category] = $object['data'];
		
		// Sort IdPs in category
		uasort($object['IdPs'], 'sortUsingTypeIndexAndName');
		
		// Add IdPs
		foreach ($object['IdPs'] as $entityId => $IDProvider){
			$IDProviders[$entityId] = $IDProvider;
		}
	}
}

/******************************************************************************/
// Sorts two entries according to their Type, Index and (local) Name
function sortUsingTypeIndexAndName($a, $b){
	global $language;
	
	if ($a['Type'] != $b['Type']){
		return strcasecmp($a['Type'], $b['Type']);
	} elseif (isset($a['Index']) && isset($b['Index']) && $a['Index'] != $b['Index']){
		return strcasecmp($a['Index'], $b['Index']);
	} else {
		// Sort using locale names
		$localNameB = (isset($a[$language]['Name'])) ? $a[$language]['Name'] : $a['Name'];
		$localNameA = (isset($b[$language]['Name'])) ? $b[$language]['Name'] : $b['Name'];
		return strcasecmp($localNameB, $localNameA);
	}
}


/******************************************************************************/
// Returns true if the referer of the current request is matching an assertion
// consumer or discovery service URL of a Service Provider
function isRequestRefererMatchingSPHost(){
	
	global $SProviders;
	
	// If referer is not available return false
	if (!isset($_SERVER["HTTP_REFERER"]) || $_SERVER["HTTP_REFERER"] == ''){
		return false;
	}
	
	if (!isset($SProviders) || !is_array($SProviders)){
		return false;
	}
	
	$refererHostname = getHostNameFromURI($_SERVER["HTTP_REFERER"]);
	foreach ($SProviders as $key => $SProvider){
		// Check referer against entityID
		$spHostname = getHostNameFromURI($key);
		if ($refererHostname == $spHostname){
			return true;
		}
		
		// Check referer against Discovery Response URL(DSURL)
		if (isset($SProvider['DSURL'])) {
			foreach ($SProvider['DSURL'] as $url){
				$spHostname = getHostNameFromURI($url);
				if ($refererHostname == $spHostname){
					return true;
				}
			}
		}
		
		// Check referer against Assertion Consumer Service URL(ACURL)
		if (isset($SProvider['ACURL'])) {
			foreach ($SProvider['ACURL'] as $url){
				$spHostname = getHostNameFromURI($url);
				if ($refererHostname == $spHostname){
					return true;
				}
			}
		}
	}
	
	return false;
}

/******************************************************************************/
// Is this script run in CLI mode
function isRunViaCLI(){
	return !isset($_SERVER['REMOTE_ADDR']);
}

/******************************************************************************/
// Is this script run in CLI mode
function isRunViaInclude(){
	return basename($_SERVER['SCRIPT_NAME']) != 'readMetadata.php';
}

?>
