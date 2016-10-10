<?php // Copyright (c) 2016, SWITCH ?>

<!-- EMBEDDED-WAYF-START -->
<script type="text/javascript"><!--
// To use this JavaScript, please access:
// https://<?php echo $host ?><?php echo $path ?>/embedded-wayf.js/snippet.html
// and copy/paste the resulting HTML snippet to an unprotected web page that 
// you want the embedded WAYF to be displayed


//////////////////// ESSENTIAL SETTINGS ////////////////////

// URL of the WAYF to use
// Examples: "https://wayf.example.org/SWITCHwayf/WAYF"
// [Mandatory]
var wayf_URL = "https://<?php echo $host ?><?php echo $path ?>";

// EntityID of the Service Provider that protects this Resource
// Value will be overwritten automatically if the page where the Embedded WAYF
// is displayed is called with a GET argument 'entityID' as automatically set by Shibboleth
// Examples: "https://econf.switch.ch/shibboleth", "https://dokeos.unige.ch/shibboleth"
// [Mandatory]
var wayf_sp_entityID = "https://my-app.switch.ch/shibboleth";

// Shibboleth Service Provider handler URL
// Examples: "https://point.switch.ch/Shibboleth.sso", "https://rr.aai.switch.ch/aaitest/Shibboleth.sso"
// [Mandatory, if wayf_use_discovery_service = false]
var wayf_sp_handlerURL = "https://my-app.switch.ch/Shibboleth.sso";

// URL on this resource that the user should be returned to after authentication
// Examples: "https://econf.switch.ch/aai/home", "https://olat.uzh.ch/my/courses"
// [Mandatory]
var wayf_return_url = "https://my-app.switch.ch/aai/index.php?page=show_welcome";


//////////////////// RECOMMENDED SETTINGS ////////////////////

// Width of the embedded WAYF in pixels or "auto"
// This is the width of the content only (without padding and border). 
// Add 2 x (10px + 1px) = 22px for padding and border to get the actual 
// width of everything that is drawn.
// [Optional, default: "auto"]
// var wayf_width  = 250;

// Height of the embedded WAYF in pixels or "auto"
// This is the height of the content only (without padding and border). 
// Add 2 x (10px + 1px) = 22px for padding and border to get the actual 
// height of everything that is drawn.
// [Optional, default: "auto"]
// Example for fixed size: 
// var wayf_height = 150;

// Whether to show the checkbox to remember settings for this session
// [Optional, default: true]
//var wayf_show_remember_checkbox = true;

// Hide the Logo
// If true, no logo is shown
// [Optional, default: false]
// var wayf_hide_logo = false;

// Logo size
// Choose whether the small or large logo should be used
// [Optional, default: true]
//var wayf_use_small_logo = true;

// Font size
// [Optional, default: 12]
//var wayf_font_size = 12;

// Font color as CSS color value, e.g. 'black' or '#000000'
// [Optional, default: #000000]
//var wayf_font_color = '#000000';

// Border color as CSS color value, e.g. 'black' or '#000000'
// [Optional, default: #848484]
//var wayf_border_color = '#848484';

// Background color as CSS color value, e.g. 'black' or '#000000'
// [Optional, default: #F0F0F0]
//var wayf_background_color = '#F0F0F0';

// Whether to automatically log in user if he has a session/permanent redirect
// cookie set at central wayf
// [Optional, default: true]
//var wayf_auto_login = true;

// Whether to hide the WAYF after the user was logged in
// This requires that the _shib_session_* cookie is set when a user 
// could be authenticated, which is the default case when Shibboleth is used.
// For other Service Provider implementations have a look at the setting
// wayf_check_login_state_function that allows you to customize this
// [Optional, default: true]
// var wayf_hide_after_login = true;

// Whether or not to show the categories in the drop-down list
// Possible values are: true or false
// [Optional, default: true]
// var wayf_show_categories =  true;

// Most used Identity Providers will be shown as top category in the drop down
// list if this feature is used.
// Will not be shown if wayf_show_categories is false
// [Optional, default: none]
// var wayf_most_used_idps =  new Array("https://aai-logon.unibas.ch/idp/shibboleth", "https://aai.unil.ch/idp/shibboleth");

// Categories of Identity Provider that should not be shown
// Possible values are: <?php echo $types ?>, "all"
// Example of how to hide categories
// var wayf_hide_categories =  new Array("other", "library");
// [Optional, default: none]
// var wayf_hide_categories =  new Array();

// EntityIDs of Identity Provider whose category is hidden but that should be shown anyway
// Example of how to unhide certain Identity Providers
// var wayf_unhide_idps = new Array("https://aai-login.uzh.ch/idp/shibboleth");
// [Optional, default: none]
// var wayf_unhide_idps = new Array();

