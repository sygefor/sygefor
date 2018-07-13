SYGEFOR3
========================

Qu'est-ce que Sygefor3 ?
-----------------

Sygefor3 est une solution de gestion de formations conçu par l'Association du Réseau des URFIST, puis enrichie par l'adCRFCB ainsi que le CNRS. Elle a été réalisée par [Conjecto](http://www.conjecto.com/).
L'application se présente sous la forme d'une interface de gestion privée. Une version publique de la solution permet aux stagiaires de s'inscrire aux formations.
Une API publique est également disponible. Les types d'authentification OAuth2 et Shibboleth sont intégrés à la solution.

Démonstration
-----------------

Un version de démonstration de la solution est disponible à l'adresse : http://sygefor.conjecto.com.


Captures d'écran
-----------------

<img src="https://raw.githubusercontent.com/conjecto/sygefor/master/assets/screen-dashboard.png?raw=true" title="Capture d'écran du dashboard" width="30%"/>
<img src="https://raw.githubusercontent.com/conjecto/sygefor/master/assets/screen-list-searchbar.png?raw=true" title="Capture d'écran de la vue liste filtrée via la barre de recherche" width="30%"/>
<img src="https://raw.githubusercontent.com/conjecto/sygefor/master/assets/screen-trainee.png?raw=true" title="Capture d'écran de la vue d'un stagiaire" width="30%"/>
<img src="https://raw.githubusercontent.com/conjecto/sygefor/master/assets/screen-mailing.png?raw=true" title="Capture d'écran d'un envoie d'emails" width="30%"/>
<img src="https://raw.githubusercontent.com/conjecto/sygefor/master/assets/screen-summary.png?raw=true" title="Capture d'écran de la génération des bilans" width="30%"/>
<img src="https://raw.githubusercontent.com/conjecto/sygefor/master/assets/screen-front-home.png?raw=true" title="Capture d'écran de la page d'accueil du site public" width="30%"/>
<img src="https://raw.githubusercontent.com/conjecto/sygefor/master/assets/screen-front-profile.png?raw=true" title="Capture d'écran de la partie compte du site public" width="30%"/>

Configuration requise
------------

### PHP

* version 5.3.9 minimum (sauf 5.3.16). Php7 n'est pas compatible pour le moment.
* extensions :
    * json
    * ctype
* modules :
    * pdo_mysql
    * openssl
    * apc
    * mbstring
    * curl
    * fileinfo

### Symfony2

Sygefor3 s'appuie sur Symfony 2.8.

### MySQL

version 5.0 minimum

### ElasticSearch

Sygefor3 s'appuie sur un serveur [ElasticSearch](http://www.elasticsearch.org/) qui gère l'indexation de l'ensemble
des éléments.

* version 1.4

### Unoconv

La génération des PDF lors d'un publipostage est rendue possible grâce à la librairie [Unoconv](https://github.com/dagwieers/unoconv)
qui doit donc être installée sur le serveur.

* version 0.7

### Accès interactif

Sygefor3 est livré avec un outil en ligne de commande qui permet d'automatiser certaines opérations d'installation et
de maintenance. Il faut donc un accès interactif du type SSH.

### Certificat SSL

Il est fortemment recommandé d'installer un certificat SSL et d'utiliser HTTPS pour l'ensemble des communications avec l'application.

### Shibboleth

Sygefor3 utilise la Fédération d'identité Education-Recherche de Renater pour permettre aux stagiaires de s'inscrire, au travers
du protocole Shibboleth. Il faut donc installer un Service Provider sur le serveur et le déclarer auprés de Renater :

[Installation d'un SP Shibboleth](https://services.renater.fr/federation/docs/installation/sp#test_dans_la_federation_de_test)

Installation
------------

### Prérequis

- Composer installé : http://www.coolcoyote.net/php-mysql/installation-de-composer-sous-linux-et-windows
- Openssl installé
- npm, bower, yarn installé (sudo npm install yarn bower -g)
- Visual Studio Redistributables installé pour Windows
- libssl-dev installé pour linux
- Rewrite module activé

### Le projet

- git clone https://github.com/sygefor/sygefor.git
- cd sygefor
- composer install
    - Renseigner les paramètres symfony
- yarn install
- bower install
- php app/console doctrine:database:create
- php app/console doctrine:schema:create
- php app/console doctrine:fixtures:load (pour générer quelques données initiales)
- php app/console fos:js-routing:dump
- gulp build:dist
- php app/console fos:elastica:populate
- php app/console server:run 127.0.0.1:8000
- Se rendre sur localhost:8000 avec votre navigateur
- Se connecter avec les identifiants admin/admin

### Etendre

Le coeur de Sygefor3 est intégré dans les vendors du projet. Ce coeur déclare des classes et des controlleurs abstraits. Vous devez étendre
ces classes et controlleurs pour faire fonctionner l'application.
Le bundle App intègre ces extentions. Vous pourrez comprendre comment étendre Sygefor3 en regardant ce bundle.

Vous pouvez également adapter l'interface privée de gestion en modifiant les templates AngularJS contenus dans le répertoire app/Resources/public/ng.
Le module FrontBundle intègre une version publique et allégée de Sygefor permettant aux stagiaires de s'inscrire aux différents stages.
Vous pourrez aussi retrouver un module Bilan basé sur ElasticSearch.