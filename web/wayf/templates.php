<?php // Copyright (c) 2016, SWITCH

/*
******************************************************************************
This file contains the some functions that render HTML code.
******************************************************************************
*/

if(!isset($_SERVER['REMOTE_ADDR']) || basename($_SERVER['SCRIPT_NAME']) == 'templates.php'){
	exit('No direct script access allowed');
}

/*------------------------------------------------*/
// Functions containing HTML code
/*------------------------------------------------*/

function printHeader(){

	global $langStrings, $language, $imageURL, $javascriptURL, $cssURL, $logoURL;
	global $useImprovedDropDownList, $disableRemoteLogos, $organizationLogoURL;
	global $federationURL, $organizationURL, $faqURL, $helpURL, $privacyURL;
	
	// Check if custom header template exists
	if(file_exists('custom-header.php')){
		include('custom-header.php');
	} else {
		// Use default code
		include('default-header.php');
	}
}


/******************************************************************************/
// Presents the user the drop-down list with available IDPs
function printWAYF(){
	
	global $selectedIDP, $language, $IDProviders, $SProviders, $redirectCookieName, $imageURL, $redirectStateCookieName, $showPermanentSetting;
	
	if (!isset($showPermanentSetting)){
		$showPermanentSetting = false;
	}
	
	$promptMessage =  getLocalString('make_selection');
	$serviceName = '';
	$entityID = '';
	
	// Check if entityID is available
	if (isset($_GET['entityID'])){
		$entityID = $_GET['entityID'];
	} else if (isset($_GET['providerId'])){
		$entityID = $_GET['providerId'];
	}
	
	// Set service name if entityID has a description
	if (!empty($entityID) && isset($SProviders[$entityID]) ){
		$SP = $SProviders[$entityID];
		$serviceName = $SP['Name'];
		if (isset($SP[$language]['Name'])){
			$serviceName = $SP[$language]['Name'];
		}
	}
	
	// Reset service name if it is the same as the entityID
	if ($serviceName == $entityID){
		$serviceName = '';
	}
	
	// Fallback to hostname of return URL if no service name was available
	if (empty($serviceName)){
		if (isset($_GET['return'])){
			$serviceName = getHostNameFromURI($_GET['return']);
		} else if (isset($_GET['shire'])){
			$serviceName = getHostNameFromURI($_GET['shire']);
		} else {
			$serviceName = $entityID;
		}
		$serviceName = '<span class="hostName">'.$serviceName.'</span>';
	} else {
		$serviceName = '<span class="serviceName">'.$serviceName.'</span>';
	}
	
	// Compose strings
	$promptMessage =  sprintf(getLocalString('access_host'), $serviceName);
	$actionURL = $_SERVER['SCRIPT_NAME'].'?'.htmlentities($_SERVER['QUERY_STRING']);
	$defaultSelected = ($selectedIDP == '-') ? 'selected="selected"' : '';
	$rememberSelectionChecked = (isset($_COOKIE[$redirectStateCookieName])) ? 'checked="checked"' : '' ;
	
	// Check if custom header template exists
	if(file_exists('custom-body.php')){
		include('custom-body.php');
	} else {
		// Use default code
		include('default-body.php');
	}
}

/******************************************************************************/
// Presents the user a form to set a permanent cookie for their default IDP
function printSettings(){
	
	global $selectedIDP, $language, $IDProviders, $redirectCookieName;
	
	$actionURL = $_SERVER['SCRIPT_NAME'].'?'.htmlentities($_SERVER['QUERY_STRING']);
	$defaultSelected = ($selectedIDP == '-') ? 'selected="selected"' : '';
	
	// Check if custom header template exists
	if(file_exists('custom-settings.php')){
		include('custom-settings.php');
	} else {
		// Use default code
		include('default-settings.php');
	} 
}

/******************************************************************************/
// Prints the HTML drop down list including categories etc
function printDropDownList($IDProviders, $selectedIDP = ''){
	global $language;
	
	$previouslyUsedIdPsHTML = getPreviouslyUsedIdPsHTML();
	echo $previouslyUsedIdPsHTML;
	
	
	$counter = 0;
	$optgroup = '';
	foreach ($IDProviders as $key => $values){
		
		// Get IdP Name
		$IdPName = (isset($values[$language]['Name'])) ? $values[$language]['Name'] : $IdPName = $values['Name'];
		
		// Figure out if entry is valid or a category
		if (!isset($values['SSO'])){
			
			// Check if entry is a category
			if (isset($values['Type']) && $values['Type'] == 'category'){
				if (!empty($optgroup)){
					echo "\n".'</optgroup>';
				}
				
				// Skip adding a new category if first category is 'unknown'
				// and it is the (probably) only category
				if ($key == 'unknown' && empty($optgroup) && $previouslyUsedIdPsHTML == ''){
					continue;
				}
				
				echo "\n".'<optgroup label="'.$IdPName.'">';
				$optgroup = $key;
				
			}
			continue;
		}
		
		echo "\n\t".printOptionElement($IDProviders, $key, $selectedIDP);
		
		$counter++;
	}
	
	// Add last optgroup if that was used
	if (!empty($optgroup)){
		echo "\n".'</optgroup>';
	}
}

