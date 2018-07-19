<?php // Copyright (c) 2016, SWITCH

// Localized language strings for SWITCHwayf
// Make sure to use HTML entities instead of plain UTF-8 characters for 
// non-ASCII characters if you are using the Embedded WAYF. It could be that the
// Embedded WAYF is used on non-UTF8 web pages, which then could cause 
// encoding issues

// *********************************************************************************
// If you want locales in your own language here, please send them to aai@switch.ch
// *********************************************************************************
// ****************************
//       English, default
// ****************************

// To permanently customize locales such that they are not overwritten by updates
// of the SWITCHwayf, create a file 'custom-languages.php' and override any 
// individual locale in the $langStrings array. For example like this:
// 
// $langStrings['en']['about_federation'] = 'About Example Federation';
// $langStrings['en']['additional_info'] = 'My <b>sample HTML content</b>';
// 
//
// Set a locale to an empty string ('') in order to hide it
// Note that any string in custom-languages.php will survive updates

// In particular you might want to override these three locales or set the
// to an empty string in order to hide them if they are not needed.
$langStrings['en']['about_federation'] = 'About AAI';  // This string can be hidden by setting it to ''
$langStrings['en']['about_organisation'] = 'About SWITCH'; // This string can be hidden by setting it to ''
$langStrings['en']['additional_info'] = '<a href="http://www.switch.ch/" target="_blank">SWITCH</a> provides innovative, unique internet services for the Swiss universities and internet users.'; // This string can be hidden by setting it to ''

// Generic strings
$langStrings['en']['faq'] = 'FAQ'; // This string can be hidden by setting it to ''
$langStrings['en']['help'] = 'Help';// This string can be hidden by setting it to ''
$langStrings['en']['privacy'] = 'Data Privacy'; // This string can be hidden by setting it to ''
$langStrings['en']['title'] = 'Organisation Selection';
$langStrings['en']['header'] = 'Select your organisation'; 
$langStrings['en']['make_selection'] = 'You must select an organisation.';
$langStrings['en']['settings'] = 'Default organisation for this web browser';
$langStrings['en']['permanent_select_header'] = 'Permanently set your organisation';
$langStrings['en']['permanent_cookie'] = 'On this page you can set a <strong>default organisation</strong> for this web browser. Setting a default organisation will henceforth redirect you directly to your organisation when you access certain services that require login. Don\'t use this feature if you use several user accounts from multiple organisations.';
$langStrings['en']['permanent_cookie_notice'] = 'The organisation selected by default will be:';
$langStrings['en']['permanent_cookie_note'] = 'You can reset this default setting on the page: %s';
$langStrings['en']['delete_permanent_cookie_button'] = 'Reset';
$langStrings['en']['goto_sp'] = 'Save and continue';
$langStrings['en']['permanently_remember_selection'] = 'Remember selection permanently and bypass this step from now on.';
$langStrings['en']['confirm_permanent_selection'] = 'Are you sure you want to make the selected organisation your default organisation? Don\'t proceed if you have several user accounts from multiple organisations.';
$langStrings['en']['save_button'] = 'Save';
$langStrings['en']['access_host'] = 'In order to access the service %s please select or search the organisation you are affiliated with.';
$langStrings['en']['select_idp'] = 'Select the organisation you are affiliated with.';
$langStrings['en']['search_idp'] = 'Enter the name of the organisation you are affiliated with...';
$langStrings['en']['remember_selection'] = 'Remember selection for this web browser session.';
$langStrings['en']['invalid_user_idp'] = 'There may be an error in the data you just submitted.<br>The value of your input <code>\'%s\'</code> is invalid.<br>Only the following values are allowed:';
$langStrings['en']['contact_assistance'] = 'Please contact <a href="mailto:%s">%s</a> for assistance.';
$langStrings['en']['no_arguments'] = 'No arguments received!';
$langStrings['en']['arguments_missing'] = 'The web server received an invalid query because there are some arguments missing<br>The following arguments were received:';
$langStrings['en']['valid_request_description'] = 'A valid request needs at least the arguments <code>shire</code> and <code>target</code> with valid values. Optionally the arguments <code>providerID</code>, <code>origin</code> and <code>redirect</code> can be supplied to automtically redirect the web browser to an organisation and to do that automatically for the current web browser session';
$langStrings['en']['valid_saml2_request_description'] = 'A valid SAML2 request needs at least the arguments <code>entityID</code> and <code>return</code> with valid values. Instead of the <code>return</code> argument, metadata for the Service Provider can include a <code>DiscoveryResponse</code> endpoint. Optionally the arguments <code>isPassive</code>, <code>policy</code> and <code>returnIDParam</code> can be supplied to automtically redirect the web browser to an organisation and to do that automatically for the current web browser session';
$langStrings['en']['invalid_query'] = 'Error: Invalid Query';
$langStrings['en']['select_button'] = 'Select';
$langStrings['en']['login'] = 'Login';
$langStrings['en']['login_with'] = 'Login with:';
$langStrings['en']['other_federation'] = 'From other federations';
$langStrings['en']['logged_in'] = 'You are already authenticated. <a href=\"%s\">Proceed</a>.';
$langStrings['en']['most_used'] = 'Most frequently used organisations';
$langStrings['en']['invalid_return_url'] = 'The return URL <code>\'%s\'</code> is not a valid URL.';
$langStrings['en']['unverified_return_url'] = 'The return URL <code>\'%s\'</code> could not be verified for Service Provider <code>\'%s\'</code>.';
$langStrings['en']['unknown_sp'] = 'The Service Provider <code>\'%s\'</code> could not be found in metadata and is therefore unknown.';
$langStrings['en']['no_idp_found'] = 'No organisation found for this search text';
$langStrings['en']['no_idp_available'] = 'No organisation available';
$langStrings['en']['last_used'] = 'Last used';


