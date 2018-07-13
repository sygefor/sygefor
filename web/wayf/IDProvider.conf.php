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

// Category
$IDProviders['unknown'] = array (
        'Type' => 'category',
        'Name' => 'Others',
        'de' => array ('Name' => 'Andere'),
        'fr' => array ('Name' => 'Autres'),
        'it' => array ('Name' => 'Altri'),
);

?>