/******************************************************************************/
// Prints option group of previously used organisations
function getPreviouslyUsedIdPsHTML(){
	global $IDProviders, $IDPArray, $selectedIDP, $showNumOfPreviouslyUsedIdPs;
	
	if (!isset($IDPArray) || count($IDPArray) < 1){
		return '';
	}
	
	$content = '';
	$counter = (isset($showNumOfPreviouslyUsedIdPs)) ? $showNumOfPreviouslyUsedIdPs : 3;
	
	for($n = count($IDPArray) - 1; $n >= 0; $n--){
		
		if ($counter <= 0){
			break;
		}
		
		$optionHTML = printOptionElement($IDProviders, $IDPArray[$n], $selectedIDP);
		
		if (empty($optionHTML)){
			continue;
		}
		
		$content .= "\t".$optionHTML."\n";
		
		$counter--;
	}
	
	// Return if no previously used IdPs exist
	if (empty($content)){
		return '';
	}
	
	// Print previously used IdPs
	$categoryName = getLocalString('last_used');
	$content = "\n".'<optgroup label="'.$categoryName.'">'."\n".$content;
	$content .= '</optgroup>';
	
	return $content;
}

/******************************************************************************/
// Print a single option element of the drop down list
function printOptionElement($IDProviders, $key, $selectedIDP){
	global $language;
	
	// Return if IdP does not exit
	if (!isset($IDProviders[$key])){
		return '';
	}
	
	// Get values
	$values = $IDProviders[$key];
	
	// Get IdP Name
	$IdPName = (isset($values[$language]['Name'])) ? $values[$language]['Name'] : $IdPName = $values['Name'];
	
	// Set selected attribute
	$selected = ($selectedIDP == $key) ? ' selected="selected"' : $selected = '';
	
	// Add additional information as data attribute to the entry
	$data = getDomainNameFromURI($key);
	$data .= composeOptionData($values);
	
	// Add logo (which is assumed to be 16x16px) to extension string
	$logo =  (isset($values['Logo'])) ? 'logo="'.$values['Logo']['URL']. '"' : '' ;
	
	return '<option value="'.$key.'"'.$selected.' data="'.htmlspecialchars($data).'" '.$logo.'>'.$IdPName.'</option>';
}

/******************************************************************************/
// Prints the notice that tells the users their permanent IDP with an option
// to clear the permanent cookie.
function printNotice(){
	
	global $redirectCookieName, $IDProviders;
	
	$actionURL = $_SERVER['SCRIPT_NAME'].'?'.htmlentities($_SERVER['QUERY_STRING']);
	
	$hiddenUserIdPInput = '';
	$permanentUserIdP = '';
	$permanentUserIdPName = '';
	$permanentUserIdPLogo = '';
	
	
	if (
			isset($_POST['user_idp']) 
			&& checkIDPAndShowErrors($_POST['user_idp'])
		){
		$permanentUserIdP = $_POST['user_idp'];
	} elseif (
			isset($_COOKIE[$redirectCookieName]) 
			&& checkIDPAndShowErrors($_COOKIE[$redirectCookieName])
		){
		$permanentUserIdP = $_COOKIE[$redirectCookieName];
	}
	
	if ($permanentUserIdP != ''){
		$hiddenUserIdPInput = '<input type="hidden" name="user_idp" value="'.$permanentUserIdP.'">';
		$permanentUserIdPName = $IDProviders[$permanentUserIdP]['Name'];
		$permanentUserIdPLogo = $IDProviders[$permanentUserIdP]['Logo']['URL'];
	}
	
	// Check if footer template exists
	if(file_exists('custom-notice.php')){
		include('custom-notice.php');
	} else {
		// Use default code
		include('default-notice.php');
	}
}

/******************************************************************************/
// Prints end of HTML page
function printFooter(){
	
	// Check if footer template exists
	if(file_exists('custom-footer.php')){
		include('custom-footer.php');
	} else {
		// Use default code
		include('default-footer.php');
	}
}

/******************************************************************************/
// Prints an error message
function printError($message){
	
	global $langStrings, $language, $supportContactEmail;
	
	// Show Header
	printHeader();
	
	// Check if error template exists
	if(file_exists('custom-error.php')){
		include('custom-error.php');
	} else {
		// Use default code
		include('default-error.php');
	}
	
	// Show footer
	printFooter();
}