// ****************************
//          Deutsch
// ****************************

// Read note on line 16 how to properly customize locales so that they survive updates
$langStrings['de']['about_federation'] = '&Uuml;ber AAI';  // This string can be hidden by setting it to ''
$langStrings['de']['about_organisation'] = '&Uuml;ber SWITCH';  // This string can be hidden by setting it to ''
$langStrings['de']['additional_info'] = '<a href="http://www.switch.ch/" target="_blank">SWITCH</a> erbringt innovative, einzigartige Internet-Dienstleistungen f&uuml;r die Schweizer Hochschulen und Internetbenutzer.';  // This string can be hidden by setting it to ''

// Generic strings
$langStrings['de']['faq'] = 'FAQ';  // This string can be hidden by setting it to ''
$langStrings['de']['help'] = 'Hilfe'; // This string can be hidden by setting it to ''
$langStrings['de']['privacy'] = 'Datenschutz'; // This string can be hidden by setting it to ''
$langStrings['de']['title'] = 'Auswahl der Organisation';
$langStrings['de']['header'] = 'Organisation ausw&auml;hlen';
$langStrings['de']['make_selection'] = 'Sie m&uuml;ssen eine Organisation ausw&auml;hlen';
$langStrings['de']['settings'] = 'Standard Organisation f&uuml;r diesen Webbrowser';
$langStrings['de']['permanent_select_header'] = 'Organisation speichern';
$langStrings['de']['permanent_cookie'] = 'Auf dieser Seite k&ouml;nnen Sie die <strong>Standardeinstellung Ihrer Organisation</strong> f&uuml;r diesen Webbrowser dauerhaft zu speichern. Sie werden darauf beim Zugriff auf einige Dienste, welche eine Anmeldung ben&ouml;tigen, jedes Mal direkt zur Loginseite Ihrer Organisation weitergeleitet. Dies wird jedoch nicht empfohlen falls sie mehrere Benutzerkonnten von verschiedenen Organisationen verwenden.';
$langStrings['de']['permanent_cookie_notice'] = 'Die standardm&auml;ssig ausgew&auml;hlte Organisation wird sein:';
$langStrings['de']['permanent_cookie_note'] = 'Sie k&ouml;nnen diese Standard-Einstellung zur&uuml;cksetzen auf der Seite: %s';
$langStrings['de']['delete_permanent_cookie_button'] = 'Zur&uuml;cksetzen';
$langStrings['de']['goto_sp'] = 'Speichern und weiter';
$langStrings['de']['permanently_remember_selection'] = 'Auswahl permanent speichern und diesen Schritt von jetzt an &uuml;berspringen.';
$langStrings['de']['confirm_permanent_selection'] = 'Sind Sie sicher, dass Sie die gew&auml;hlte Organisation als Standard-Organisation speichern wollen? Dies ist nicht empfehlenswert, wenn Sie Benutzerkonten von mehreren Organisationen besitzen.';
$langStrings['de']['save_button'] = 'Speichern';
$langStrings['de']['access_host'] = 'Um auf den Dienst %s zuzugreifen, w&auml;hlen oder suchen Sie bitte die Organisation, der Sie angeh&ouml;ren.';
$langStrings['de']['select_idp'] = 'W&auml;hlen Sie die Organisation aus, der Sie angeh&ouml;ren.';
$langStrings['de']['search_idp'] = 'Geben Sie den Namen der Organisation ein, der Sie angeh&ouml;ren...';
$langStrings['de']['remember_selection'] = 'Auswahl f&uuml;r die laufende Webbrowser Sitzung speichern.';
$langStrings['de']['invalid_user_idp'] = 'M&ouml;glicherweise sind die &uuml;bermittelten Daten fehlerhaft.<br>Der Wert der Eingabe <code>\'%s\'</code> ist ung&uuml;ltig.<br>Es sind ausschliesslich die folgenden Wert erlaubt:';
$langStrings['de']['contact_assistance'] = 'F&uuml;r Unterst&uuml;tzung und Hilfe, kontaktieren Sie bitte <a href="mailto:%s">%s</a>.';
$langStrings['de']['no_arguments'] = 'Keine Argumente erhalten!';
$langStrings['de']['arguments_missing'] = 'Der Webserver hat eine fehlerhafte Anfrage erhalten da einige Argumente in der Anfrage fehlen.<br>Folgende Argumente wurden empfangen:';
$langStrings['de']['valid_request_description'] = 'Eine g&uuml;ltige Anfrage muss mindestens die Argumente <code>shire</code> und <code>target</code> enthalten. Zus&auml;tzlich k&ouml;nnen die Argumente <code>providerID</code>, <code>origin</code> und <code>redirect</code> benutzt werden um den Webbrowser automatisch an die gew&auml;hlte Organisation weiter zu leiten und um eine Organisation f&uuml;r l&auml;ngere Zeit als Standardorganisation zu speichern.';
$langStrings['de']['valid_saml2_request_description'] = 'Eine g&uuml;ltige Anfrage muss mindestens die Argumente <code>entityID</code> und <code>return</code> enthalten. Anstatt dem Argument <code>return</code> k&ouml;nnen die Metadaten f&uuml;r den Service Provider einen <code>DiscoveryResponse</code> Endpunkt enthalten. Zus&auml;tzlich k&ouml;nnen die Argumente <code>isPassive</code>, <code>policy</code> und <code>returnIDParam</code> benutzt werden um den Webbrowser automatisch an zur gew&auml;hlten Organisation weiter zu leiten und um eine Organisation f&uuml;r l&auml;ngere Zeit als Standardorganisation zu speichern.';
$langStrings['de']['invalid_query'] = 'Error: Fehlerhafte Anfrage';
$langStrings['de']['select_button'] = 'Ausw&auml;hlen';
$langStrings['de']['login'] = 'Anmelden';
$langStrings['de']['login_with'] = 'Anmelden &uuml;ber:';
$langStrings['de']['other_federation'] = 'Von anderen F&ouml;derationen';
$langStrings['de']['logged_in'] = 'Sie sind bereits angemeldet. <a href=\"%s\">Weiter</a>.';
$langStrings['de']['most_used'] = 'Meist benutzte Organisationen';
$langStrings['de']['invalid_return_url'] = 'Die return URL <code>\'%s\'</code> ist keine g&uuml;tige URL.';
$langStrings['de']['unverified_return_url'] = 'Die return URL <code>\'%s\'</code> ist nicht g&uuml;ltig f&uuml;r den Service Provider <code>\'%s\'</code>.';
$langStrings['de']['unknown_sp'] = 'Der Service Provider <code>\'%s\'</code> konnte nicht in den Metadaten gefunden werden und ist deshalb unbekannt.';
$langStrings['de']['no_idp_found'] = 'Keine Organisation gefunden f&uuml;r diesen Suchtext';
$langStrings['de']['no_idp_available'] = 'Keine Organisation verf&uuml;gbar';
$langStrings['de']['last_used'] = 'Zuletzt benutzt';


