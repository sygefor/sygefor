<?php // Copyright (c) 2016, SWITCH

// This file is used to dynamically create the list of IdPs and SP to be 
// displayed for the WAYF/DS service based on the federation metadata.
// Configuration parameters are specified in config.php.
//
// The list of Identity Providers can also be updated by running the script
// readMetadata.php periodically as web server user, e.g. with a cron entry like:
// 5 * * * * /usr/bin/php readMetadata.php > /dev/null


// Set dummy server name if run in CLI
if (!isset($_SERVER['SERVER_NAME'])){
	$_SERVER['SERVER_NAME'] = 'localhost';
}

require_once('functions.php');
require_once('config.php');

// Make sure this script is not accessed directly
if(isRunViaCLI()){
	// Run in cli mode.
	
	// Set default config options
	initConfigOptions();
	
	// Load Identity Providers
	require($IDPConfigFile);
	
	// Check that $IDProviders exists
	if (!isset($IDProviders) or !is_array($IDProviders)){
		$IDProviders = array();
	}
	
	if (
		   !file_exists($metadataFile) 
		|| trim(@file_get_contents($metadataFile)) == '') {
	  exit ("Exiting: File ".$metadataFile." is empty or does not exist\n");
	}
	
	// Get an exclusive lock to generate our parsed IdP and SP files.
	if (($lockFp = fopen($metadataLockFile, 'a+')) === false) {
		$errorMsg = 'Could not open lock file '.$metadataLockFile;
		die($errorMsg);
	}
	if (flock($lockFp, LOCK_EX) === false) { 
		$errorMsg = 'Could not lock file '.$metadataLockFile;
		die($errorMsg);
	}
	
	echo 'Parsing metadata file '.$metadataFile."\n";
	list($metadataIDProviders, $metadataSProviders) = parseMetadata($metadataFile, $defaultLanguage);
	
	// If $metadataIDProviders is not FALSE, dump results in $metadataIDPFile.
	if(is_array($metadataIDProviders)){ 
		
		echo 'Dumping parsed Identity Providers to file '.$metadataIDPFile."\n";
		dumpFile($metadataIDPFile, $metadataIDProviders, 'metadataIDProviders');
	}
	// If $metadataSProviders is not FALSE, dump results in $metadataSPFile.
	if(is_array($metadataSProviders)){ 
		
		echo 'Dumping parsed Service Providers to file '.$metadataSPFile."\n";
		dumpFile($metadataSPFile, $metadataSProviders, 'metadataSProviders');
	}

	// Release the lock, and close.
	flock($lockFp, LOCK_UN);
	fclose($lockFp);
	
	
	// If $metadataIDProviders is not FALSE, update $IDProviders and print the Identity Providers lists.
	if(is_array($metadataIDProviders)){ 

		echo 'Merging parsed Identity Providers with data from file '.$IDPConfigFile."\n";
		$IDProviders = mergeInfo($IDProviders, $metadataIDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries);
		
		echo "Printing parsed Identity Providers:\n";
		print_r($metadataIDProviders);
		
		echo "Printing effective Identity Providers:\n";
		print_r($IDProviders);
	}
	
	// If $metadataSProviders is not FALSE, update $SProviders and print the list.
	if(is_array($metadataSProviders)){ 
		
		// For now copy the array by reference
		$SProviders = &$metadataSProviders;
		
		echo "Printing parsed Service Providers:\n";
		print_r($metadataSProviders);
	}
	
	
} elseif (isRunViaInclude()) {
	// Run as included file
	
	// Open the metadata lock file.
	if (($lockFp = fopen($metadataLockFile, 'a+')) === false) {
		$errorMsg = 'Could not open lock file '.$metadataLockFile;
		logError($errorMsg);
	}
	
	// Check that $IDProviders exists
	if (!isset($IDProviders) or !is_array($IDProviders)){
		$IDProviders = array();
	}
	
	// Run as included file
	if(!file_exists($metadataIDPFile) or filemtime($metadataFile) > filemtime($metadataIDPFile)){
	
		// Get an exclusive lock to regenerate the parsed files.
		if ($lockFp !== false) {
			if (flock($lockFp, LOCK_EX) === false) { 
				$errorMsg = 'Could not get exclusive lock on '.$metadataLockFile;
				logError($errorMsg);
			}
		}
	}
	
	// Now that we have the lock, check again
	if(
		(!file_exists($metadataIDPFile) or filemtime($metadataFile) > filemtime($metadataIDPFile)) 
		and regenerateMetadata($metadataFile, $defaultLanguage)
	){
		
		// Now merge IDPs from metadata and static file
		$IDProviders = mergeInfo($IDProviders, $metadataIDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries);
		
		// For now copy the array by reference
		$SProviders = &$metadataSProviders;
		
	} elseif (file_exists($metadataIDPFile)){
		
		// Get a shared lock to read the IdP and SP files
		// generated from the metadata file.
		if ($lockFp !== false) {
			
			// Release the lock in case we had it for some 
			// reason and still ended up here
			flock($lockFp, LOCK_UN);
			
			if (flock($lockFp, LOCK_SH) === false) { 
				$errorMsg = 'Could not lock file '.$metadataLockFile;
				logError($errorMsg);
			}
		}
		
		// Read SP and IDP files generated with metadata
		require($metadataIDPFile);
		require($metadataSPFile);
	
		// Release the lock.
		if ($lockFp !== false) {
			flock($lockFp, LOCK_UN);
		}
		
		// Now merge IDPs from metadata and static file
		$IDProviders = mergeInfo($IDProviders, $metadataIDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries);
		
		// For now copy the array by reference
		$SProviders = &$metadataSProviders;
	}
	
	// Close the metadata lock file.
	if ($lockFp !== false) {
		fclose($lockFp);
	}
	
} else {
	exit('No direct script access allowed');
}

