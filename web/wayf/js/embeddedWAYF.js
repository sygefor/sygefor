// Copyright (c) 2014, SWITCH
// To use this JavaScript, please access:
// <?php echo $configurationScriptUrl ?>
// and copy/paste the resulting HTML snippet to an unprotected web page that 
// you want the embedded WAYF to be displayed

// ############################################################################

// Declare all global variables

// Essential settings
var wayf_sp_entityID;
var wayf_URL;
var wayf_return_url;
var wayf_sp_handlerURL;

// Other settings
var wayf_use_discovery_service;
var wayf_use_improved_drop_down_list;
var wayf_disable_remote_idp_logos;
var wayf_use_small_logo;
var wayf_width;
var wayf_height;
var wayf_background_color;
var wayf_border_color;
var wayf_font_color;
var wayf_font_size;
var wayf_hide_logo;
var wayf_auto_login;
var wayf_logged_in_messsage;
var wayf_auto_redirect_if_logged_in;
var wayf_hide_after_login;
var wayf_most_used_idps;
var wayf_overwrite_last_used_idps_text;
var wayf_overwrite_most_used_idps_text;
var wayf_overwrite_checkbox_label_text;
var wayf_overwrite_submit_button_text;
var wayf_overwrite_intro_text;
var wayf_overwrite_from_other_federations_text;
var wayf_default_idp;
var wayf_num_last_used_idps;
var wayf_show_categories;
var wayf_hide_categories;
var wayf_hide_idps;
var wayf_unhide_idps;
var wayf_show_remember_checkbox;
var wayf_force_remember_for_session;
var wayf_additional_idps;
var wayf_sp_samlDSURL;
var wayf_sp_samlACURL;
var wayf_use_disco_feed;
var wayf_discofeed_url;

// Internal variables
var wayf_disco_feed_idps;
var wayf_html = "";
var wayf_categories = { <?php echo $JSONCategoryList ?>};
var wayf_idps = { <?php echo $JSONIdPList ?> };
var wayf_other_fed_idps = {};

// Functions
function redirectTo(url){
	// Make sure the redirect always is being done in parent window
	if (window.parent){
		window.parent.location = url;
	} else {
		window.location = url;
	}
}

function submitForm(){
	
	if (document.IdPList.user_idp && document.IdPList.user_idp.selectedIndex == 0){
		alert('<?php echo $makeSelectionString ?>');
		return false;
	}
	
	// Set local cookie
	var selectedIdP = document.IdPList.user_idp[document.IdPList.user_idp.selectedIndex].value;
	setDomainSAMLDomainCookie(selectedIdP);
	
	// User chose federation IdP entry
	if( wayf_idps[selectedIdP]) {
		return true;
	} 
	
	// User chose IdP from other federation
	var redirect_url;
	
	// Redirect user to SP handler
	if (wayf_use_discovery_service){
		
		var entityIDGETParam = getGETArgument("entityID");
		var returnGETParam = getGETArgument("return");
		if (entityIDGETParam != "" && returnGETParam != ""){
			redirect_url = returnGETParam;
		} else {
			redirect_url = wayf_sp_samlDSURL;
			redirect_url += getGETArgumentSeparator(redirect_url) + 'target=' + encodeURIComponent(wayf_return_url);
		}
		
		// Append selected Identity Provider
		redirect_url += '&entityID=' + encodeURIComponent(selectedIdP);
		
		redirectTo(redirect_url);
	} else {
		redirect_url = wayf_sp_handlerURL + '?providerId=' 
		+ encodeURIComponent(selectedIdP)
		+ '&target=' + encodeURIComponent(wayf_return_url);
		
		redirectTo(redirect_url);
	}
	
	// If input type button is used for submit, we must return false
	return false;
}

function writeOptGroup(IdPElements, category){
	
	if (!wayf_categories[category]){
		writeHTML(IdPElements);
		return;
	}
	
	if (IdPElements == ''){
		return;
	}
	
	var categoryName = wayf_categories[category].name;
	
	if (wayf_show_categories){
		writeHTML('<optgroup label="' + categoryName + '">');
	}
	
	writeHTML(IdPElements);
	
	if (wayf_show_categories){
		writeHTML('</optgroup>');
	}
}

function writeHTML(a){
	wayf_html += a;
}

function isEmptyObject(obj){

	if (typeof(obj) != "object"){
		return true;
	}
	
	for (var index in obj){
		return false;
	}
	
	return true;
}