// ****************************
//          French
// ****************************

// Read note on line 16 how to properly customize locales so that they survive updates
$langStrings['fr']['about_federation'] = '&Agrave; propos de l\'AAI'; // This string can be hidden by setting it to ''
$langStrings['fr']['about_organisation'] = '&Agrave; propos de SWITCH'; // This string can be hidden by setting it to ''
$langStrings['fr']['additional_info'] = '<a href="http://www.switch.ch/" target="_blank">SWITCH</a> fournit des prestations innovantes et uniques pour les hautes &eacute;coles suisses et les utilisateurs d\'Internet.'; // This string can be hidden by setting it to ''

// Generic strings
$langStrings['fr']['faq'] = 'FAQ'; // This string can be hidden by setting it to ''
$langStrings['fr']['help'] = 'Aide';// This string can be hidden by setting it to ''
$langStrings['fr']['privacy'] = 'Protection des donn&eacute;es';// This string can be hidden by setting it to ''
$langStrings['fr']['title'] = 'S&eacute;lection de votre &eacute;tablissement';
$langStrings['fr']['header'] = 'S&eacute;lectionnez votre &eacute;tablissement';
$langStrings['fr']['make_selection'] = 'Vous devez s&eacute;lectionner un &eacute;tablissement valide.';
$langStrings['fr']['settings'] = '&Eacute;tablissement par d&eacute;faut pour ce navigateur';
$langStrings['fr']['permanent_select_header'] = 'S&eacute;lection d\'un &eacute;tablissement de fa&ccedil;on permanente';
$langStrings['fr']['permanent_cookie'] = 'Sur cette page vous pouvez d&eacute;finir un <strong>&eacute;tablissement par d&eacute;faut</strong> pour ce navigateur. En d&eacute;finissant un &eacute;tablissement par d&eacute;faut, vous serez automatiquement redirig&eacute; vers cet &eacute;tablissement lorsque vous acc&eacute;dez &agrave; une ressource. N\'utilisez pas cette fonction si vous avez plusieurs identit&eacute;s dans plusieurs &eacute;tablissements.';
$langStrings['fr']['permanent_cookie_notice'] = 'Par d&eacute;faut l\'&eacute;tablissement sera : ';
$langStrings['fr']['permanent_cookie_note'] = 'Vous pouvez r&eacute;initialiser la propri&eacute;t&eacute; par d&eacute;faut en allant &agrave; l\'adresse: %s';
$langStrings['fr']['delete_permanent_cookie_button'] = 'R&eacute;initialiser';
$langStrings['fr']['goto_sp'] = 'Sauver et continuez';
$langStrings['fr']['permanently_remember_selection'] = 'Se souvenir de mon choix d&eacute;finitivement et contourner cette &eacute;tape &agrave; partir de maintenant.';
$langStrings['fr']['confirm_permanent_selection'] = '&Ecirc;tes-vous s&ucirc;r de ce choix d&rsquo;&eacute;tablissement par d&eacute;faut ? N&rsquo;utilisez pas cette fonctionnalit&eacute; si vous poss&eacute;der des comptes dans plusieurs &eacute;tablissements.';
$langStrings['fr']['save_button'] = 'Sauver';
$langStrings['fr']['access_host'] = 'Pour acc&eacute;der au service %s s&eacute;lectionnez ou cherchez l\'&eacute;tablissement auquel vous appartenez.';
$langStrings['fr']['select_idp'] = 'Veuillez s&eacute;lectionner l\'&eacute;tablissement auquel vous appartenez.';
$langStrings['fr']['search_idp'] = 'Veuillez entrer le nom de votre &eacute;tablissement...';
$langStrings['fr']['remember_selection'] = 'Se souvenir de mon choix pour cette session.';
$langStrings['fr']['invalid_user_idp'] = 'Une erreur s\'est produite.<br>La valeur de votre donn&eacute;e <code>\'%s\'</code> n\'est pas valide.<br>Seules ces valeurs sont admises :';
$langStrings['fr']['contact_assistance'] = 'Contactez le support <a href="mailto:%s">%s</a> si l\'erreur persiste.';
$langStrings['fr']['no_arguments'] = 'Aucun param&egrave;tre re&ccedil;u !';
$langStrings['fr']['arguments_missing'] = 'La requ&ecirc;te n\'est pas valide, certains param&egrave;tres sont manquants.<br>Les param&egrave;tres suivants ont &eacute;t&eacute; re&ccedil;us :';
$langStrings['fr']['valid_request_description'] = 'Une requ&ecirc;te valide doit contenir au moins les param&egrave;tres <code>shire</code> et <code>target</code>. Les param&egrave;tres optionnels <code>providerID</code>, <code>origin</code> et <code>redirect</code> peuvent &ecirc;tre utilis&eacute;s pour rediriger automatiquement le navigateur vers un &eacute;tablissement.';
$langStrings['fr']['valid_saml2_request_description'] = 'Une requ&ecirc;te valide doit contenir au moins les param&egrave;tres <code>entityID</code> et <code>return</code>. Au lieu de param&egrave;tre <code>return</code>, metadata pour ce Service Provider peut contenir un URL pour le <code>DiscoveryResponse</code>. Les param&egrave;tres optionnel <code>isPassive</code>, <code>policy</code> et <code>returnIDParam</code> peuvent &ecirc;tre utilis&eacute;s pour rediriger automatiquement le navigateur vers un &eacute;tablissement.';
$langStrings['fr']['invalid_query'] = 'Erreur : La requ&ecirc;te n\'est pas valide';
$langStrings['fr']['select_button'] = 'S&eacute;lection';
$langStrings['fr']['login'] = 'Connexion';
$langStrings['fr']['login_with'] = 'Se connecter avec:';
$langStrings['fr']['other_federation'] = 'D\'autres f&eacute;derations';
$langStrings['fr']['logged_in'] = 'Vous &ecirc;tes d&eacute;j&agrave; authentifi&eacute;. <a href=\"%s\">Continuez</a>.';
$langStrings['fr']['invalid_return_url'] = 'L\'URL de retour <code>\'%s\'</code> n\'est pas une URL valide.';
$langStrings['fr']['unverified_return_url'] = 'L\'URL de retour <code>\'%s\'</code> ne peut pas &ecirc;tre v&eacute;rifi&eacute; pour le fournisseur de service <code>\'%s\'</code>.';
$langStrings['fr']['unknown_sp'] = 'Le fournisseur de service <code>\'%s\'</code> ne pouvait pas &ecirc;tre trouv&eacute; dans les meta donn&eacute;es et il est donc inconnu.';
$langStrings['fr']['no_idp_found'] = 'Aucun &eacute;tablissement trouv&eacute; pour ce texte recherch&eacute;';
$langStrings['fr']['no_idp_available'] = 'Aucun &eacute;tablissement disponible';
$langStrings['fr']['most_used'] = '&Eacute;tablissements les plus utilis&eacute;s';
$langStrings['fr']['last_used'] = 'Derni&egrave;rement utilis&eacute;s';