closelog();

/*****************************************************************************/
// Function parseMetadata, parses metadata file and returns Array($IdPs, SPs)  or
// Array(false, false) if error occurs while parsing metadata file
function parseMetadata($metadataFile, $defaultLanguage){
	global $supportHideFromDiscoveryEntityCategory;
	
	if(!file_exists($metadataFile)){
		$errorMsg = 'File '.$metadataFile." does not exist"; 
		if (isRunViaCLI()){
			echo $errorMsg."\n";
		} else {
			logError($errorMsg);
		}
		return Array(false, false);
	}

	if(!is_readable($metadataFile)){
		$errorMsg = 'File '.$metadataFile." cannot be read due to insufficient permissions"; 
		if (isRunViaCLI()){
			echo $errorMsg."\n";
		} else {
			logError($errorMsg);
		}
		return Array(false, false);
	}
	
	$CurrentXMLReaderNode = new XMLReader();
	if(!$CurrentXMLReaderNode->open($metadataFile, null, LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING | 1)){
		$errorMsg = 'Could not parse metadata file '.$metadataFile; 
		if (isRunViaCLI()){
			echo $errorMsg."\n";
		} else {
			logError($errorMsg);
		}
		return Array(false, false);
	}
	
	// Go to first element and check it is named 'EntitiesDescriptor'
	// If not it's probably not a valid SAML metadata file
	$CurrentXMLReaderNode->read();
	if ($CurrentXMLReaderNode->localName  !== 'EntitiesDescriptor') {
		$errorMsg = 'Metadata file '.$metadataFile.' does not include a root node EntitiesDescriptor'; 
		if (isRunViaCLI()){
			echo $errorMsg."\n";
		} else {
			logError($errorMsg);
		}
		return Array(false, false);
	}
	
	// Init variables
	$hiddenIdPs = 0;
	$metadataIDProviders = array();
	$metadataSProviders = array();
	
	// Process individual EntityDescriptors
	while( $CurrentXMLReaderNode->read() ) {
		if($CurrentXMLReaderNode->nodeType == XMLReader::ELEMENT && $CurrentXMLReaderNode->localName  === 'EntityDescriptor') {
			$entityID = $CurrentXMLReaderNode->getAttribute('entityID');
			$EntityDescriptorXML = $CurrentXMLReaderNode->readOuterXML();
			$EntityDescriptorDOM = new DOMDocument();
			$EntityDescriptorDOM->loadXML($EntityDescriptorXML);
			
			// Check role descriptors
			foreach($EntityDescriptorDOM->documentElement->childNodes as $RoleDescriptor) {
				$nodeName = $RoleDescriptor->localName;
				switch($nodeName){
					case 'IDPSSODescriptor':
						$IDP = processIDPRoleDescriptor($RoleDescriptor);
						if ($IDP){
							$metadataIDProviders[$entityID] = $IDP;
						} else {
							$hiddenIdPs++;
						}
						break;
					case 'SPSSODescriptor':
						$SP = processSPRoleDescriptor($RoleDescriptor);
						if ($SP){
							$metadataSProviders[$entityID] = $SP;
						} else {
							$errorMsg = "Failed to load SP with entityID $entityID from metadata file $metadataFile";
							if (isRunViaCLI()){
								echo $errorMsg."\n";
							} else {
								logWarning($errorMsg);
							}
						}
						break;
					default:
				}
			}
		}
	}
	
	// Output result
	$infoMsg = "Successfully parsed metadata file ".$metadataFile. " ";
	$infoMsg .= "(".count($metadataIDProviders)." IdPs, ";
	$infoMsg .= " ".count($metadataSProviders)." SPs, ";
	$infoMsg .=  ($hiddenIdPs > 0) ? $hiddenIdPs." IdPs are hidden)" : "no hidden IdPs)" ;
	
	if (isRunViaCLI()){
		echo $infoMsg."\n";
	} else {
		logInfo($infoMsg);
	}
	
	
	return Array($metadataIDProviders, $metadataSProviders);
}


