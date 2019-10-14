# drupal8_vagrant
A vagrant box to match production

# install dependencies:
  
  - ansible, vagrant, virtualbox just like for dora repo

# build a dev box:

  - ```git clone https://github.com/lsulibraries/drupal8_vagrant```
  - ```cd drupal8_vagrant```
  - change the database password in file playbooks/confs/drupal/settings.php (each site can use a different password)
  - ```vagrant up --provision```
  - website at localhost:8080

# add drupal_sync & gnatty_theme (Prod or Dev):

  - vagrant ssh or ssh into the production box
  
  - ```cd /var/www/html```
  - ```sudo git clone https://github.com/lsulibraries/drupal_sync```
  - ```sudo chown -R apache:apache drupal_sync```
  - ```cd /var/www/html/web/themes/```
  - ```sudo git clone https://github.com/lsulibraries/gnatty_theme contrib/```
  - (this clones the gnatty_theme repo as the foldername contrib/.)
  - ```sudo chown -R apache:apache contrib/```

  - or do git add/push/checkout/whatever.  we're treating these folders as independent repos on dev and on production.
  - Setting these permissions is necessary after pulling any new files from github (git does not preserve user:group for good reasons).


  # build a production box

  - using the php.ini-development would display php errors & var dumps onto the user's browser.  So I'm making this difficult to mess up by forcing us to manually move confs/drupal/php.ini-production to the production box's /etc/php.ini with root:root permissions.  NOTE:  Fix this later to do ansible commands based on which host(localhost vs production.)
  - tbd

# Secure the database

  - vagrant ssh or ssh into the production box
  - ```sudo mysql_secure_installation```
  - give a new root password then "y" for all the options.
  - (this remove anonymous users, sets a root password, etc.)

## create a new database

  - after securing the database aboce
  - ```mysql -u root -p```  (use root password set above)
  - ``` CREATE USER 'drupaluser'@'localhost' IDENTIFIED BY 'password';
```
  - ```CREATE DATABASE drupal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;```
  - ```GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON drupal.* TO 'drupaluser'@'localhost' IDENTIFIED BY 'password';```
  - ```exit```

## import existing database

  - after securing the database above
  - ```mysql -u root -p```  (use root password set above)
  - ``` CREATE USER 'drupaluser'@'localhost' IDENTIFIED BY 'password';
```
  - ```exit```
  - ```mysql -u drupal -p drupal < data-dump.sql```
  - ```mysql -u root -p```
  -```GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON drupal.* TO 'drupaluser'@'localhost' IDENTIFIED BY 'password';```
  - ```exit```


# updating composer.json, drupal settings.php, httpd.conf

  - revise & version control the file on your local machine, then
  - ```vagrant destroy && vagrant up --provision```

# run one playbook against local box:

  - ansible-playbook -i hosts.ini { playbook_filename }.yml

# run one playbook against production:
    
  - tbd