// ****************************
//          Italian
// ****************************

// Read note on line 16 how to properly customize locales so that they survive updates
$langStrings['it']['about_federation'] = 'Informazioni su AAI'; // This string can be hidden by setting it to ''
$langStrings['it']['about_organisation'] = 'Informazioni su SWITCH'; // This string can be hidden by setting it to ''
$langStrings['it']['additional_info'] = '<a href="http://www.switch.ch/" target="_blank">SWITCH</a> eroga servizi Internet innovativi e unici per le scuole universitarie svizzere e per gli utenti di Internet.'; // This string can be hidden by setting it to ''

// Generic strings
$langStrings['it']['faq'] = 'FAQ'; // This string can be hidden by setting it to ''
$langStrings['it']['help'] = 'Aiuto'; // This string can be hidden by setting it to ''
$langStrings['it']['privacy'] = 'Protezione dei dati'; // This string can be hidden by setting it to ''
$langStrings['it']['title'] = 'Selezione della vostra organizzazione';
$langStrings['it']['header'] = 'Selezioni la sua organizzazione';
$langStrings['it']['make_selection'] = 'Per favore, scelga una valida organizzazione.';
$langStrings['it']['settings'] = 'Organizzazione predefinita per questo web browser.';
$langStrings['it']['permanent_select_header'] = 'Salvare l\'organizzazione.';
$langStrings['it']['permanent_cookie'] = 'In questa pagina pu&ograve; impostare la <strong>organizzazione predefinita</strong> per questo web browser. Impostare una organizzazione predefinita consentir&agrave; al suo web browser di venir reindirizzato alla sua organizzazione automaticamente ogni qual volta lei tenter&agrave; di accedere a risorse per le quali necessita un\'autentificazione. Non &egrave; da impostare se lei possiede e usa correntemente differenti account.';
$langStrings['it']['permanent_cookie_notice'] = 'L\'impostazione predefinita &egrave;:';
$langStrings['it']['permanent_cookie_note'] = 'Pu&ograve; cambiare la sua impostazione predefinita sulla pagina: %s';
$langStrings['it']['delete_permanent_cookie_button'] = 'Cancella';
$langStrings['it']['goto_sp'] = 'Salvare e proseguire';
$langStrings['it']['permanently_remember_selection'] = 'Salvare la scelta permanentemente e non passare pi&ugrave; per il WAYF.';
$langStrings['it']['confirm_permanent_selection'] = 'E\' sicuro di voler impostare l\'organizzazione selezionata come sua organizzazione predefinita? Non &egrave; da impostare se usa regolarmente diversi account.';
$langStrings['it']['save_button'] = 'Salva';
$langStrings['it']['access_host'] = 'Per poter accedere alla risorsa %s per favore selezioni o cerchi l\'organizzazione con la quale &egrave; affiliato.';
$langStrings['it']['select_idp'] = 'Selezioni l\'organizzazione con la quale &egrave; affiliato.';
$langStrings['it']['search_idp'] = 'Digitare il nome dell\'organizzazione con cui e\' affiliato...';
$langStrings['it']['remember_selection'] = 'Ricorda la selezione per questa sessione.';
$langStrings['it']['invalid_user_idp'] = 'Errore nei parametri pervenuti.<br>Il valore del parametro <code>\'%s\'</code> non &#143; valido.<br>Solo i seguenti valori sono ammessi:';
$langStrings['it']['contact_assistance'] = 'Se l\' errore persiste, si prega di contattare <a href="mailto:%s">%s</a>.';
$langStrings['it']['no_arguments'] = 'Parametri non pervenuti!';
$langStrings['it']['arguments_missing'] = 'La richiesta non &egrave; valida per la mancanza di alcuni parametri. <br>I seguenti parametri sono stati ricevuti:';
$langStrings['it']['valid_request_description'] = 'Una richiesta valida &egrave; deve contenere almeno i parametri <code>shire</code> e <code>target</code>. I parametri opzionali <code>providerID</code>, <code>origin</code> e <code>redirect</code> possono essere utilizzati per ridirigere automaticamente il browser web verso una organizzazione.';
$langStrings['it']['valid_saml2_request_description'] = 'Una richiesta valida &egrave; deve contenere almeno i parametri <code>entityID</code> e <code>return</code>. I parametri opzionali <code>isPassive</code>, <code>policy</code> e <code>returnIDParam</code> possono essere utilizzati per ridirigere automaticamente il browser web verso una organizzazione.';
$langStrings['it']['invalid_query'] = 'Errore: Richiesta non Valida';
$langStrings['it']['select_button'] = 'Seleziona';
$langStrings['it']['login'] = 'Login';
$langStrings['it']['login_with'] = 'Login con:';
$langStrings['it']['other_federation'] = 'Di altra federaziones';
$langStrings['it']['logged_in'] = 'Lei &egrave; gi&agrave; autenticato. <a href=\"%s\">Proseguire</a>.';
$langStrings['it']['most_used'] = 'Organizzaziones utilizzate pi&ugrave; spesso';


