# Making a Dev box:

### install dependencies:
  
  - ansible, vagrant, virtualbox just like for dora repo

### Pull this repo to your computer

  - ```git clone https://github.com/lsulibraries/drupal8_vagrant```
  - ```cd drupal8_vagrant```

### Change the drupaluser db password

  - drupaluser is the account drupal will use to connect to MariaDB.  His password is valuable.  Each box can have a different drupaluser password.
  - write some new password in file ./playbooks/conf/drupal/settings.php
  - remember this password for the step "Create a new database"

### build a dev box

  - ```vagrant up --provision```
  - localhost:8080

### Secure the database

  - ```vagrant ssh```
  - ```sudo mysql_secure_installation```
  - root is the account with complete control over MariaDB & his password is very valuable.  Each box can have a different root password.
  - when asked, root initially has no password.
  - give root a new password, then "y" for all the subsequent options.

### Create a new database

  - after securing the database above

  - using the drupaluser password saved in ./playbooks/conf/drupal/settings.php:

  ```mysql -u root -p```  (use root password set above)

  ```CREATE USER 'drupaluser'@'localhost' IDENTIFIED BY '{password}';```

  ```CREATE DATABASE drupal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;```

  ```GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON drupal.* TO 'drupaluser'@'localhost' IDENTIFIED BY '{password}';```

  ```exit```

### Import existing database

  if your OS has mariadb <5.6: 

  -```sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' drupal8_sandbox_db.sql```

  then import:

  - ```cd /vagrant```
  - ```mysql -u root -p drupal < drupal8_sandbox_db.sql```

### add our drupal_sync

  - ```cd /var/www/html/drupal_site/```
  - next, we're git cloning as user apache
  - ```sudo -u apache git clone https://github.com/lsulibraries/drupal_sync```

### Sync the config settings

  - ```cd /var/www/html/drupal_site/```
  - ```drush config-import -y```

### add our drupal8_theme

  - ```vagrant ssh```
  - ```cd /var/www/html/drupal_site/web/themes/```
  - ```sudo rm -R contrib/```
  - (the next step is complicated.  We run the command as the user "apache", so that the permissions get assigned to "apache".  We also rename the folder from "drupal8_theme" to "contrib", in order to make drupal happy.  The rename is shallow & we can still git push & pull from "contrib" folder to the github "drupal8_theme" repo.)
  - ```sudo -u apache git clone https://github.com/lsulibraries/drupal8_theme contrib/```


# Maintenance

### Version control of theme or sync

  - git add/push/checkout/whatever from within the vagrant box, and from within the repo folders.  Those folders are:

    - /var/www/html/drupal_site/drupal_sync/  (drupal_sync)
    - /var/www/html/drupal_site/web/themes/contrib/  (drupal8_theme)

  - we're treating these folders as unrelated repos on dev and on production.

  - git does not preserve user:group permissions for good reasons, so you may find permissions errors after a git pull.


### updating composer.json, drupal settings.php, httpd.conf

  - these files belong to the drupal8_vagrant repo

  - revise the files on your host computer, & run git commands from your host computer. 

  - to prove the changes are good, run a ```vagrant destroy && vagrant up --provision``` complete rebuild of the box.

# Making a Production box:

### add your ssh public key to the remote server

 - check if you already have a private & public key on your local machine.

   - ```ls ~/.ssh/id_*```
   - there may already be an "id_rsa" [private] and "id_rsa.pub" [public] keypair.
   
   - if not, create a new one:

     - caveats -- creating a new keypair overwrites the old keypair.  
     - ```ssh-keygen```
     - confirm the files "id_rsa" and "id_rsa.pub" are in your ```~/.ssh/``` folder.

 - send your public key to the remote server:
    
    (if you can ```ssh USERNAME@REMOTE```, then)
    ```ssh-copy-id USERNAME@REMOTE```

    you can check the remote machine's ~/.ssh/authorized_keys to see it matches your local ~/.ssh/id_rsa.pub

 - this process has the cool effect of allowing ssh login to remote without password.


### make ansible aware of the remote server

 - add the following text to /etc/ansible/hosts

```[drupal8dev]
127.0.0.1 ansible_connection=local

[drupal8staging]
130.39.60.169   # libwebsitebackup001.lsu.edu
```

#### check connection

 - check that your local ansible can connect to the remote server
 - ```ansible all -m ping -u USERNAME```
 - or ```ansible drupal8staging -m ping -u USERNAME```
 - run a command on the remote computers
 - ```ansible all -u USERNAME -a "/bin/echo hello"```

#### run one playbook

 - the "hosts" variable in the playbook.yaml names which group of servers listed in /etc/ansible/hosts to target
 - if your username is different on remote and local, use -u USERNAME flag.
 - if your playbook runs any commands as become sudo, you must add the --ask-become-pass flag.

 ```ansible-playbook {path/name.yaml} -u USERNAME --ask-become-pass --limit="{HOSTNAME},"```


# All builds:

### Secure the database

  - vagrant ssh or ssh into the production box
  - ```sudo mysql_secure_installation```
  - give a new root password then "y" for all the options.
  - (this remove anonymous users, sets a root password, etc.)

### Create a new database

  - after securing the database above

  - using the drupaluser password saved in ./playbooks/conf/drupal/settings.php:

  ```mysql -u root -p```  (use root password set above)

  ``` CREATE USER 'drupaluser'@'localhost' IDENTIFIED BY 'password';```

  ```CREATE DATABASE drupal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;```

  ```GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON drupal.* TO 'drupaluser'@'localhost' IDENTIFIED BY 'password';```

  ```exit```

### Import existing database

  if your OS has mariadb <5.6: 

  ```sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' drupal8_sandbox_db.sql```

  then import:

  ```mysql -u root -p drupal < drupal8_sandbox_db.sql```


### Sync the config settings

  - ```drush config-import -y```














# Creating new playbooks for an existing machine:

### run one playbook against local box:

  - ansible-playbook --limit="drupal8dev," { playbook_filename }.yml

### run one playbook against production:
    
USERNAME = you username on remote system
REMOTE = ip address or domain name of the remote system


# Misc

  - using the php.ini-development would display php errors & var dumps onto the user's browser.  So I'm making this difficult to mess up by forcing us to manually move confs/drupal/php.ini-production to the production box's /etc/php.ini with root:root permissions.  NOTE:  Fix this later to do ansible commands based on which host(localhost vs production.)
  - tbd

# whenever updating composer.json, drupal settings.php, httpd.conf

  - revise & version control the file on your local machine, then
  ```vagrant destroy && vagrant up --provision```