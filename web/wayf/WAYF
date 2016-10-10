<?php // Copyright (c) 2016, SWITCH

/*
******************************************************************************
SWITCHwayf
Version: 1.20.2
Contact:  aai@switch.ch
Web site: https://www.switch.ch/aai/support/tools/wayf/
******************************************************************************
*/

/*------------------------------------------------*/
// Load general configuration and template file
/*------------------------------------------------*/

require_once('config.php');
require_once('languages.php');
require_once('functions.php');
require_once('templates.php');

// Set default config options
initConfigOptions();

// Read custom locales
if (file_exists('custom-languages.php')){
	require_once('custom-languages.php');
}

/*------------------------------------------------*/
// Turn on PHP error reporting
/*------------------------------------------------*/
if ($developmentMode){
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 'On');
	ini_set('log_erros', 'Off');
} else {
	error_reporting(0);
}

/*------------------------------------------------*/
// Read IDP configuration file
/*------------------------------------------------*/

// Determine language
$language = determineLanguage();

// Check if IdP files differ
// If not load file
if ($IDPConfigFile == $backupIDPConfigFile){
	require_once($IDPConfigFile);
// If they do, check config file
} elseif (checkConfig($IDPConfigFile, $backupIDPConfigFile)){
	require_once($IDPConfigFile);
// Use backup file if something went wrong 
} else {
	require_once($backupIDPConfigFile);
}

// Read metadata file if configuration option is set
if($useSAML2Metadata && function_exists('xml_parser_create')){
	require('readMetadata.php');
}

// Set default type
foreach ($IDProviders as $key => $values){
	if (!isset($IDProviders[$key]['Type'])){
		$IDProviders[$key]['Type'] = 'unknown';
	}
}

/*------------------------------------------------*/
// Back-wards compatibility logic
/*------------------------------------------------*/

// Set P3P headers just in case they were not set in Apache already
header('P3P: CP="NOI CUR DEVa OUR IND COM NAV PRE"');

// This is for back-wards compatibility with very old versions of the WAYF
if (isset($_GET['getArguments']) && isset($_GET['origin']) && isset($_GET['redirect'])){
	redirectTo($_SERVER['PHP_SELF'].'/redirect/'.$_GET['origin'].'?'.$_GET['getArguments']);
	exit;
}

/*------------------------------------------------*/
// Input validation
/*------------------------------------------------*/

if(isValidDSRequest()){
	// Check that return URL in DS request is a valid URL
	$returnURL = getSanitizedURL($_GET['return']);
	if(!$returnURL){
		// Show error
		$message = sprintf(getLocalString('invalid_return_url'), htmlentities($_GET['return']));
		logWarning('Invalid return URL: '.$_GET['return']);
		printError($message);
		exit;
	}
	
	if ($useSAML2Metadata && $enableDSReturnParamCheck){
		// Check SP
		if(!isset($SProviders[$_GET['entityID']])){
			// Show error
			$message = sprintf(getLocalString('unknown_sp'), htmlentities($_GET['entityID']));
			logWarning('Unknown SP: '.$_GET['entityID']);
			printError($message);
			exit;
		}
		
		// Check return URL in DS request if checks are enabled
		$returnURLOK = verifyReturnURL($_GET['entityID'], $returnURL);
		if(!$returnURLOK){
			// Show error
			$message = sprintf(getLocalString('unverified_return_url'), htmlentities($returnURL), htmlentities($_GET['entityID']));
			logWarning('Unverified return URL: '.$returnURL.' for SP: '.$_GET['entityID']);
			printError($message);
			exit;
		}
	}
	
	
}

/*------------------------------------------------*/
// Set and delete cookies
/*------------------------------------------------*/

// Delete all cookies
if (isRequestType('deleteSettings')){
	$cookies = array($redirectCookieName, $redirectStateCookieName, $SAMLDomainCookieName, $SPCookieName);
	foreach ($cookies as $cookie){ 
		if (isset($_COOKIE[$cookie])){
			setcookie($cookie,'',time()-86400, '/', $commonDomain, $cookieSecurity, $cookieSecurity);
		}
	}
	
	if (isset($_GET['return'])){
		redirectTo($_GET['return']);
	} else {
		redirectTo($_SERVER['SCRIPT_NAME']);
	}
	exit;
}