// ****************************
//          Portuguese
// ****************************

// Read note on line 16 how to properly customize locales so that they survive updates
$langStrings['pt']['about_federation'] = 'Sobre AAI'; // This string can be hidden by setting it to ''
$langStrings['pt']['about_organisation'] = 'Sobre a SWITCH'; // This string can be hidden by setting it to ''
$langStrings['pt']['additional_info'] = 'A SWITCH foundation &eacute; uma institui&ccedil;&atilde;o gere e opera a rede de investiga&ccedil;&atilde;o e ensino sui&ccedil;a por forma a garantir conectividade de alto desempenho &agrave; Internet e a redes de I&amp;D globais para o beneficio de uma educa&ccedil;&atilde;o superior na sui&ccedil;a'; // This string can be hidden by setting it to ''

// Generic strings
$langStrings['pt']['faq'] = 'FAQ'; // This string can be hidden by setting it to ''
$langStrings['pt']['help'] = 'Ajuda'; // This string can be hidden by setting it to ''
$langStrings['pt']['privacy'] = 'Privacidade'; // This string can be hidden by setting it to ''
$langStrings['pt']['title'] = 'Selec&ccedil;&atilde;o de institui&ccedil;&atilde;o';
$langStrings['pt']['header'] = 'Seleccione a sua institui&ccedil;&atilde;o';
$langStrings['pt']['make_selection'] = 'Dever&aacute; seleccionar uma institui&ccedil;&atilde;o V&aacute;lida';
$langStrings['pt']['settings'] = 'Institui&ccedil;&atilde;o por defeito para este web browser';
$langStrings['pt']['permanent_select_header'] = 'Defina permanentemente a sua institui&ccedil;&atilde;o';
$langStrings['pt']['permanent_cookie'] = 'Nesta p&aacute;gina poder&aacute; definir a sua <strong>institui&ccedil;&atilde;o</strong> para este web browser. Defenir uma institui&ccedil;&atilde;o levar&aacute; a que seja redireccionado directamente para a sua institui&ccedil;&atilde;o aquando do acesso de recursos. N&atilde;o use esta funcionalidade se possuir v&aacute;rias contas.';
$langStrings['pt']['permanent_cookie_notice'] = 'A configura&ccedil;&atilde;o &ecute;:';
$langStrings['pt']['permanent_cookie_note'] = 'Poder&aacute; efectuar um reset &agrave;s configura&ccedil;&otilde;es no URL %s';
$langStrings['pt']['delete_permanent_cookie_button'] = 'Reset';
$langStrings['pt']['goto_sp'] = 'Salve e continue';
$langStrings['pt']['permanently_remember_selection'] = 'Memorize a sua selec&ccedil;&atilde;o permanentemente e passe o mecanismo WAYF apartir de agora.';
$langStrings['pt']['confirm_permanent_selection'] = 'Tem a certeza que pretende seleccionar a op&ccedil;&atilde;o escolhida como a sua institui&ccedil;&atilde;o? N&atilde;o seleccione se possui v&aacute;rias contas.';
$langStrings['pt']['save_button'] = 'Guarde';
$langStrings['pt']['access_host'] = 'No sentido de aceder ao recurso em <code>\'%s\'</code> dever&aacute; autenticar-se.';
$langStrings['pt']['select_idp'] = 'Seleccione a sua institui&ccedil;&atilde;o.';
$langStrings['pt']['remember_selection'] = 'Memorize a selec&ccedil;&atilde;o para esta sess&atilde;o.';
$langStrings['pt']['invalid_user_idp'] = 'Poder&aacute; existir um erro nos dados que enviou.<br>Os valores enviados <code>\'%s\'</code> s&atilde;o inv&aacute;lidos.<br>Apenas os valores seguintes s&atilde;o permitidos:';
$langStrings['pt']['contact_assistance'] = 'Contacte <a href="mailto:%s">%s</a> para assistencia.';
$langStrings['pt']['no_arguments'] = 'Nenhum argumento recebido!';
$langStrings['pt']['arguments_missing'] = 'O servidor web recebeu uma query inv&acute;lida devido &agrave; falta de alguns argumentos. Foram recebidos os seguintes argumentos:';
$langStrings['pt']['valid_request_description'] = 'Um pedido v&acute;lido necessita de pelo menos dos atributos <code>shire</code> e <code>target</code> com valores v&acute;lidos. Opcionalmente os argumentos <code>providerID</code>, <code>origin</code> e <code>redirect</code> podem ser fornecidos para de uma forma autom&acute;tica redireccionar o browser do utilizador.';
$langStrings['pt']['invalid_query'] = 'Erro: Query Invalida';
$langStrings['pt']['select_button'] = 'Seleccione';
$langStrings['pt']['login'] = 'Autenticar';
$langStrings['pt']['login_with'] = 'Autenticar em:';
$langStrings['pt']['other_federation'] = 'Outra Federa&ccedil;Atilde;o';
$langStrings['pt']['logged_in'] = 'J&aacute; se encontra autenticado. <a href=\"%s\">Continue</a>.';
$langStrings['pt']['most_used'] = 'Institui&ccedil;&atilde;o mais utilizada';