function isAllowedIdP(IdP){
	
	var type = '';
	if (wayf_idps[IdP]){
		type = wayf_idps[IdP].type;
	} else if (wayf_other_fed_idps[IdP]){
		type = wayf_other_fed_idps[IdP].type;
	}
	
	// Check if IdP shall be hidden 
	for ( var i = 0; i < wayf_hide_idps.length; i++){
		if (wayf_hide_idps[i] == IdP){
			return false;
		}
	}
	
	// Check if category is hidden
		// Check if IdP is unhidden in this category
	for ( var i = 0; i < wayf_hide_categories.length; i++){
		
		if (wayf_hide_categories[i] == "all" || wayf_hide_categories[i] == type){
			
			for ( var i=0; i < wayf_unhide_idps.length; i++){
				// Show IdP if it has to be unhidden
				if (wayf_unhide_idps[i] == IdP){
					return true;
				}
			}
			
			// If IdP is not unhidden, the default applies
			return false;
		}
	}
	
	// Default
	return true;
}

function setDomainSAMLDomainCookie(entityID){
	// Create and store SAML domain cookie on host where WAYF is embedded
	var currentDomainCookie = getCookie('_saml_idp');
	var encodedEntityID = encodeBase64(entityID);
	
	if (currentDomainCookie == null){
		currentDomainCookie = '';
	}
	
	var oldIdPs = currentDomainCookie.split(' ');
	var newCookie = '';
	for (var i = 0; i < oldIdPs.length; i++) {
		if (oldIdPs[i] != encodedEntityID && oldIdPs[i] != ''){
			newCookie += oldIdPs[i] + ' ';
		}
	}
	newCookie += encodedEntityID;
	setCookie('<?php echo $SAMLDomainCookieName ?>', newCookie , 100);
}

function setCookie(c_name, value, expiredays){
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + expiredays);
	document.cookie=c_name + "=" + escape(value) +
	((expiredays==null) ? "" : "; expires=" + exdate.toGMTString());
}