// Delete permanent cookie
if (isset($_POST['clear_user_idp'])){
	setcookie ($redirectCookieName, '', time() - 3600, '/', $commonDomain, $cookieSecurity, $cookieSecurity);
	redirectTo('?'.$_SERVER['QUERY_STRING']);
	exit;
}

// Get previously accessed IdPs
if (isset($_COOKIE[$SAMLDomainCookieName])){
	$IDPArray = getIdPArrayFromValue($_COOKIE[$SAMLDomainCookieName]);
} else {
	$IDPArray = array();
}

// Get previously accessed SPs
if (isset($_COOKIE[$SPCookieName])){
	$SPArray = getIdPArrayFromValue($_COOKIE[$SPCookieName]);
} else {
	$SPArray = array();
}

// Set Cookie to remember the selection
if (isset($_POST['user_idp']) && checkIDPAndShowErrors($_POST['user_idp'])){
	$IDPArray = appendValueToIdPArray($_POST['user_idp'], $IDPArray);
	setcookie ($SAMLDomainCookieName, getValueFromIdPArray($IDPArray) , time() + ($cookieValidity*24*3600), '/', $commonDomain, $cookieSecurity, $cookieSecurity);
}

// Set cookie for most recently used Service Provider
if (isset($_GET['entityID'])){
	$SPArray = appendValueToIdPArray($_GET['entityID'], array());
	setcookie ($SPCookieName, getValueFromIdPArray($SPArray), time() + (10*24*3600), '/', $commonDomain, $cookieSecurity, $cookieSecurity);
} else if (isset($_GET['providerId'])){
	$SPArray = appendValueToIdPArray($_GET['providerId'], array());
	setcookie ($SPCookieName, getValueFromIdPArray($SPArray), time() + (10*24*3600), '/', $commonDomain, $cookieSecurity, $cookieSecurity);
}


// Set the permanent or session cookie
if (isset($_POST['permanent']) 
	&& isset($_POST['user_idp']) 
	&& checkIDPAndShowErrors($_POST['user_idp'])){
	
	// Set permanent cookie 
	if (is_numeric($_POST['permanent'])){
		setcookie ($redirectCookieName, $_POST['user_idp'], time() + ($_POST['permanent']*24*3600), '/', $commonDomain, $cookieSecurity, $cookieSecurity);
	} else {
		setcookie ($redirectCookieName, $_POST['user_idp'], time() + ($cookieValidity*24*3600), '/', $commonDomain, $cookieSecurity, $cookieSecurity);
	}
} elseif (
	isset($_POST['user_idp']) 
	&& checkIDPAndShowErrors($_POST['user_idp'])
	){
	
	if (isset($_POST['session'])){
		// Set redirection cookie and redirection state cookie
		setcookie ($redirectCookieName, $_POST['user_idp'], null, '/', $commonDomain, $cookieSecurity, $cookieSecurity);
		setcookie ($redirectStateCookieName, 'checked', time() + ($cookieValidity*24*3600), '/', $commonDomain, $cookieSecurity, $cookieSecurity);
	} else {
		setcookie ($redirectStateCookieName, 'checked', time() - 3600, '/', $commonDomain, $cookieSecurity, $cookieSecurity);
	}
}

/*------------------------------------------------*/
// Redirecting user
/*------------------------------------------------*/