// ****************************
//          Japanese
// ****************************

// Read note on line 16 how to properly customize locales so that they survive updates
$langStrings['ja']['about_federation'] = 'フェデレーションとは'; // This string can be hidden by setting it to ''
$langStrings['ja']['about_organisation'] = 'SWITCHとは'; // This string can be hidden by setting it to ''
$langStrings['ja']['additional_info'] = '<a href="http://www.switch.ch/" target="_blank">SWITCH</a>は革新的で唯一無二のインターネットサービスをスイスの大学およびインターネットの利用者に提供します．'; // This string can be hidden by setting it to ''

// Generic strings
$langStrings['ja']['faq'] = 'FAQ'; // This string can be hidden by setting it to ''
$langStrings['ja']['help'] = 'ヘルプ'; // This string can be hidden by setting it to ''
$langStrings['ja']['privacy'] = 'プライバシー'; // This string can be hidden by setting it to ''
$langStrings['ja']['title'] = '所属機関選択';
$langStrings['ja']['header'] = '所属機関の選択';
$langStrings['ja']['make_selection'] = '所属機関を選んで下さい';
$langStrings['ja']['settings'] = 'このブラウザで利用するデフォルト所属機関';
$langStrings['ja']['permanent_select_header'] = '所属機関情報の保存';
$langStrings['ja']['permanent_cookie'] = 'このブラウザで利用する<strong>デフォルト所属機関</strong>を保存できます．この設定により，サービスで機関認証を選択した場合に，再び所属機関のIdPを選択することなく，直接機関のIdPにリダイレクトされます．複数の機関のアカウントを使い分ける場合この機能を利用しないで下さい．';
$langStrings['ja']['permanent_cookie_notice'] = '現在セット中のデフォルト所属機関は:';
$langStrings['ja']['permanent_cookie_note'] = '次のURLにアクセスすることで，デフォルトセッティングをリセットできます: %s';
$langStrings['ja']['delete_permanent_cookie_button'] = 'リセット';
$langStrings['ja']['goto_sp'] = '保存して続行';
$langStrings['ja']['permanently_remember_selection'] = '選択した所属機関を保存して今後この画面をスキップする';
$langStrings['ja']['confirm_permanent_selection'] = '選択した機関をデフォルト所属機関としてもよいですか？　複数の機関のアカウントを使い分ける場合この機能を利用しないで下さい．';
$langStrings['ja']['save_button'] = '保存';
$langStrings['ja']['access_host'] = 'サービス %s を利用するために所属機関を選択もしくは入力してください';
$langStrings['ja']['select_idp'] = '所属している機関を選択';
$langStrings['ja']['search_idp'] = '所属している機関を入力...';
$langStrings['ja']['remember_selection'] = 'ブラウザ起動中は自動ログイン';
$langStrings['ja']['invalid_user_idp'] = '入力したIdPの情報（<code>\'%s\'</code>）に誤りがあります<br>以下の値のみが入力可能です:';
$langStrings['ja']['contact_assistance'] = '問い合わせ先：<a href="mailto:%s">%s</a>';
$langStrings['ja']['no_arguments'] = '引数が送られてきませんでした';
$langStrings['ja']['arguments_missing'] = 'ウェブサーバーが無効なクエリを受け付けました．いくつかの必要な引数が不足しています．<br>以下の引数を受け付けました:';
$langStrings['ja']['valid_request_description'] = '有効なリクエストでは少なくとも，<code>shire</code>と<code>target</code>の適正な値を必要とします．オプショナルな引数である<code>providerID</code>，<code>origin</code>や<code>redirect</code>を送信することにより，ウェブブラウザを所属機関IdPに自動的にリダイレクトさせることができます．';
$langStrings['ja']['valid_saml2_request_description'] = '有効なSAML2のリクエストでは少なくとも，<code>entityID</code>と<code>return</code>の適正な値を必要とします．<code>return</code>の代わりにSPメタデータに<code>DiscoveryResponse</code>エンドポイントを含めても良いです．オプショナルな引数である<code>isPassive</code>, <code>policy</code>や<code>returnIDParam</code>を送信することにより，ウェブブラウザを所属機関IdPに自動的にリダイレクトさせることができます．';
$langStrings['ja']['invalid_query'] = 'エラー: 無効なクエリです';
$langStrings['ja']['select_button'] = '選択';
$langStrings['ja']['login'] = '選択';
$langStrings['ja']['login_with'] = '所属機関:';
$langStrings['ja']['other_federation'] = '他のフェデレーションから';
$langStrings['ja']['logged_in'] = '認証済 <a href=\"%s\">進む</a>.';
$langStrings['ja']['most_used'] = '最もよく利用されている機関';
$langStrings['ja']['invalid_return_url'] = 'リターンURL <code>\'%s\'</code> が不正です';
$langStrings['ja']['unverified_return_url'] = 'リターンURL <code>\'%s\'</code> はSP <code>\'%s\'</code> のものとみなされません';
$langStrings['ja']['unknown_sp'] = 'SP <code>\'%s\'</code> はメタデータに存在しないので不明です';
$langStrings['ja']['no_idp_found'] = 'この検索キーでは機関が見つかりません';
$langStrings['ja']['no_idp_available'] = '利用できる機関がありません';
$langStrings['ja']['last_used'] = '前回利用';
?>