// EntityIDs of Identity Provider that should not be shown at all
// Example of how to hide certain Identity Provider
// var wayf_hide_idps = new Array("https://idp.unige.ch/idp/shibboleth", "https://aai-logon.switch.ch/idp/shibboleth");
// [Optional, default: none]
// var wayf_hide_idps = new Array();

//////////////////// ADVANCED SETTINGS ////////////////////

// Use the SAML2/Shibboleth 2 Discovery Service protocol where
// the user is sent back to the Service Provider after selection
// of his Home Organisation.
// This feature should only be uncommented and set to false if there 
// is a good reason why to use the old and deprecated Shibboleth WAYF
// protocol instead.
// [Optional, default: true]
// var wayf_use_discovery_service = false;

// If enabled, the Embedded WAYF will activate the 
// improved drop down list feature, which will transform the list of 
// organisations into a search-field while keeping its original function as
// a select list. To make this work, the JQuery library will dynamically be 
// loaded if it is not yet present. Additionally, another Javascript and CSS
// file are loaded to perform the actual transformation.
// Please note that this feature will also display the organisations' logos,
// which might be loaded from a remote domain. While generally not especially
// dangerous, there is always a risk when loading content (in this case
// images) from third party hosts.
// [Optional, default: false]
// var wayf_use_improved_drop_down_list = false;

// If true the improved drop-down-list will not display IdP logos that
// have to be loaded from remote URLs. That way the web browser
// does not have to make requests to third party hosts.
// Logos that are embedded using data URIs 
// (src="data:image/png;base64...") will however still be displayed
// Don't confuse this with wayf_hide_logo, which shows or hides
// the logo of this WAYF instance
// [Optional, default: false]
//  wayf_disable_remote_idp_logos = false;

// Force the user's Home Organisation selection to be remembered for the
// current browser session. If wayf_show_remember_checkbox is true
// the checkbox will be shown but will be read only.
// WARNING: Only use this feature if you know exactly what you are doing
//          This option will cause problems that are difficult to find 
//          in case they accidentially select a wrong Home Organisation
// [Optional, default: false]
//var wayf_force_remember_for_session = false;

// Session Initiator URL of the Service Provider
// Examples: "https://interact.switch.ch/Shibboleth.sso/Login", "https://dokeos.unige.ch/Shibboleth.sso/DS"
// This will implicitely be set to wayf_sp_samlDSURL = wayf_sp_handlerURL + "/Login";
// or will be set automatically if the page where the Embedded WAYF is placed is called
// with a 'return' and an 'entityID' GET Arguments
// [Optional, if wayf_use_discovery_service = true 
//  or if wayf_additional_idps is not empty, default: wayf_sp_handlerURL + "/Login"]
// var wayf_sp_samlDSURL = wayf_sp_handlerURL + "/Login";

// Default IdP to preselect when central WAYF couldn't guess IdP either
// This is usually the case the first time ever a user accesses a resource
// [Optional, default: none]
// var wayf_default_idp = "https://aai-logon.switch.ch/idp/shibboleth";

// Number of last used IdPs to show
// Will not be shown if wayf_show_categories is false 
// Set to 0 to deactivate
// [Optional, default: 3]
// var wayf_num_last_used_idps = 3;

// Set a custom Assertion Consumer URL instead of
// the default wayf_sp_handlerURL + '/SAML/POST'
// Only relevant if wayf_use_discovery_service is false and SAML1 is used.
// Examples: "https://my-app.switch.ch/custom/saml-implementation/samlaa"
// This will implicitely be set to wayf_sp_samlACURL = wayf_sp_handlerURL + "/SAML/POST";
// [Optional, default: wayf_sp_handlerURL + "/SAML/POST"]
// var wayf_sp_samlACURL = "https://my-app.switch.ch/custom/saml-implementation/samlaa";

// Overwites the text of the checkbox if
// wayf_show_remember_checkbox is set to true
// [Optional, default: none]
// var wayf_overwrite_checkbox_label_text = 'Save setting for today';

// Overwrites the text of the submit button
// [Optional, default: none]
// var wayf_overwrite_submit_button_text = 'Go';

// Overwrites the intro text above the drop-down list
// [Optional, default: none]
// var wayf_overwrite_intro_text = 'Select your Home Organisation to log in';

// Overwrites the category name of the most used IdP category in the drop-down list
// [Optional, default: none]
// var wayf_overwrite_most_used_idps_text = 'Most popular';

// Overwrites the category name of the last used IdP category in the drop-down list
// [Optional, default: none]
// var wayf_overwrite_last_used_idps_text = 'Previously used';

// Overwrites the category name of IdPs from other federations in the drop-down list
// [Optional, default: none]
// var wayf_overwrite_from_other_federations_text = 'Other organisations';

// Whether to hide the WAYF after the user was logged in
// This requires that the _shib_session_* cookie is set when a user 
// could be authenticated
// If you want to hide the embedded WAYF completely, uncomment
// the property and set it to "". This then won't draw anything
// [Optional, default: none]
// var wayf_logged_in_messsage = "";