// IDP determined by redirect cookie
if (	
		isValidShibRequest()
		&& isset($_COOKIE[$redirectCookieName]) 
		&& checkIDP($_COOKIE[$redirectCookieName])
	){
	
	$cookieIdP = $_COOKIE[$redirectCookieName];
	
	// Handle cascaded WAYF
	if (isset($IDProviders[$cookieIdP]['Type']) && $IDProviders[$cookieIdP]['Type'] == 'wayf'){
		
		// Send user to cascaded WAYF with same request
		redirectTo($IDProviders[$cookieIdP]['SSO'].'?'.$_SERVER['QUERY_STRING']);
		
	} elseif (isValidDSRequest()){
		redirectToSP($_GET['return'], $cookieIdP);
		
		// Create log entry
		logAccessEntry('DS', 'Cookie', $_GET['entityID'], $cookieIdP, $_GET['return']);
		
	} else {
		redirectTo($IDProviders[$cookieIdP]['SSO'].'?'.$_SERVER['QUERY_STRING']);
		
		// Create log entry
		logAccessEntry('WAYF', 'Cookie', (isset($_GET['providerId'])) ? $_GET['providerId'] : '-', $cookieIdP, $_GET['shire']);
		
	}
		
	exit;
} 

// Redirect using Kerberos
// Check if $REMOTE_USER has been set by mod_auth_kerb
if ($useKerberos && isset($_SERVER['REMOTE_USER'])) {
	$kerberosPrincipal = $_SERVER['REMOTE_USER'];
	// Bingo - we have a winner!
	$kerberosRealm = substr($user, 1 + strlen($kerberosPrincipal) - strlen(strrchr($kerberosPrincipal, "@")));
	
	if ($kerberosIDP = getKerberosRealm($kerberosRealm) && checkIDP($kerberosIDP)){
		
		// Handle cascaded WAYF
		if (isset($IDProviders[$kerberosIDP]['Type']) && $IDProviders[$kerberosIDP]['Type'] == 'wayf'){
			
			// Send user to cascaded WAYF with same request
			redirectTo($IDProviders[$kerberosIDP]['SSO'].'?'.$_SERVER['QUERY_STRING']);
			
		} elseif (isValidDSRequest()){
			redirectToSP($_GET['return'], $kerberosIDP);
			
			// Create log entry
			logAccessEntry('DS', 'Kerberos', $_GET['entityID'], $kerberosIDP, $_GET['return']);
		} else {
			redirectTo($IDProviders[$kerberosIDP]['SSO'].'?'.$_SERVER['QUERY_STRING']);
			
			// Create log entry
			logAccessEntry('WAYF', 'Kerberos', (isset($_GET['providerId'])) ? $_GET['providerId'] : '-', $kerberosIDP, $_GET['shire']);
		}
		exit;
	}
}

// Trigger Kerberos authentication
if ($useKerberos && !isset($kerberosRealm)) {
	// Check the headers for an Authorisation header.
	$headers = getallheaders();
	// If its' there...
	foreach ($headers as $name => $content) {
			if ($name == "Authorization") {
			// ... then the user agent is attempting Negotiate, so we
			// redirect to the soft link (that points back to this script)
			// which is	 protected by mod_auth_kerb.
			$url = $kerberosRedirectURL."?".$_SERVER['QUERY_STRING'];
			redirectTo($url);
			exit();
		}
	}
	
	// Send the User Agent a Negotiate header
	// This will provoke a User Agent that supports Negotiate into requesting this 
	// script a second time, including an 'Authorize' header. We catch this header
	// in the code above.
	header('WWW-Authenticate: Negotiate');
	// If the User Agent doesn't support Negotiate, we continue as usual.
}

// For backwards compatiblity
if (	
		isset($_GET['shire']) 
		&& isset($_GET['target'])
		&& isset($_GET['origin'])
		&& checkIDPAndShowErrors($_GET['origin'])
	){
	redirectTo($IDProviders[$_GET['origin']]['SSO'].'?'.$_SERVER['QUERY_STRING']);
	
	// Create log entry
	logAccessEntry('WAYF', 'Old-Request', (isset($_GET['providerId'])) ? $_GET['providerId'] : '-', $_GET['origin'], $_GET['shire']);
	exit;
} 

