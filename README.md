SYGEFOR3
========================

Qu'est-ce que Sygefor3 ?
-----------------

Sygefor3 est une solution de gestion de formations conçu par l'Association du Réseau des URFIST et réalisée par [Conjecto](http://www.conjecto.com/). L'application 
se présente sous la forme d'une interface de gestion privée et d'une API publique avec authentification OAuth2.

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

Configuration requise
------------

### PHP

* version 5.3.9 minimum (sauf 5.3.16)
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

- version 1.4
 - Répertoire pour Debian : deb http://packages.elasticsearch.org/elasticsearch/1.4/debian stable main
 - Répertoire pour CentOS : voir (ajouter le fichier external_conf/elasticsearch.repo dans /etc/yum.repos.d/)
 - apt-get/yum update
 - apt-get/yum install elasticsearch
 - service elasticsearch start
 - update-rc.d elasticsearch defaults

### Unoconv

- La génération des PDF lors d'un publipostage est rendue possible grâce à la librairie [Unoconv](https://github.com/dagwieers/unoconv)
qui doit donc être installée sur le serveur.
    - yum/apt-get install unoconv
    - Mettre à jour vers la [version 0.7](https://gist.github.com/janeklb/657e119b2ce3d0138b42e6720f248e09)

### Accès interactif

Sygefor3 est livré avec un outil en ligne de commande qui permet d'automatiser certaines opérations d'installation et 
de maintenance. Il faut donc un accès interactif du type SSH.

### Certificat SSL

Il est fortemment recommandé d'installer un certificat SSL et d'utiliser HTTPS pour l'ensemble des communications avec l'application.

### Shibboleth

Sygefor3 utilise la Fédération d'identité Education-Recherche de Renater pour permettre aux stagiaires de s'inscrire, au travers
du protocole Shibboleth. Il faut donc installer un Service Provider sur le serveur et le déclarer auprés de Renater :

[Installation d'un SP Shibboleth](https://services.renater.fr/federation/docs/installation/sp#test_dans_la_federation_de_test)

Vous pouvez utiliser le script d'installation de Shibboleth (dans shell/installShib)

Installation
------------

### Prérequis

- Composer installé : http://www.coolcoyote.net/php-mysql/installation-de-composer-sous-linux-et-windows
- Openssl installé
- npm installé (sudo apt-get/yum install npm)
    - Si vous rencontrez des problèmes avec le npm install, vous pouvez installer la version 0.12.17 de Node.js (sudo npm install n -g && sudo n 0.12.17)
- bower installé (sudo npm install bower -g)
- gulp installé (sudo npm install gulp@3.8.0 -g)
- Visual Studio Redistributables installé sur Windows
- wkhtmltopdf installé (pour générer des pdf) (sudo apt-get/yum install wkhtmltopdf)
- Rewrite module activé

### Le projet

- git clone https://github.com/sygefor/sygefor
- cd sygefor
- composer install
    - Renseigner les paramètres symfony
- npm install
- bower install
- php app/console doctrine:database:create
- php app/console doctrine:schema:create
- php app/console doctrine:fixtures:load (pour générer quelques données initiales)
- php app/console fos:js-routing:dump
- php app/console assetic:dump
- php app/console assets:install --symlink
- gulp build
- php app/console fos:elastica:populate (en cas de problème de sérialisation exécutez un composer update doctrine/orm)
- php app/console fos:user:change-password admin admin
- php app/console server:run
- Se rendre sur localhost:8000 avec votre navigateur (s'identifier avec admin/admin)
- chown www-data. app/cache app/logs -R pour servir avec apache et nginx
