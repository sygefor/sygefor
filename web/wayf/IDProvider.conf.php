<?php // Copyright (c) 2016, SWITCH

// WAYF Identity Provider Configuration file

// Find below some example entries of Identity Providers, categories and 
// cascaded WAYFs
// The keys of $IDProviders must correspond to the entityId of the 
// Identity Providers or a unique value in case of a cascaded WAYF/DS or 
// a category. In the case of a category, the key must correspond to the the 
// Type value of Identity Provider entries.
// The sequence of IdPs and SPs play a role. No sorting is done.
// 
// Please read the file DOC for information on the format of the entries
//
//// Category
//$IDProviders['university'] = array (
//        'Type' => 'category',
//        'Name' => 'Universities',
//);
//
//
//$IDProviders['https://test-uni1.example.org/idp/shibboleth'] = array(
//        'SSO' => 'https://test-uni1.example.org/idp/profile/Shibboleth/SSO',
//        'Name' => 'Test University 1',
//        'Type' => 'university',
//);
//
//$IDProviders['https://test-uni2.example.org/idp/shibboleth'] = array(
//        'SSO' => 'https://test-uni1.example.org/idp/profile/Shibboleth/SSO',
//        'Name' => 'Test Universit&auml;t 2',
//        'de' => array ('Name' => 'Test Universit&auml;t 2'),
//        'en' => array ('Name' => 'Test University 2'),
//        'Type' => 'university',
//        'IP' => array ('193.166.2.0/24','129.132.0.0/16'),
//);
//
//$IDProviders['https://test-uni3.example.org/idp/shibboleth'] = array(
//        'SSO' => 'https://test-uni3.example.org/idp/profile/Shibboleth/SSO',
//        'Name' => 'Test University 3',
//        'Type' => 'university',
//        'Realm' => 'example.org',
//        'en' => array ('Keywords' => 'Zurich Irchel+Park'),
//);
//
//
//// Category
//$IDProviders['vho'] = array (
//        'Type' => 'category',
//        'Name' => 'Virtual Home Organizations',
//);
//
//// An example of a configuration with multiple network blocks and multiple languages
//$IDProviders['https://vho.example.org/idp/shibboleth'] = array (
//        'Type' => 'vho',
//        'Name' => 'Virtual Home Organisation',
//        'en' => array (
//            'Name' => 'Virtual Home Organisation',
//            'Keywords','Zurich Switzerland',
//            ),
//        'de' => array (
//            'Name' => 'Virtuelle Home Organisation',
//            'Keywords','Z�rich Schweiz',
//            ),
//        'fr' => array ('Name' => 'Home Organisation Virtuelle'),
//        'it' => array ('Name' => 'Virtuale Home Organisation'),
//        'IP' => array ('130.59.6.0/16','127.0.0.0/24'),
//        'SSO' => 'https://vho.example.org/idp/profile/Shibboleth/SSO',
//);
//
//// Example of an IDP you want not to be displayed when IDPs are parsed from
//// a metadata file and SAML2MetaOverLocalConf is set to false
////$IDProviders['https://test-uni3.example.org/idp/shibboleth'] = '-';


// Category
$IDProviders['unknown'] = array (
        'Type' => 'category',
        'Name' => 'Others',
        'de' => array ('Name' => 'Andere'),
        'fr' => array ('Name' => 'Autres'),
        'it' => array ('Name' => 'Altri'),
);

?>