// Redirect using resource hint
$hintedPathIDP = getIdPPathInfoHint();
if ($hintedPathIDP != '-'){
	// Handle cascaded WAYF
	if (isset($IDProviders[$hintedPathIDP]['Type']) && $IDProviders[$hintedPathIDP]['Type'] == 'wayf'){
		
		// Send user to cascaded WAYF with same request
		redirectTo($IDProviders[$hintedPathIDP]['SSO'].'?'.$_SERVER['QUERY_STRING']);
		exit;
		
	} elseif (isPartOfPathInfo('redirect') ){
		// Set redirect cookie for this session
		setcookie ($redirectCookieName, $hintedPathIDP, null, '/', $commonDomain, $cookieSecurity, $cookieSecurity);
		
		// Determine if DS or WAYF request
		if (isValidDSRequest()){
			redirectToSP($_GET['return'], $hintedPathIDP);
			
			// Create log entry
			logAccessEntry('DS', 'Path', $_GET['entityID'], $hintedPathIDP, $_GET['return']);
			
		} else {
			redirectTo($IDProviders[$hintedPathIDP]['SSO'].'?'.$_SERVER['QUERY_STRING']);
			
			// Create log entry
			logAccessEntry('WAYF', 'Path', (isset($_GET['providerId'])) ? $_GET['providerId'] : '-', $hintedPathIDP, $_GET['shire']);
		}
		
		exit;
	}
}

// Redirect using user selection
if (
	isset($_POST['user_idp']) 
	&& checkIDPAndShowErrors($_POST['user_idp'])
	&& isValidShibRequest()
	&& !isset($_POST['permanent'])
	){
	
	$selectedIDP = $_POST['user_idp'];
	
	// Handle cascaded WAYF
	if (isset($IDProviders[$selectedIDP]['Type']) && $IDProviders[$selectedIDP]['Type'] == 'wayf'){
		
		// Send user to cascaded WAYF with same request
		redirectTo($IDProviders[$selectedIDP]['SSO'].'?'.$_SERVER['QUERY_STRING']);
		
	} else if (isValidDSRequest()){
		redirectToSP($_GET['return'], $selectedIDP);
		
		// Create log entry
		if (isset($_POST['request_type']) && $_POST['request_type'] == 'embedded'){
			$dsType = 'Embedded-DS';
		} else {
			$dsType = 'DS';
		}
		logAccessEntry($dsType, 'Request', $_GET['entityID'], $selectedIDP, $_GET['return']);
		
	} else {
		redirectTo($IDProviders[$selectedIDP]['SSO'].'?'.$_SERVER['QUERY_STRING']);
		
		// Create log entry
		if (isset($_POST['request_type']) && $_POST['request_type'] == 'embedded'){
			$dsType = 'Embedded-WAYF';
		} else {
			$dsType = 'WAYF';
		}
		logAccessEntry($dsType, 'Request', (isset($_GET['providerId'])) ? $_GET['providerId'] : '-', $selectedIDP, $_GET['shire']);
	}
	exit;
}

/*------------------------------------------------*/
// Gather data to preselect user's IdP
/*------------------------------------------------*/

// Initialize selected IdP
$selectedIDP = '-';

// Cookie hint
$hintedCookieIdP = '-';
if (count($IDPArray) > 0){
	// Make sure one of these IdP exists
	foreach ($IDPArray as $previouslyUsedIDP){
		if (isset($IDProviders[$previouslyUsedIDP])){
			$hintedCookieIdP = $previouslyUsedIDP;
		}
	}
} 

// IP address range hint
$hintedIPIDP = '-';
$hintedIPIDP = getIPAdressHint();

// Reverse DNS lookup hint
$hintedDomainIDP = '-';
if ($useReverseDNSLookup){
	$hintedDomainIDP = getDomainNameFromURIHint();
}

/*------------------------------------------------*/
// Determine preselected IdP using gathered data
/*------------------------------------------------*/

// Prioritise selected IDP in this sequence
// - Previous used IdP
// - Path info hint
// - IP address range hint 
// - Reverse DNS Lookup hint
// - No Preselection

if ($hintedCookieIdP != '-'){
	$selectedIDP = $hintedCookieIdP;
} elseif ($hintedPathIDP != '-'){
	$selectedIDP = $hintedPathIDP;
} elseif ($hintedIPIDP != '-'){
	$selectedIDP = $hintedIPIDP;
} elseif ($hintedDomainIDP != '-'){
	$selectedIDP = $hintedDomainIDP;
} else {
	$selectedIDP = '-';
}