/******************************************************************************/
// Load SAML metadata file, parse it and update 
// IDProvider.metadata.php and SProvider.metadata.php files
function regenerateMetadata($metadataFile, $defaultLanguage) {
	global $metadataIDPFile, $metadataSPFile, $IDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries, $lockFp;
	
	// Regenerate $metadataIDPFile.
	list($metadataIDProviders, $metadataSProviders) = parseMetadata($metadataFile, $defaultLanguage);
	
	if($metadataIDProviders == false) {
		return false;
	}
	
	// If $metadataIDProviders is not an array (parse error in metadata),
	// $IDProviders from $IDPConfigFile will be used.
	if(is_array($metadataIDProviders)){
		dumpFile($metadataIDPFile, $metadataIDProviders, 'metadataIDProviders');
		$IDProviders = mergeInfo($IDProviders, $metadataIDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries);
	}
	
	if(is_array($metadataSProviders)){
		dumpFile($metadataSPFile, $metadataSProviders, 'metadataSProviders');
		require($metadataSPFile);
	}
	
	// Release the lock.
	if ($lockFp !== false) {
		flock($lockFp, LOCK_UN);
	}
	
}

/******************************************************************************/
// Processes an IDPRoleDescriptor XML node and returns an IDP entry or false if 
// something went wrong
function processIDPRoleDescriptor($IDPRoleDescriptorNode){
	global $defaultLanguage, $supportHideFromDiscoveryEntityCategory;
	
	$IDP = Array();
	$Profiles = Array();
	
	// Skip Idp if it has the Hide-From-Discovery entity 
	// category attribute
	if (!isset($supportHideFromDiscoveryEntityCategory) || $supportHideFromDiscoveryEntityCategory){
		if (hasHideFromDiscoveryEntityCategory($IDPRoleDescriptorNode)){
			return false;
		}
	}
	
	// Get SSO URL
	$SSOServices = $IDPRoleDescriptorNode->getElementsByTagNameNS( 'urn:oasis:names:tc:SAML:2.0:metadata', 'SingleSignOnService' );
	foreach( $SSOServices as $SSOService ){
	  $Profiles[$SSOService->getAttribute('Binding')] = $SSOService->getAttribute('Location');
	}
	
	// Set SAML1 SSO URL
	if (isset($Profiles['urn:mace:shibboleth:1.0:profiles:AuthnRequest'])) {
		$IDP['SSO'] = $Profiles['urn:mace:shibboleth:1.0:profiles:AuthnRequest'];
	} else if (isset($Profiles['urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'])) {
		$IDP['SSO'] = $Profiles['urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'];
	} else {
		$IDP['SSO'] = 'https://no.saml1.or.saml2.sso.url.defined.com/error';
	}
	
	// First get MDUI name
	$MDUIDisplayNames = getMDUIDisplayNames($IDPRoleDescriptorNode);
	if (count($MDUIDisplayNames)){
		$IDP['Name'] = current($MDUIDisplayNames);
	}
	foreach ($MDUIDisplayNames as $lang => $value){
		$IDP[$lang]['Name'] = $value;
	}
	
	// Then try organization names 
	if (empty($IDP['Name'])){
		$OrgnizationNames = getOrganizationNames($IDPRoleDescriptorNode);
		$IDP['Name'] = current($OrgnizationNames);
		
		foreach ($OrgnizationNames as $lang => $value){
			$IDP[$lang]['Name'] = $value;
		}
	} 
	
	// As last resort, use entityID
	if (empty($IDP['Name'])){
		$IDP['Name'] = $IDPRoleDescriptorNode->parentNode->getAttribute('entityID');
	}
	
	// Set default name
	if (isset($IDP[$defaultLanguage])){
		$IDP['Name'] = $IDP[$defaultLanguage]['Name'];
	} elseif (isset($IDP['en'])){
		$IDP['Name'] = $IDP['en']['Name'];
	}
	
	// Get supported protocols
	$protocols = $IDPRoleDescriptorNode->getAttribute('protocolSupportEnumeration');
	$IDP['Protocols'] = $protocols;
	
	// Get keywords
	$MDUIKeywords = getMDUIKeywords($IDPRoleDescriptorNode);
	foreach ($MDUIKeywords as $lang => $keywords){
		$IDP[$lang]['Keywords'] = $keywords;
	}
	
	// Get Logos
	$MDUILogos = getMDUILogos($IDPRoleDescriptorNode);
	foreach ($MDUILogos as $Logo){
		// Skip non-favicon logos
		if ($Logo['Height'] != 16 || $Logo['Width'] != 16 ){
			continue;
		}
		
		// Strip height and width
		unset($Logo['Height']);
		unset($Logo['Width']);
		
		if ($Logo['Lang'] == ''){
			unset($Logo['Lang']);
			$IDP['Logo'] = $Logo;
		} else {
			$lang = $Logo['Lang'];
			unset($Logo['Lang']);
			$IDP[$lang]['Logo'] = $Logo;
		}
	}
	
	// Get AttributeValue 
	$SAMLAttributeValues = getSAMLAttributeValues($IDPRoleDescriptorNode);
	if ($SAMLAttributeValues){
		$IDP['AttributeValue'] = $SAMLAttributeValues;
	}
	
	// Get IPHints 
	$MDUIIPHints = getMDUIIPHints($IDPRoleDescriptorNode);
	if ($MDUIIPHints){
		$IDP['IPHint'] = $MDUIIPHints;
	}
	
	// Get DomainHints 
	$MDUIDomainHints = getMDUIDomainHints($IDPRoleDescriptorNode);
	if ($MDUIDomainHints){
		$IDP['DomainHint'] = $MDUIDomainHints;
	}
	
	// Get GeolocationHints 
	$MDUIGeolocationHints = getMDUIGeolocationHints($IDPRoleDescriptorNode);
	if ($MDUIGeolocationHints){
		$IDP['GeolocationHint'] = $MDUIGeolocationHints;
	}
	
	return $IDP;
}

