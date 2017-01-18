#!/bin/sh

apt-get install libssl-dev
apt-get install libboost-all-dev
apt-get install apache2-dev

wget https://shibboleth.net/downloads/log4shib/1.0.9/log4shib-1.0.9.tar.gz
wget https://www.apache.org/dist/xerces/c/3/sources/xerces-c-3.1.3.tar.gz
wget http://www.apache.org/dist/santuario/c-library/xml-security-c-1.7.2.tar.gz
wget https://shibboleth.net/downloads/c++-opensaml/2.5.3/xmltooling-1.5.3.tar.gz
wget https://shibboleth.net/downloads/c++-opensaml/2.5.3/opensaml-2.5.3.tar.gz
wget https://shibboleth.net/downloads/service-provider/2.5.3/shibboleth-sp-2.5.3.tar.gz

tar xvfz log4shib-1.0.9.tar.gz
tar xvfz xerces-c-3.1.3.tar.gz
tar xvfz xml-security-c-1.7.2.tar.gz
tar xvfz xmltooling-1.5.3.tar.gz
tar xvfz opensaml-2.5.3.tar.gz
tar xvfz shibboleth-sp-2.5.3.tar.gz

cd log4shib-1.0.9
./configure --disable-static --disable-doxygen --prefix=/opt/shibboleth-sp && make && sudo make install

cd ../xerces-c-3.1.3
./configure --prefix=/opt/shibboleth-sp && make && sudo make install

cd ../xml-security-c-1.7.2
./configure --without-xalan --disable-static --with-xerces=/opt/shibboleth-sp --prefix=/opt/shibboleth-sp --with-openssl=/usr/lib/x86_64-linux-gnu && make && sudo make install

cd ../xmltooling-1.5.3
./configure --with-log4shib=/opt/shibboleth-sp --prefix=/opt/shibboleth-sp -C && make && sudo make install

cd ../opensaml-2.5.3
./configure --prefix=/opt/shibboleth-sp --with-log4shib=/opt/shibboleth-sp -C && make && sudo make install

cd ../shibboleth-sp-2.5.3
./configure --with-saml=/opt/shibboleth-sp --enable-apache-24 --with-log4shib=/opt/shibboleth-sp --with-xmltooling=/opt/shibboleth-sp --prefix=/opt/shibboleth-sp --with-apxs2=/opt/apache2/bin/apxs --with-apr1=/usr/bin/apr-1-config --with-apu1=/usr/bin/apr-1-config && make && sudo make install

sudo apt-get install shibboleth-sp2-schemas libshibsp-dev
sudo apt-get install libshibsp-doc libapache2-mod-shib2 opensaml2-tools

cd ..