/*------------------------------------------------*/
// Sort Identity Providers
/*------------------------------------------------*/

if ($useSAML2Metadata){
	// Only automatically sort if list of Identity Provider is parsed
	// from metadata instead of being manualy managed
	sortIdentityProviders($IDProviders);
}

/*------------------------------------------------*/
// Draw WAYF
/*------------------------------------------------*/

// Coming from an SP with proper GET arguments
if ( 
	isValidShibRequest() 
	&& (!isset($_POST['user_idp']) || $_POST['user_idp'] == '-')
	) {
	
	// Return directly to IdP if isPassive is set
	if (
		isValidDSRequest() 
		&& isset($_GET['isPassive'])
		&& $_GET['isPassive'] == 'true'
		){
		
		// Only return user with returnIDParam to SP if IdP could be guessed
		if ($selectedIDP == '-' || $selectedIDP == ''){
			redirectTo($_GET['return']);
			
			// Create log entry
			logAccessEntry('DS', 'Passive', $_GET['entityID'], '-', $_GET['return']);
			
			
		} else {
			redirectToSP($_GET['return'], $selectedIDP);
			
			// Create log entry
			logAccessEntry('DS', 'Passive', $_GET['entityID'], $selectedIDP, $_GET['return']);
		}
		exit;
	}
	
	// Show selection
	
	// Show Header
	printHeader();
	
	// Show drop down list
	printWAYF();
	
	// Show footer
	printFooter();
	exit;
	
} elseif(
		(!isset($_GET['shire']) && isset($_GET['target'])) 
		|| (isset($_GET['shire']) && !isset($_GET['target']))
){
	
	// Show error
	$invalidstring = urldecode($_SERVER['QUERY_STRING']);
	$invalidstring = preg_replace('/&/',"&\n",$invalidstring);
	if ($invalidstring == '')
		$invalidstring = getLocalString('no_arguments');
	$message = getLocalString('arguments_missing') . '<pre>';
	$message .= '<code>'.htmlentities($invalidstring).'</code></pre></p>';
	$message .= '<p>'. getLocalString('valid_request_description');
	logWarning('Invalid GET arguments for Shibboleth discovery requests: '.$invalidstring);
	printError($message);
	exit;
} elseif(
		(!isset($_GET['entityID']) && isset($_GET['return'])) 
		|| (isset($_GET['entityID']) && !isset($_GET['return']))
){
	
	// Show error
	$invalidstring = urldecode($_SERVER['QUERY_STRING']);
	$invalidstring = preg_replace('/&/',"&\n",$invalidstring);
	if ($invalidstring == '')
		$invalidstring = getLocalString('no_arguments');
	$message = getLocalString('arguments_missing') . '</p><pre>';
	$message .= '<code>'.htmlentities($invalidstring).'</code></pre>';
	$message .= '<p>'. getLocalString('valid_saml2_request_description');
	logWarning('Invalid GET arguments for SAML discovery requests: '.$invalidstring);
	printError($message);
	exit;
	
} elseif(isRequestType('styles.css')){
	
	header('Content-Type: text/css');
	
	printCSS('styles.css');
	
	exit;

} elseif(isRequestType('ImprovedDropDown.css')){

	header('Content-Type: text/css');
	
	printCSS('ImprovedDropDown.css');
	
	exit;

} elseif(isRequestType('snippet.html')){
	
	// Check if this feature is activated at all
	if (!$useEmbeddedWAYF){
		echo '// The embedded WAYF feature is deactivated in the configuration';
		exit;
	}
	
	// Return the HTML snippet for including the Embedded WAYF
	printEmbeddedConfigurationScript();
	
	exit;

} elseif(isRequestType('snippet.txt')){
	
	header('Content-Type: text/plain');
	
	// Check if this feature is activated at all
	if (!$useEmbeddedWAYF){
		echo '// The embedded WAYF feature is deactivated in the configuration';
		exit;
	}
	
	// Return the HTML snippet for including the Embedded WAYF
	printEmbeddedConfigurationScript();
	
	exit;

} elseif(isRequestType('embedded-wayf.js')){
	
	// Check if this feature is activated at all
	if (!$useEmbeddedWAYF){
		echo '// The embedded WAYF feature is deactivated in the configuration';
		exit;
	}
	
	// Set JavaScript content type
	header('Content-type: text/javascript;charset="utf-8"');
	
	// If the data protection feature is enabled, don't preselect the IdP
	if ($useEmbeddedWAYFPrivacyProtection){
		$selectedIDP = '-';
	}
	
	// If the referer check is enabled but fails, don't preselect the IdP
	if (
		   !$useEmbeddedWAYFPrivacyProtection
		&&  $useEmbeddedWAYFRefererForPrivacyProtection
		&& !isRequestRefererMatchingSPHost()
		){
		$selectedIDP = '-';
	}
	
	// Generate JavaScript code
	printEmbeddedWAYFScript();
	
	exit;
	
} elseif (isRequestType('ShibbolethDS-IDProviders.json')){
	
	// Return $IdProviders as JSON data structure
	if (!$developmentMode){
		header('Content-Type: application/json');
	}
	
	// Add guessed Identity Provider
	if ($exportPreselectedIdP){
		$IDProviders['preselectedIDP'] = $selectedIDP;
	}
	
	$ShibDSIDProviders = convertToShibDSStructure($IDProviders);
	
	// Add list of IdPs
	echo json_encode($ShibDSIDProviders);
	
	exit;
	
} elseif (isRequestType('ShibbolethDS-IDProviders.js')){
	
	// Set JavaScript content type
	header('Content-type: text/javascript;charset="utf-8"');
	
	// Add guessed Identity Provider
	if ($exportPreselectedIdP){
		$IDProviders['preselectedIDP'] = $selectedIDP;
	}
	
	$ShibDSIDProviders = convertToShibDSStructure($IDProviders);
	
	// Add list of IdPs
	echo 'var myJSONObject = '.json_encode($ShibDSIDProviders).';';
	
	exit;
	
} elseif (isRequestType('IDProviders.json')){
	
	// Return $IdProviders as JSON data structure
	if (!$developmentMode){
		header('Content-Type: application/json');
	}
	
	// Add guessed Identity Provider
	if ($exportPreselectedIdP){
		$IDProviders['preselectedIDP'] = $selectedIDP;
	}
	
	// Add list of IdPs
	echo json_encode($IDProviders);
	
	exit;
	
} elseif (isRequestType('IDProviders.txt')){
	
	// Return $IdProviders as human readable data
	header('Content-Type: text/plain');
	
	// Add guessed Identity Provider
	if ($exportPreselectedIdP){
		$IDProviders['preselectedIDP'] = $selectedIDP;
	}
	
	echo 'IDProviders = ';
	
	// Return list of IdP array
	print_r($IDProviders);
	
	exit;
	
} elseif (isRequestType('IDProviders.php')){
	
	// Return $IdProviders as PHP code
	header('Content-Type: text/plain');
	
	// Add guessed Identity Provider
	if ($exportPreselectedIdP){
		$IDProviders['preselectedIDP'] = $selectedIDP;
	}
	
	echo '$IDProviders = ';
	
	// Return list of IdP array
	var_export($IDProviders);
	
	exit;
	
} elseif (
			(isset($_POST['user_idp']) && checkIDPAndShowErrors($_POST['user_idp']))
			|| (
				   isset($_COOKIE[$redirectCookieName]) 
				&& checkIDP($_COOKIE[$redirectCookieName])
				)
		){
	
	// Show confirmatin notice
	// Show Header
	printHeader();
	
	// Show drop down list
	printNotice();
	
	// Show footer
	printFooter();
	
	exit;
} else {
	
	// Show Header
	printHeader();
	
	// Show drop down list
	printSettings();
	
	// Show footer
	printFooter();
	exit;
} 

?>