/******************************************************************************/
// Prints the JavaScript that renders the Embedded WAYF
function printEmbeddedWAYFScript(){

	global $langStrings, $language, $imageURL, $javascriptURL, $cssURL, $logoURL, $smallLogoURL, $federationURL;
	global $selectedIDP, $IDProviders, $SAMLDomainCookieName, $redirectCookieName, $redirectStateCookieName, $federationName;
	
	// Set values that are used in the java script
	$loginWithString = getLocalString('login_with');
	$makeSelectionString = getLocalString('make_selection', 'js');
	$loggedInString =  getLocalString('logged_in');
	$configurationScriptUrl = preg_replace('/embedded-wayf.js/', 'embedded-wayf.js/snippet.html', 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
	$utcTime = time();
	$checkedBool = (isset($_COOKIE[$redirectStateCookieName]) && !empty($_COOKIE[$redirectStateCookieName])) ? 'checked="checked"' : '' ;
	$rememberSelectionText = addslashes(getLocalString('remember_selection'));
	$loginString = addslashes(getLocalString('login'));
	$selectIdPString = addslashes(getLocalString('select_idp'));
	$otherFederationString = addslashes(getLocalString('other_federation'));
	$mostUsedIdPsString = addslashes(getLocalString('most_used'));
	$lastUsedIdPsString = addslashes(getLocalString('last_used'));
	$redirectCookie = (isset($_COOKIE[$redirectCookieName]) && !empty($_COOKIE[$redirectCookieName])) ?  $_COOKIE[$redirectCookieName] : '';
	
	// Generate list of Identity Providers
	$JSONIdPArray = array();
	$JSONCategoryArray = array();
	foreach ($IDProviders as $key => $IDProvider){
		
		// Get IdP Name
		if (isset($IDProvider[$language]['Name'])){
			$IdPName = addslashes($IDProvider[$language]['Name']);
		} else {
			$IdPName = addslashes($IDProvider['Name']);
		}
		
		// Set selected attribute
		$selected = ($selectedIDP == $key) ? ' selected:"true",' : '' ;
		$IdPType = isset($IDProviders[$key]['Type']) ? $IDProviders[$key]['Type'] : '';
		
		// SSO
		if (isset($IDProvider['SSO'])){
			$IdPSSO = $IDProvider['SSO'];
		} else {
			$IdPSSO = '';
		}
		
		// Logo URL
		if (isset($IDProvider['Logo']['URL'])){
			$IdPLogoURL = $IDProvider['Logo']['URL'];
		} else {
			$IdPLogoURL = '';
		}
		
		// Add other information to find IdP
		$IdPData = getDomainNameFromURI($key);
		$IdPData .= composeOptionData($IDProvider);
		$IdPData = addslashes( $IdPData);
		
		// Skip non-IdP entries
		if ($IdPType == ''){
			continue;
		}
		
		// Fill category and IdP buckets
		if ($IdPType == 'category'){
			$JSONCategoryArray[] = <<<ENTRY

"{$key}":{
	type:"{$IdPType}",
	name:"{$IdPName}"
}

ENTRY;
		} else {
			$JSONIdPArray[] = <<<ENTRY

"{$key}":{ {$selected}
	type:"{$IdPType}",
	name:"{$IdPName}",
	logoURL:"{$IdPLogoURL}",
	data:"{$IdPData}"
}
ENTRY;
		}
	}
	$JSONIdPList = join(',', $JSONIdPArray);
	$JSONCategoryList = join(',', $JSONCategoryArray);
	
	// Locales for javascript
	$searchText = getLocalString('search_idp', 'js');
	$noIdPFoundText =  getLocalString('no_idp_found', 'js');
	$noIdPAvailableText = getLocalString('no_idp_available', 'js');
	
	// Process script
	require_once('js/embeddedWAYF.js');
}

/******************************************************************************/
// Print sample configuration script used for Embedded WAYF
function printEmbeddedConfigurationScript(){
	global $IDProviders;
	
	$types = array();
	foreach ($IDProviders as $IDProvider){
		if (isset($IDProvider['Type']) && $IDProvider['Type'] != 'category'){
			$types[$IDProvider['Type']] = $IDProvider['Type'];
		}
	}
	
	$host = $_SERVER['SERVER_NAME'];
	$path = $_SERVER['SCRIPT_NAME'];
	$types = '"'.implode('","',$types).'"';
	
	header('Content-type: text/plain;charset="utf-8"');
	
	if(file_exists('custom-embedded-wayf.php')){
		include('custom-embedded-wayf.php');
	} else {
		// Use default code
		include('default-embedded-wayf.php');
	}
}

/******************************************************************************/
// Print sample configuration script used for Embedded WAYF
function printCSS($file){
	
	global $imageURL;
	
	if ($file != 'ImprovedDropDown.css'){
		$file= 'styles.css';
	}
	
	$defaultCSSFile =  'css/default-'.$file;
	$cssContent = file_get_contents($defaultCSSFile);

	// Read custom CSS if available
	if (file_exists('css/custom-'.$file)){
		$customCSSFile =  'css/custom-'.$file;
		$cssContent .= file_get_contents($customCSSFile);
	}
	
	// Read CSS and substitute content
	$cssContent = preg_replace('/{?\$imageURL}?/',$imageURL, $cssContent);
	
	echo $cssContent;
}
?>