/******************************************************************************/
// Processes an SPRoleDescriptor XML node and returns an SP entry or false if 
// something went wrong
function processSPRoleDescriptor($SPRoleDescriptorNode){
	global $defaultLanguage;

	$SP = Array();
	
	// Get <idpdisc:DiscoveryResponse> extensions
	$DResponses = $SPRoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol', 'DiscoveryResponse');
	foreach( $DResponses as $DResponse ){
		if ($DResponse->getAttribute('Binding') == 'urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol'){
			$SP['DSURL'][] =  $DResponse->getAttribute('Location');
		}
	}
	
	// First get MDUI name
	$MDUIDisplayNames = getMDUIDisplayNames($SPRoleDescriptorNode);
	if (count($MDUIDisplayNames)){
		$SP['Name'] = current($MDUIDisplayNames);
	}
	foreach ($MDUIDisplayNames as $lang => $value){
		$SP[$lang]['Name'] = $value;
	}
	
	// Then try attribute consuming service
	if (empty($SP['Name'])){
		$ConsumingServiceNames = getAttributeConsumingServiceNames($SPRoleDescriptorNode);
		$SP['Name'] = current($ConsumingServiceNames);
		
		foreach ($ConsumingServiceNames as $lang => $value){
			$SP[$lang]['Name'] = $value;
		}
	} 
	
	// As last resort, use entityID
	if (empty($SP['Name'])){
		$SP['Name'] = $SPRoleDescriptorNode->parentNode->getAttribute('entityID');
	}
	
	// Set default name
	if (isset($SP[$defaultLanguage])){
		$SP['Name'] = $SP[$defaultLanguage]['Name'];
	} elseif (isset($SP['en'])){
		$SP['Name'] = $SP['en']['Name'];
	}
	
	// Get Assertion Consumer Services and store their hostnames
	$ACServices = $SPRoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'AssertionConsumerService');
	foreach( $ACServices as $ACService ){
		$SP['ACURL'][] =  $ACService->getAttribute('Location');
	}
	
	// Get supported protocols
	$protocols = $SPRoleDescriptorNode->getAttribute('protocolSupportEnumeration');
	$SP['Protocols'] = $protocols;
	
	// Get keywords
	$MDUIKeywords = getMDUIKeywords($SPRoleDescriptorNode);
	foreach ($MDUIKeywords as $lang => $keywords){
		$SP[$lang]['Keywords'] = $keywords;
	}
	
	return $SP;
}