function getCookie(check_name){
	// First we split the cookie up into name/value pairs
	// Note: document.cookie only returns name=value, not the other components
	var a_all_cookies = document.cookie.split( ';' );
	var a_temp_cookie = '';
	var cookie_name = '';
	var cookie_value = '';
	
	for ( var i = 0; i < a_all_cookies.length; i++ ){
		// now we'll split apart each name=value pair
		a_temp_cookie = a_all_cookies[i].split('=');
		
		
		// and trim left/right whitespace while we're at it
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');
	
		// if the extracted name matches passed check_name
		if ( cookie_name == check_name )
		{
			// We need to handle case where cookie has no value but exists (no = sign, that is):
			if ( a_temp_cookie.length > 1 )
			{
				cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
			}
			// note that in cases where cookie is initialized but no value, null is returned
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = '';
	}
	
	return null;
}

// Query Shibboleth Session handler and process response afterwards
// This method has to be used because HttpOnly prevents reading 
// the shib session cookies via JavaScript
function isShibbolethSession(url){
	
	var result = queryGetURL(url);
	
	// Return true if session handler shows valid session
	if (result && result.search(/Authentication Time/i) > 0){
		return true;
	}
	
	return false;
}

// Loads Identity Provider from DiscoFeed and adds them to additional IdPs
function loadDiscoFeedIdPs(){
	
	var result = queryGetURL(wayf_discofeed_url);
	var IdPs = {};
	
	// Load JSON
	if (result != ''){
		IdPs = eval("(" +result + ")");
	}
	
	return IdPs;
}

// Makes a synchronous AJAX request with the given URL and returns 
// returned string or '' in case of a problem
function queryGetURL(url){
	var xmlhttp;
	console.error("The request for " + url + " timed out.");
	if (window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	}  else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	// Send synchronous request
	try {
		xmlhttp.open("GET", url, false);
		xmlhttp.send();
	} catch (e) {
		// Something went wrong, send back false
		return false;
	} 
	
	// Check response code
	if (xmlhttp.readyState != 4 || xmlhttp.status != 200 ){
		return '';
	}
	
	return xmlhttp.responseText;
}


// Adds unknown IdPs to wayf_additional_idps and hides IdPs that are not
// contained in the Discovery Feed
function processDiscoFeedIdPs(IdPs){
	
	if (typeof(IdPs) == "undefined"){
		return;
	}
	
	// Hide IdPs that are not in the Discovery Feed
	for (var entityID in wayf_idps){
		var foundIdP = false;
		for ( var i = 0; i < IdPs.length; i++) {
			if (IdPs[i].entityID == entityID){
				foundIdP = true;
			}
		}
		
		if (foundIdP == false){
			wayf_hide_idps.push(entityID);
		}
	}
	
	// Add unkown IdPs to wayf_additional_idps
	for ( var i = 0; i < IdPs.length; i++) {
		
		// Skip IdPs that are already known
		if (wayf_idps[IdPs[i].entityID]){
			continue;
		}
		
		var newIdP = getIdPFromDiscoFeedEntry(IdPs[i]);
		
		wayf_additional_idps.push(newIdP);
	}
}


function getIdPFromDiscoFeedEntry(IdPData){
	var name = IdPData.entityID;
	var name_default = '';
	var name_requested = '';
	var data = '';
	var logo = '';
	
	if (IdPData.DisplayNames){
		for (var i = 0; i < IdPData.DisplayNames.length; i++){
			
			name = IdPData.DisplayNames[i].value;
			
			if (IdPData.DisplayNames[i].lang == '<?php echo $language ?>'){
				name_requested = name;
			} else if (IdPData.DisplayNames[i].lang == 'en'){
				name_default = name;
			}
			
			data += ' ' + IdPData.DisplayNames[i].value;
		}
		
		if (name_requested != ''){
			name = name_requested;
		} else if (name_default != ''){
			name = name_default;
		}
	}
	
	if (IdPData.Keywords){
		for (var i = 0; i < IdPData.Keywords.length; i++){
			data += ' ' + IdPData.Keywords[i].value;
		}
	}
	
	if (IdPData.Logos){
		for (var i = 0; i < IdPData.Logos.length; i++){
			if (IdPData.Logos[i].height == 16 && IdPData.Logos[i].width == 16){
				logo = IdPData.Logos[i].value;
			}
		}
	}
	
	var newIdP = {
		"entityID":IdPData.entityID, 
		"name": name, 
		"type": "unknown", 
		"SAML1SSOurl":"https://this.url.does.not.exist/test", 
		"data": data, 
		"logoURL":logo
	};
	
	return newIdP;
}


// Sorts Discovery feed entries 
function sortEntities(a, b){
	var nameA = a.name.toLowerCase();
	var nameB = b.name.toLowerCase();
	
	if (nameA < nameB){
		return -1;
	}
	
	if (nameA > nameB){
		return 1;
	}
	
	return 0;
}

// Returns true if user is logged in
function isUserLoggedIn(){
	
	if (
		   typeof(wayf_check_login_state_function) != "undefined"
		&& typeof(wayf_check_login_state_function) == "function" ){
		
		// Use custom function
		return wayf_check_login_state_function();
	
	} else {
		// Check Shibboleth session handler
		return isShibbolethSession(wayf_sp_handlerURL + '/Session');
	}
}

function encodeBase64(input) {
	var base64chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var output = "", c1, c2, c3, e1, e2, e3, e4;
	
	for ( var i = 0; i < input.length; ) {
		c1 = input.charCodeAt(i++);
		c2 = input.charCodeAt(i++);
		c3 = input.charCodeAt(i++);
		e1 = c1 >> 2;
		e2 = ((c1 & 3) << 4) + (c2 >> 4);
		e3 = ((c2 & 15) << 2) + (c3 >> 6);
		e4 = c3 & 63;
		if (isNaN(c2)){
			e3 = e4 = 64;
		} else if (isNaN(c3)){
			e4 = 64;
		}
		output += base64chars.charAt(e1) + base64chars.charAt(e2) + base64chars.charAt(e3) + base64chars.charAt(e4);
	}
	
	return output;
}

function decodeBase64(input) {
	var base64chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var output = "", chr1, chr2, chr3, enc1, enc2, enc3, enc4;
	var i = 0;

	// Remove all characters that are not A-Z, a-z, 0-9, +, /, or =
	var base64test = /[^A-Za-z0-9\+\/\=]/g;
	input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
	
	do {
		enc1 = base64chars.indexOf(input.charAt(i++));
		enc2 = base64chars.indexOf(input.charAt(i++));
		enc3 = base64chars.indexOf(input.charAt(i++));
		enc4 = base64chars.indexOf(input.charAt(i++));

		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;

		output = output + String.fromCharCode(chr1);

		if (enc3 != 64) {
			output = output + String.fromCharCode(chr2);
		}
		if (enc4 != 64) {
			output = output + String.fromCharCode(chr3);
		}
		
		chr1 = chr2 = chr3 = "";
		enc1 = enc2 = enc3 = enc4 = "";
		
	} while (i < input.length);
	
	return output;
}

function getGETArgument(name){
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexString = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp(regexString);
	var results = regex.exec(window.location.href);
	
	if( results == null ){
		return "";
	} else {
		return decodeURIComponent(results[1]);
	}
}

function getGETArgumentSeparator(url){
	if (url.indexOf('?') >=0 ){
		return '&';
	} else {
		return '?';
	}
}

function ieLoadBugFix(scriptElement, callback){
	if (scriptElement.readyState && (scriptElement.readyState=='loaded' || scriptElement.readyState=='completed')){
		callback();
	 } else {
		setTimeout(function() {
			ieLoadBugFix(scriptElement, callback); 
		}, 100);
	 }
}

function getOptionHTML(entityID){
	
	var IdPData;
	if (wayf_idps[entityID]){
		IdPData = wayf_idps[entityID];
	} else if (wayf_other_fed_idps[entityID]){
		IdPData = wayf_other_fed_idps[entityID];
	} else {
		return '';
	}
	
	var content = '';
	var data = '';
	var logo = '';
	var selected = '';
	
	if (IdPData.data){
		data = ' data="' + IdPData.data + '"';
	}
	
	if (IdPData.logoURL){
		logo = ' logo="' + IdPData.logoURL + '"';
	}
	
	if (IdPData.selected){
		selected = ' selected="selected"';
	}
	
	content = '<option value="' + entityID + '"' + data + logo + selected + '>' + IdPData.name + '</option>';
	
	return content;
}

function loadJQuery() {
	
	var head = document.getElementsByTagName('head')[0];
	var script = document.createElement('script');
	var improvedDropDownLoaded = false;
	script.src = '<?php echo $javascriptURL ?>/jquery.js';
	script.type = 'text/javascript';
	script.onload = function() {
		loadImprovedDropDown();
		improvedDropDownLoaded = true;
	};
	head.appendChild(script);
	
	// Fix for IE Browsers
	ieLoadBugFix(script, function(){
		if (!improvedDropDownLoaded){
			loadImprovedDropDown();
		}
	});
}

function loadImprovedDropDown(){
	
	
	// Load CSS
	$('head').append('<link rel="stylesheet" type="text/css" href="<?php echo $cssURL ?>/default-ImprovedDropDown.css">');
	
	// Load Improved Drop Down Javascript
	$.getScript( '<?php echo $javascriptURL ?>/improvedDropDown.js', function( ) {
		var searchText = '<?php echo $searchText ?>';
		$("#user_idp:enabled option[value='-']").text(searchText);
		
		// Convert select element into improved drop down list
		$("#user_idp:enabled").improveDropDown({
			iconPath:'<?php echo $imageURL ?>/drop_icon.png',
			noMatchesText: '<?php echo $noIdPFoundText ?>',
			noItemsText: '<?php echo $noIdPAvailableText ?>',
			disableRemoteLogos: wayf_disable_remote_idp_logos
		});
	 
	});
}

(function() {
	
	var config_ok = true; 
	
	// Get GET parameters that maybe are set by Shibboleth
	var returnGETParam = getGETArgument("return");
	var entityIDGETParam = getGETArgument("entityID");
	
	// First lets make sure properties are available
	if(
		typeof(wayf_use_discovery_service)  == "undefined"  
		|| typeof(wayf_use_discovery_service) != "boolean"
	){
		wayf_use_discovery_service = true;
	}
	
	if(
		typeof(wayf_use_improved_drop_down_list)  == "undefined"  
		|| typeof(wayf_use_improved_drop_down_list) != "boolean"
	){
		wayf_use_improved_drop_down_list = false;
	}
	
	if(
		typeof(wayf_disable_remote_idp_logos)  == "undefined"  
		|| typeof(wayf_disable_remote_idp_logos) != "boolean"
	){
		wayf_disable_remote_idp_logos = false;
	}
	
	// Overwrite entityID with GET argument if present
	var entityIDGETParam = getGETArgument("entityID");
	if (entityIDGETParam != ""){
		wayf_sp_entityID = entityIDGETParam;
	}
	
	if(
		typeof(wayf_sp_entityID) == "undefined"
		|| typeof(wayf_sp_entityID) != "string"
		){
		alert('The mandatory parameter \'wayf_sp_entityID\' is missing. Please add it as a javascript variable on this page.');
		config_ok = false;
	}
	
	if(
		typeof(wayf_URL) == "undefined"
		|| typeof(wayf_URL) != "string"
		){
		alert('The mandatory parameter \'wayf_URL\' is missing. Please add it as a javascript variable on this page.');
		config_ok = false;
	}
	
	if(
		typeof(wayf_return_url) == "undefined"
		|| typeof(wayf_return_url) != "string"
		){
		alert('The mandatory parameter \'wayf_return_url\' is missing. Please add it as a javascript variable on this page.');
		config_ok = false;
	}
	
	if(
		wayf_use_discovery_service == false 
		&& typeof(wayf_sp_handlerURL) == "undefined"
		){
		alert('The mandatory parameter \'wayf_sp_handlerURL\' is missing. Please add it as a javascript variable on this page.');
		config_ok = false;
	}
	
	if(
		wayf_use_discovery_service == true 
		&& typeof(wayf_sp_samlDSURL) == "undefined"
		){
		// Set to default DS handler
		wayf_sp_samlDSURL = wayf_sp_handlerURL + "/Login";
	}
	
	if (
		typeof(wayf_sp_samlACURL) == "undefined"
		|| typeof(wayf_sp_samlACURL) != "string"
		){
		wayf_sp_samlACURL = wayf_sp_handlerURL + '/SAML/POST';
	}
	
	if(
		typeof(wayf_font_color) == "undefined"
		|| typeof(wayf_font_color) != "string"
		){
		wayf_font_color = 'black';
	}
	
	if(
		typeof(wayf_font_size) == "undefined"
		|| typeof(wayf_font_size) != "number"
		){
		wayf_font_size = 12;
	}
	
	if(
		typeof(wayf_border_color) == "undefined"
		|| typeof(wayf_border_color) != "string"
		){
		wayf_border_color = '#848484';
	}
	
	if(
		typeof(wayf_background_color) == "undefined"
		|| typeof(wayf_background_color) != "string"
		){
		wayf_background_color = '#F0F0F0';
	}
	
	if(
		typeof(wayf_use_small_logo) == "undefined" 
		|| typeof(wayf_use_small_logo) != "boolean"
		){
		wayf_use_small_logo = true;
	}
	
	if(
		typeof(wayf_hide_logo) == "undefined" 
		|| typeof(wayf_use_small_logo) != "boolean"
		){
		wayf_hide_logo = false;
	}
	
	if(
		typeof(wayf_width) == "undefined" 
		|| typeof(wayf_width) != "number"
	){
		wayf_width = "auto";
	} else {
		wayf_width += 'px';
	}
	
	if(
		typeof(wayf_height) == "undefined" 
		|| typeof(wayf_height) != "number"
		){
		wayf_height = "auto";
	} else {
		wayf_height += "px";
	}
	
	if(
		typeof(wayf_show_remember_checkbox) == "undefined"
		|| typeof(wayf_show_remember_checkbox) != "boolean"
		){
		wayf_show_remember_checkbox = true;
	}
	
	if(
		typeof(wayf_force_remember_for_session) == "undefined"
		|| typeof(wayf_force_remember_for_session) != "boolean"
		){
		wayf_force_remember_for_session = false;
	}
	
	if(
		typeof(wayf_auto_login) == "undefined"
		|| typeof(wayf_auto_login) != "boolean"
		){
		wayf_auto_login = true;
	}
	
	if(
		typeof(wayf_hide_after_login) == "undefined"
		|| typeof(wayf_hide_after_login) != "boolean"
		){
		wayf_hide_after_login = true;
	}
	
	if(
		typeof(wayf_logged_in_messsage) == "undefined"
		|| typeof(wayf_logged_in_messsage) != "string"
		){
		wayf_logged_in_messsage = "<?php echo $loggedInString ?>".replace(/%s/, wayf_return_url);
	}

	if(
		typeof(wayf_auto_redirect_if_logged_in) == "undefined"
		|| typeof(wayf_auto_redirect_if_logged_in) != "boolean"
		){
		wayf_auto_redirect_if_logged_in = false;
	}

	if(
		typeof(wayf_default_idp) == "undefined"
		|| typeof(wayf_default_idp) != "string"
		){
		wayf_default_idp = '';
	}

	if(
		typeof(wayf_num_last_used_idps) == "undefined"
		|| typeof(wayf_num_last_used_idps) != "number"
		){
		wayf_num_last_used_idps = 3;
	}
	
	if(
		typeof(wayf_most_used_idps) == "undefined"
		|| typeof(wayf_most_used_idps) != "object"
		){
		wayf_most_used_idps = new Array();
	}

	if(
		typeof(wayf_logged_in_messsage) == "undefined"
		|| typeof(wayf_logged_in_messsage) != "string"
		){
		wayf_logged_in_messsage = "<?php echo $loggedInString ?>".replace(/%s/, wayf_return_url);
	}

	if(
		typeof(wayf_overwrite_last_used_idps_text) == "undefined"
		|| typeof(wayf_overwrite_last_used_idps_text) != "string"
		){
		wayf_overwrite_last_used_idps_text = "<?php echo $lastUsedIdPsString ?>";
	}

	if(
		typeof(wayf_overwrite_most_used_idps_text) == "undefined"
		|| typeof(wayf_overwrite_most_used_idps_text) != "string"
		){
		wayf_overwrite_most_used_idps_text = "<?php echo $mostUsedIdPsString ?>";
	}

	if(
		typeof(wayf_overwrite_checkbox_label_text) == "undefined"
		|| typeof(wayf_overwrite_checkbox_label_text) != "string"
		){
		wayf_overwrite_checkbox_label_text = "<?php echo $rememberSelectionText ?>";
	}

	if(
		typeof(wayf_overwrite_submit_button_text) == "undefined"
		|| typeof(wayf_overwrite_submit_button_text) != "string"
		){
		wayf_overwrite_submit_button_text = "<?php echo $loginString ?>";
	}

	if(
		typeof(wayf_overwrite_intro_text) == "undefined"
		|| typeof(wayf_overwrite_intro_text) != "string"
		){
		wayf_overwrite_intro_text = "<?php echo $loginWithString ?>";
	}
	
	if(
		typeof(wayf_overwrite_from_other_federations_text) == "undefined"
		|| typeof(wayf_overwrite_from_other_federations_text) != "string"
		){
		wayf_overwrite_from_other_federations_text = "<?php echo $otherFederationString ?>";
	}
	
	if(
		typeof(wayf_show_categories) == "undefined"
		|| typeof(wayf_show_categories) != "boolean"
		){
		wayf_show_categories = true;
	}
	
	if(
		typeof(wayf_hide_categories) == "undefined"
		|| typeof(wayf_hide_categories) != "object"
		){
		wayf_hide_categories = new Array();
	}
	
	if(
		typeof(wayf_unhide_idps) == "undefined"
		||  typeof(wayf_unhide_idps) != "object"
	){
		wayf_unhide_idps = new Array();
	}
	
	if(
		typeof(wayf_hide_idps) == "undefined"
		|| typeof(wayf_hide_idps) != "object"
		){
		wayf_hide_idps = new Array();
	}
	
	if(
		typeof(wayf_additional_idps) == "undefined"
		|| typeof(wayf_additional_idps) != "object"
		){
		wayf_additional_idps = [];
	}
	
	if(
		typeof(wayf_use_disco_feed) == "undefined"
		|| typeof(wayf_use_disco_feed) != "boolean"
		){
		wayf_use_disco_feed = false;
	}
	
	if(
		typeof(wayf_discofeed_url) == "undefined"
		|| typeof(wayf_discofeed_url) != "string"
		){
		wayf_discofeed_url = "/Shibboleth.sso/DiscoFeed";
	}
	
	// Exit without outputting html if config is not ok
	if (config_ok != true){
		return;
	}
	
	// Check if user is logged in already:
	var user_logged_in = isUserLoggedIn();
	
	// Check if user is authenticated already and should
	// be redirected to wayf_return_url
	if (
		user_logged_in
		&& wayf_auto_redirect_if_logged_in
	){
		redirectTo(wayf_return_url);
		return;
	}
	
	// Check if user is authenticated already and 
	// whether something has to be drawn
	if (
		wayf_hide_after_login 
		&& user_logged_in 
		&& wayf_logged_in_messsage == ''
	){
		
		// Exit script without drawing
		return;
	}
	
	// Now start generating the HTML for outer box
	if(
		wayf_hide_after_login 
		&& user_logged_in
	){
		writeHTML('<div id="wayf_div" style="background:' + wayf_background_color + ';border-style: solid;border-color: ' + wayf_border_color + ';border-width: 1px;padding: 10px; height: auto;width: ' + wayf_width + ';text-align: left;overflow: hidden;">');
	} else {
		writeHTML('<div id="wayf_div" style="background:' + wayf_background_color + ';border-style: solid;border-color: ' + wayf_border_color + ';border-width: 1px;padding: 10px; height: ' + wayf_height + ';width: ' + wayf_width + ';text-align: left;overflow: hidden;">');
	}
	
	// Do we have to display the logo
	if (wayf_hide_logo != true){
		
		// Write header of logo div
		writeHTML('<div id="wayf_logo_div" style="float: right;"><a href="<?php echo sprintf($federationURL, $language) ?>" target="_blank" style="border:0px; margin-bottom: 4px;">');
		
		// Which size of the logo should we display
		var embeddedLogoURL = '';
		if (wayf_use_small_logo){
			embeddedLogoURL = "<?php echo $smallLogoURL ?>";
		} else {
			embeddedLogoURL = "<?php echo $logoURL ?>";
		}
		
		// Only show logo if it is not empty
		if (embeddedLogoURL != ''){
			writeHTML('<img id="wayf_logo" src="' + embeddedLogoURL +  '" alt="Federation Logo" style="border:0px; margin-bottom: 4px;">');
		}
		
		// Write footer of logo div
		writeHTML('</a></div>');
	}
	
	// Start login check
	// If session exists, we only draw the logged_in_message
	if(
		wayf_hide_after_login 
		&& user_logged_in
	){
		writeHTML('<p id="wayf_intro_div" style="float:left;font-size:' + wayf_font_size + 'px;color:' + wayf_font_color + ';">' + wayf_logged_in_messsage + '</p>');
		
	} else {
	// Else draw embedded WAYF
		
		// Draw intro text
		writeHTML('<label for="user_idp" id="wayf_intro_label" style="float:left; min-width:80px; font-size:' + wayf_font_size + 'px;color:' + wayf_font_color + ';">' + wayf_overwrite_intro_text + '</label>');
		
		var wayf_authReq_URL = '';
		var form_start = '';
		
		if (wayf_use_discovery_service == true){
			// New SAML Discovery Service protocol
			
			wayf_authReq_URL = wayf_URL;
			
			// Use GET arguments or use configuration parameters
			if (entityIDGETParam != "" && returnGETParam != ""){
				wayf_authReq_URL += '?entityID=' + encodeURIComponent(entityIDGETParam);
				wayf_authReq_URL += '&amp;return=' + encodeURIComponent(returnGETParam);
			} else {
				var return_url = wayf_sp_samlDSURL + getGETArgumentSeparator(wayf_sp_samlDSURL);
				return_url += 'SAMLDS=1&target=' + encodeURIComponent(wayf_return_url);
				wayf_authReq_URL += '?entityID=' + encodeURIComponent(wayf_sp_entityID);
				wayf_authReq_URL += '&amp;return=' + encodeURIComponent(return_url);
			}
		} else {
			// Old Shibboleth WAYF protocol
			wayf_authReq_URL = wayf_URL;
			wayf_authReq_URL += '?providerId=' + encodeURIComponent(wayf_sp_entityID);
			wayf_authReq_URL += '&amp;target=' + encodeURIComponent(wayf_return_url);
			wayf_authReq_URL += '&amp;shire=' + encodeURIComponent(wayf_sp_samlACURL);
			wayf_authReq_URL += '&amp;time=<?php echo $utcTime ?>';
		}
		
		// Add form element
		form_start = '<form id="IdPList" name="IdPList" method="post" target="_parent" action="' + wayf_authReq_URL + '">';
		
		// Do auto login if redirect cookie exists
		if ('<?php echo $redirectCookie ?>' != '' && wayf_auto_login){
		
			// Redirect user automatically to WAYF
			var redirect_url = wayf_authReq_URL.replace(/&amp;/g, '&');
			
			redirectTo(redirect_url);
			return;
		}
		
		// Get local cookie
		var saml_idp_cookie = getCookie('_saml_idp');
		var last_idp = '';
		var last_idps = new Array();
		
		// Get last used IdP from local host cookie
		if (saml_idp_cookie && saml_idp_cookie.length > 0){
			last_idps = saml_idp_cookie.split(/[ \+]/);
			if (last_idps.length > 0){
				last_idp = last_idps[(last_idps.length - 1)];
				if (last_idp.length > 0){
					last_idp = decodeBase64(last_idp);
				}
			}
		}
		
		// Load additional IdPs from DiscoFeed if feature is enabled
		if (wayf_use_disco_feed){
			wayf_disco_feed_idps = loadDiscoFeedIdPs();
			
			// Hide IdPs for which SP doesnt have metadata and add unknown IdPs 
			// Add to additional IdPs
			processDiscoFeedIdPs(wayf_disco_feed_idps);
		}
		
		// Sort additional IdPs and add IdPs to sorted associative array of other federation IdPs
		if (wayf_additional_idps.length > 0){
			wayf_additional_idps.sort(sortEntities);
			
			for ( var i = 0; i < wayf_additional_idps.length; i++){
				var IdP = wayf_additional_idps[i];
				
				if (!IdP){
					continue;
				}
				
				if (IdP.entityID && last_idp != '' && IdP.entityID == last_idp){
					IdP.selected = true;
				} else if (IdP.entityID && last_idp == '' && IdP.entityID == wayf_default_idp){
					IdP.selected = true;
				}
				
				if (!IdP.type){
					IdP.type = "unknown";
				}
				
				if (!IdP.data){
					IdP.data = IdP.name;
				}
				
				wayf_other_fed_idps[IdP.entityID] = IdP;
			}
		}
		
		// Set default IdP if no last used IdP exists
		if (last_idp == '' && wayf_default_idp != ''){
			if (wayf_idps[wayf_default_idp]){
				wayf_idps[wayf_default_idp].selected = true;
			}
		}
		
		
		writeHTML(form_start);
		writeHTML('<input name="request_type" type="hidden" value="embedded">');
		writeHTML('<select id="user_idp" name="user_idp" style="margin-top: 6px; width: 100%;">');
		
		// Add first entry: "Select your IdP..."
		writeHTML('<option value="-"><?php echo $selectIdPString ?> ...</option>');
		
		// Last used
		if (wayf_show_categories == true && wayf_num_last_used_idps > 0 && last_idps.length > 0){
			
			// Add new category
			var category = "wayf_last_used_idps";
			wayf_categories.wayf_last_used_idps = {
				"type": category, 
				"name": wayf_overwrite_last_used_idps_text
			}
			
			var IdPElements = '';
			var counter = wayf_num_last_used_idps;
			for ( var i= (last_idps.length - 1); i >= 0; i--){
				
				if (counter <= 0){
					break;
				}
				
				var currentIdP = decodeBase64(last_idps[i]);
				var content = getOptionHTML(currentIdP);
				
				if (content != ''){
					counter--;
					IdPElements += content;
				}
				
			}
			
			writeOptGroup(IdPElements, category);
		}
		
		// Most used and Favourites
		if (wayf_show_categories == true && wayf_most_used_idps.length > 0){
			
			// Add new category
			var category = "wayf_most_used_idps";
			wayf_categories.wayf_most_used_idps = {
				"type": category, 
				"name": wayf_overwrite_most_used_idps_text
			}
			
			// Show most used IdPs in the order they are defined
			var IdPElements = '';
			for ( var i=0; i < wayf_most_used_idps.length; i++){
				if (wayf_idps[wayf_most_used_idps[i]]){
					IdPElements += getOptionHTML(wayf_most_used_idps[i]);
				}
			}
			
			writeOptGroup(IdPElements, category);
		}
		
		// Draw drop down list
		var category = '';
		var IdPElements = '';
		for(var entityID in wayf_idps){
			
			var idp_type = wayf_idps[entityID].type;
			
			// Draw category
			if (category != idp_type){
				
				// Finish category if a new one starts that exists
				if (IdPElements != ''){
					writeOptGroup(IdPElements, category);
				}
				
				// Reset content
				IdPElements = '';
			}
			
			// Add IdP if it is allowed
			if (isAllowedIdP(entityID)){
				IdPElements += getOptionHTML(entityID);
			}
			
			// Set current category/type
			category = idp_type;
		}
		
		// Output last remaining element
		writeOptGroup(IdPElements, category);
		
		// Show IdPs from other federations
		if ( ! isEmptyObject(wayf_other_fed_idps)){
			
			// Add new category
			var category = "wayf_other_federations_idps";
			wayf_categories.wayf_other_federations_idps = {
				"type": category, 
				"name": wayf_overwrite_from_other_federations_text
			}
			
			// Show additional IdPs
			var IdPElements = '';
			for (entityID in wayf_other_fed_idps){
				if (isAllowedIdP(entityID)){
					IdPElements += getOptionHTML(entityID)
				}
			}
			
			writeOptGroup(IdPElements, category);
		}
		
		writeHTML('</select>');
		
		// Do we have to show the remember settings checkbox?
		if (wayf_show_remember_checkbox){
			
			// Draw checkbox table
			writeHTML('<div id="wayf_remember_checkbox_div" style="float: left;margin-top:6px;"><table style="border: 0; border-collapse: collapse;"><tr><td style="vertical-align: top;">');
			
			// Is the checkbox forced to be checked
			if (wayf_force_remember_for_session){
				// First draw the dummy checkbox ...
				writeHTML('<input id="wayf_remember_checkbox" type="checkbox" name="session_dummy" value="true" checked="checked" disabled="disabled" style="margin:2px 2px 0 0; border: 0; padding:0;">');
				// ... and now the real but hidden checkbox
				writeHTML('<input type="hidden" name="session" value="true">');
			} else {
				writeHTML('<input id="wayf_remember_checkbox" type="checkbox" name="session" value="true" <?php echo $checkedBool ?> style="margin:2px 2px 0 0; border: 0; padding:0;">');
			}
			
			// Draw label
			writeHTML('</td><td style="vertical-align: top;"><label for="wayf_remember_checkbox" id="wayf_remember_checkbox_label" style="font-size:' + wayf_font_size + 'px;color:' + wayf_font_color + ';">' + wayf_overwrite_checkbox_label_text + '</label>');
			
			writeHTML('</td></tr></table></div>');
		} else if (wayf_force_remember_for_session){
			// Is the checkbox forced to be checked but hidden
			writeHTML('<input id="wayf_remember_checkbox" type="hidden" name="session" value="true">');
		}
		
		
		// Draw submit button
		writeHTML('<input id="wayf_submit_button" type="submit" name="Login" accesskey="s" value="' + wayf_overwrite_submit_button_text + '" style="float: right; margin-top:6px;" onClick="javascript:return submitForm();">');
		
		// Close form
		writeHTML('</form>');
		
	}  // End login check
	
	// Close box
	writeHTML('</div>');
	
	// Now output HTML all at once
	document.write(wayf_html);
	
	if (wayf_use_improved_drop_down_list){
		// Check if jQuery is alread loaded or version is older that this version's
		if (typeof jQuery == 'undefined'){
			loadJQuery();
		} else {
			// Check JQuery version and load our version if it is newer
			var version = jQuery.fn.jquery.split('.');
			var versionMajor = parseFloat(version[0]);
			var versionMinor = parseFloat(version[1]);
			if (versionMajor <= 1 && versionMinor < 5){
				loadJQuery();
			} else {
				loadImprovedDropDown();
			}
		}
	}
})()