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
    * xml
    * zip
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
   - Ajouter le fichier [elasticsearch.repo](https://github.com/sygefor/sygefor/blob/master/external_conf/elasticsearch.repo) dans le répertoire /etc/yum.repos.d/ pour CentOS
   - Ajouter "deb http://packages.elasticsearch.org/elasticsearch/1.4/debian stable main" dans /etc/apt/sources.list pour Debian
   - Installer le paquet elasticsearch

### Unoconv

La génération des PDF lors d'un publipostage est rendue possible grâce à la librairie [Unoconv](https://github.com/dagwieers/unoconv)
qui doit donc être installée sur le serveur.

* version 0.7

### Shibboleth

Sygefor3 utilise la Fédération d'identité Education-Recherche de Renater pour permettre aux stagiaires de s'inscrire, au travers
du protocole Shibboleth. Il faut donc installer un Service Provider sur le serveur et le déclarer auprés de Renater :

[Installation d'un SP Shibboleth](https://services.renater.fr/federation/docs/installation/sp#test_dans_la_federation_de_test)

### Docker
------------

Vous pouvez utiliser docker pour lancer les services nécessaires à Sygefor3. 
Le docker-compose.yml contient les containers déjà configurés.
Avant de lancer docker vous devrez construire votre image [Shibboleth](https://github.com/sygefor/docker-shibboleth)
et associer les droits d'écriture à l'utilisateur www-data pour les répertoires suivants :
 - app/cache
 - app/logs
 - var/Material
 - var/Publipost
 - /tmp/sygefor dans le container
 
Vous pouvez ensuite exécuter la commande docker-compose up pour lancer les containers.
Attention à renseigner les bons paramètres dans app/config/parameters.yml. Vous pouvez remplacer :
 - database_host par mysql
 - elasticsearch_host par elasticsearch
 - mailer_host par mailcatcher 

[Installer docker](https://docs.docker.com/install/)

[Installer docker-compose](https://docs.docker.com/compose/install/#prerequisites)


Installation de Sygefor3
------------

### Prérequis

- Composer installé : http://www.coolcoyote.net/php-mysql/installation-de-composer-sous-linux-et-windows
- Openssl installé
- npm installé
    - curl -sL https://deb.nodesource.com/setup_6.x | bash -
    - apt-get install npm
- yarn, bower, gulp et n installés (sudo npm install yarn bower gulp@3.9.1 n -g)
- Node avec la version 6.8 (sudo n 6.8.0)
- Visual Studio Redistributables installé pour Windows
- libssl-dev installé pour linux
- Rewrite module activé

### Le projet

- git clone https://github.com/sygefor/sygefor.git
- cd sygefor
- git submodule update --init
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
- Se rendre sur localhost:8000 avec votre navigateur pour accéder au BO
- Se connecter avec les identifiants admin/admin
- Ajouter une entrée dans votre fichier host pour faire pointer sygefor.com vers 127.0.0.1
- Se rendre sur sygefor.com:8000 avec votre navigateur pour accéder au FO

### API

Sygefor3 intègre une API disponible dans ApiBundle. Il est possible de réserver certaines parties de l'API aux utilisateurs connectés en OAuth2 ou via Shibboleth.
L'API permet notamment d'exporter [les formations](http://sygefor.com:8000/api/training) et [les sessions de formations](http://sygefor.com:8000/api/training/session).

### Export [LHEO](http://lheo.gouv.fr/description)

Sygefor3 intègre un [export LHEO](http://sygefor.com:8000/api/lheo/sygefor) des formations.

### Etendre

Le coeur de Sygefor3 est intégré dans les sous-modules du projet. Ce coeur déclare des classes et des controlleurs abstraits. Vous devez étendre
ces classes et controlleurs pour faire fonctionner l'application.
Le AppBundle intègre ces extentions. Vous pourrez comprendre comment étendre Sygefor3 en regardant ce bundle.

Vous pouvez également adapter l'interface privée de gestion en modifiant les modèles AngularJS contenus dans le répertoire app/Resources/public/ng.
Le module FrontBundle intègre une version publique et allégée de Sygefor permettant aux stagiaires de s'inscrire aux différents stages.
Vous pourrez aussi retrouver un module Bilan basé sur ElasticSearch.