/******************************************************************************/
// Dump variable to a file 
function dumpFile($dumpFile, $providers, $variableName){
	 
	if(($fp = fopen($dumpFile, 'w')) !== false){
		fwrite($fp, "<?php\n\n");
		fwrite($fp, "// This file was automatically generated by readMetadata.php\n");
		fwrite($fp, "// Don't edit!\n\n");
		
		fwrite($fp, '$'.$variableName.' = ');
		fwrite($fp, var_export($providers,true));
		
		fwrite($fp, "\n?>");
			
		fclose($fp);
	} else {
		$errorMsg = 'Could not open file '.$dumpFile.' for writting';
		if (isRunViaCLI()){
			echo $errorMsg."\n";
		} else {
			logInfo($errorMsg);
		}
	}
}


/******************************************************************************/
// Function mergeInfo is used to create the effective $IDProviders array.
// For each IDP found in the metadata, merge the values from IDProvider.conf.php.
// If an IDP is found in IDProvider.conf as well as in metadata, use metadata  
// information if $SAML2MetaOverLocalConf is true or else use IDProvider.conf data
function mergeInfo($IDProviders, $metadataIDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries){

	// If $includeLocalConfEntries parameter is set to true, mergeInfo() will also consider IDPs
	// not listed in metadataIDProviders but defined in IDProviders file
	// This is required if you need to add local exceptions over the federation metadata
	$allIDPS = $metadataIDProviders;
	$mergedArray = Array();
	if ($includeLocalConfEntries) {
		$allIDPS = array_merge($metadataIDProviders, $IDProviders);
	}
	
	foreach ($allIDPS as $allIDPsKey => $allIDPsEntry){
		if(isset($IDProviders[$allIDPsKey])){
			// Entry exists also in local IDProviders.conf.php
			if (isset($metadataIDProviders[$allIDPsKey]) && is_array($metadataIDProviders[$allIDPsKey])) {
				
				// Remove IdP if there is a removal rule in local IDProviders.conf.php 
				if (!is_array($IDProviders[$allIDPsKey])){
					unset($metadataIDProviders[$allIDPsKey]);
					continue;
				}
				
				// Entry exists in both IDProviders sources and is an array
				if($SAML2MetaOverLocalConf){
					// Metadata entry overwrite local conf
					$mergedArray[$allIDPsKey] = array_merge($IDProviders[$allIDPsKey], $metadataIDProviders[$allIDPsKey]);
				} else {
					// Local conf overwrites metada entry
					$mergedArray[$allIDPsKey] = array_merge($metadataIDProviders[$allIDPsKey], $IDProviders[$allIDPsKey]);
				}
			} else {
					// Entry only exists in local IDProviders file
					$mergedArray[$allIDPsKey] = $IDProviders[$allIDPsKey];
			}
		} else {
			// Entry doesnt exist in in local IDProviders.conf.php
			$mergedArray[$allIDPsKey] = $metadataIDProviders[$allIDPsKey];
		}
	}
	
	return $mergedArray;
}