// If the user is already logged in and this variable is set to true, the WAYF
// will automatically redirect the user to the URL set in wayf_return_url.
// If the WAYF is embedded on a dedicated login page, this value should be set 
// to true. Else, it should be left at its default value 'false'.
// [Optional, default: false]
// var wayf_auto_redirect_if_logged_in = true;

// Provide the name of a JavaScript function that checks whether the user
// already is logged in. The function should return true if the user is logged
// in or false otherwise. If the user is logged in, the Embedded WAYF will
// hide itself or draw a custom message depending on the 
// setting wayf_logged_in_messsage. The default check will access a Shibboleth
// session handler which typically is found at /Shibboleth.sso/Session.
// [Optional, default: none]
// var wayf_check_login_state_function = function() { 
// if (# specify user-is-logged-in condition#)
//   return true;
// else 
//   return false;
// }

// EntityIDs, Names and SSO URLs of Identity Providers from other federations 
// that should be added to the drop-down list. 
// name: Name of the Identity Provider to display
// entityID: SAML entityID/providerID of this Identity Provider
// SAML1SSOurl: Endpoint for the SAML1 SSO handler
// logoURL: URL or inline image data of that IdP. Image must be a 16x16 pixel image
//          and it should be loaded from an HTTPS URL. Otherwise IE and other
//          browsers complain
// data: Non-visible data that may be used to find this Identity Provider when the
//       improve drop-down feature is enabled. This string for example can  include 
//       the domain names, abbreviations, localities or alternative names of the 
//       organisation. Basically, anything the user could use to search his institution.
//       
// The IdPs will be displayed in the order they are defined
// [Optional, default: none]
// var wayf_additional_idps = [ ];

// Example of how to add Identity Provider from other federations
// var wayf_additional_idps = [ 
//        
//        {name:"International University X",
//        entityID:"urn:mace:example.org:example.university.org",
//        SAML1SSOurl:"https://int.univ.org/shibboleth-idp/SSO",
//        logoURL:"https://int.univ.org/favicon.ico",
//        data:"univ.org université intérnationale X"},
//
//        {name:"Some Other University",
//        entityID:"https://other.univ.edu/idp/shibboleth",
//        SAML1SSOurl:"https://other.univ.edu/shibboleth-idp/SSO",
//        logoURL:"https://other.univ.edu/favicon.ico",
//        data:"other.univ.org autre université intérnationale X"},
// ];


// Whether to load Identity Providers from the Discovery Feed provided by
// the Service Provider. 
// IdPs that are not listed in the Discovery Feed and that the SP therefore is 
// not  are able to accept assertions from, are hidden by the Embedded WAYF
// IdPs that are in the Discovery Feed but are unknown to the SWITCHwayf
// are added to the wayf_additional_idps. 
// The list wayf_additional_idps will be sorted alphabetically
// The SP must have configured the discovery feed handler that generates a 
// JSON object. Otherwise it won't generate the JSON data containing the IdPs.
// [Optional, default: false]
// var wayf_use_disco_feed = false;

// URL where to load the Discovery Feed from in case wayf_use_disco_feed is true
// [Optional, default: none]
// var wayf_discofeed_url = "/Shibboleth.sso/DiscoFeed";


//////////////////// ADDITIONAL CSS CUSTOMIZATIONS ////////////////////

// To further customize the appearance of the Embedded WAYF you could
// define CSS rules for the following CSS IDs that are used within the 
// Embedded WAYF:
// #wayf_div                     - Container for complete Embedded WAYF
// #wayf_logo_div                - Container for logo
// #wayf_logo                    - Image for logo
// #wayf_intro_div               - Container of drop-down list intro label
// #wayf_intro_label             - Label of intro text
// #IdPList                      - The form element
// #user_idp                     - Select element for drop-down list
// #wayf_remember_checkbox_div   - Container of checkbox and its label
// #wayf_remember_checkbox       - Checkbox for remembering settings for session
// #wayf_remember_checkbox_label - Text of checkbox
// #wayf_submit_button           - Submit button
//
// Use these CSS IDs carefully and at own risk because future updates could
// interfere with the rules you created and the IDs may change without notice!


//-->
</script>

<script type="text/javascript" src="https://<?php echo $host ?><?php echo $path ?>/embedded-wayf.js"></script>

<noscript>
  <!-- 
  Fallback to Shibboleth DS Session Initiator for non-JavaScript users 
  Value of the target GET parameter should be set to an URL-encoded 
  absolute URL that points to a Shibboleth protected web page where the user 
  is logged in into your application.
  -->
  <p>
    <strong>Login:</strong> Javascript is not enabled for your web browser. Please use the <a href="/Shibboleth.sso/Login?target=">non-Javascript Login</a>.
  </p>
</noscript>

<!-- EMBEDDED-WAYF-END -->
