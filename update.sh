#!/bin/bash

#mysqldump -uroot --password=root proteinpos > proteinpos.sql

#scp -i /home/sam/Dropbox/Work/Misc/PEM/lightsail.pem proteinpos.sql ubuntu@54.147.84.243:~/proteinpos.sql

#ssh lightsail "cd proteinpos && git pull && mysql -uroot --password=root -e 'drop database proteinpos' && mysql -uroot --password=root -e 'create database proteinpos' && mysql -uroot --password=root proteinpos < ~/proteinpos.sql && rm ~/proteinpos.sql"

#rm proteinpos.sql