/******************************************************************************/
// Get MD Display Names from RoleDescriptor
function getMDUIDisplayNames($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIDisplayNames = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'DisplayName');
	foreach( $MDUIDisplayNames as $MDUIDisplayName ){
		$lang = $MDUIDisplayName->getAttributeNodeNS('http://www.w3.org/XML/1998/namespace', 'lang')->nodeValue;
		$Entity[$lang] = trimToSingleLine($MDUIDisplayName->nodeValue);
	}
	
	return $Entity;
}

/******************************************************************************/
// Get MD Keywords from RoleDescriptor
function getMDUIKeywords($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIKeywords = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'Keywords');
	foreach( $MDUIKeywords as $MDUIKeywordEntry ){
		$lang = $MDUIKeywordEntry->getAttributeNodeNS('http://www.w3.org/XML/1998/namespace', 'lang')->nodeValue;
		$Entity[$lang] = trimToSingleLine($MDUIKeywordEntry->nodeValue);
	}
	
	return $Entity;
}

/******************************************************************************/
// Get MD Logos from RoleDescriptor. Prefer the favicon logos
function getMDUILogos($RoleDescriptorNode){
	
	$Logos = Array();
	$MDUILogos = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'Logo');
	foreach( $MDUILogos as $MDUILogoEntry ){
		$Logo = Array();
		$Logo['URL'] = trimToSingleLine($MDUILogoEntry->nodeValue);
		$Logo['Height'] = ($MDUILogoEntry->getAttribute('height') != '') ? trimToSingleLine($MDUILogoEntry->getAttribute('height')) : '16';
		$Logo['Width'] = ($MDUILogoEntry->getAttribute('width') != '') ? trimToSingleLine($MDUILogoEntry->getAttribute('width')) : '16';
		$Logo['Lang'] = ($MDUILogoEntry->getAttribute('lang') != '') ? trimToSingleLine($MDUILogoEntry->getAttribute('lang')) : '';
		$Logos[] = $Logo;
	}
	
	return $Logos;
}


