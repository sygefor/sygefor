#!/bin/sh

yum-config-manager --add-repo http://download.opensuse.org/repositories/security://shibboleth/CentOS_CentOS-6/security:shibboleth.repo
yum update
yum install shibboleth.x86_64