/******************************************************************************/
// Get MD Attribute Value(kind) from RoleDescriptor
function getSAMLAttributeValues($RoleDescriptorNode){
	
	$Entity = Array();
	
	$SAMLAttributeValues = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:assertion', 'AttributeValue');
	foreach( $SAMLAttributeValues as $SAMLAttributeValuesEntry ){
		$Entity[] = trimToSingleLine($SAMLAttributeValuesEntry->nodeValue);
	}
	
	return $Entity;
}


/******************************************************************************/
// Get MD IP Address Hints from RoleDescriptor
function getMDUIIPHints($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIIPHints = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'IPHint');
	foreach( $MDUIIPHints as $MDUIIPHintEntry ){
		if (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,2}$/", trimToSingleLine($MDUIIPHintEntry->nodeValue), $splitIP)){
			$Entity[] = trimToSingleLine($splitIP[0]);
		} elseif (preg_match("/^.*\:.*\/[0-9]{1,2}$/", trimToSingleLine($MDUIIPHintEntry->nodeValue), $splitIP)){ 
			$Entity[] = trimToSingleLine($splitIP[0]);
		}
	}
	
	return $Entity;
}

/******************************************************************************/
// Get MD Domain Hints from RoleDescriptor
function getMDUIDomainHints($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIDomainHints = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'DomainHint');
	foreach( $MDUIDomainHints as $MDUIDomainHintEntry ){
		$Entity[] = trimToSingleLine($MDUIDomainHintEntry->nodeValue);
	}
	
	return $Entity;
}

/******************************************************************************/
// Get MD Geolocation Hints from RoleDescriptor
function getMDUIGeolocationHints($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIGeolocationHints = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'GeolocationHint');
	foreach( $MDUIGeolocationHints as $MDUIGeolocationHintEntry ){
		if (preg_match("/^geo:([0-9]+\.{0,1}[0-9]*,[0-9]+\.{0,1}[0-9]*)$/", trimToSingleLine($MDUIGeolocationHintEntry->nodeValue), $splitGeo)){
			$Entity[] = trimToSingleLine($splitGeo[1]);
		}
	}
	
	return $Entity;
}

/******************************************************************************/
// Get Organization Names from RoleDescriptor
function getOrganizationNames($RoleDescriptorNode){
	
	$Entity = Array();
	
	$Orgnization = $RoleDescriptorNode->parentNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'Organization' )->item(0);
	if ($Orgnization){
		$DisplayNames = $Orgnization->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'OrganizationDisplayName');
		foreach ($DisplayNames as $DisplayName){
			$lang = $DisplayName->getAttributeNodeNS('http://www.w3.org/XML/1998/namespace', 'lang')->nodeValue;
			$Entity[$lang] = trimToSingleLine($DisplayName->nodeValue);
		}
	}
	
	return $Entity;
}

/******************************************************************************/
// Get Attribute Consuming Service
function getAttributeConsumingServiceNames($RoleDescriptorNode){
	
	$Entity = Array();
	
	$ServiceNames = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'ServiceName' );
	foreach ($ServiceNames as $ServiceName){
		$lang = $ServiceName->getAttributeNodeNS('http://www.w3.org/XML/1998/namespace', 'lang')->nodeValue;
		$Entity[$lang] = trimToSingleLine($ServiceName->nodeValue);
	}
	
	return $Entity;
}

/******************************************************************************/
// Returns true if IdP has Hide-From-Discovery entity category attribute
function hasHideFromDiscoveryEntityCategory($IDPRoleDescriptorNode){
	// Get SAML Attributes for this entity
	$AttributeValues = $IDPRoleDescriptorNode->parentNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:assertion', 'AttributeValue');
	
	if (!$AttributeValues || $AttributeValues->length < 1){
		return false;
	}
	
	foreach( $AttributeValues as $AttributeValue ){
		if (trim($AttributeValue->nodeValue) == 'http://refeds.org/category/hide-from-discovery'){
			return true;
		}
	}
	
	return false;
}

?>
