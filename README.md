# Making a Dev box:

### Install dependencies:
  
  - ansible, vagrant, virtualbox just like for dora repo

### Pull this repo to your computer

  - ```git clone https://github.com/lsulibraries/drupal8_vagrant```
  - ```cd drupal8_vagrant```

### Copy in the sqldump

  - from R:/TechInit/Drupal8DbAuthoritative/drupal8_sandbox_db.sql
  - copy inside the drupal8_vagrant folder
  - This file is the authoritative version of the drupal8 db that we'll be build up into the production database.

### Add our drupal8_theme

  - in your local drupal8_vagrant
  - ```git clone https://github.com/lsulibraries/drupal8_theme```

### Change the drupaluser db password

  - drupaluser is the account drupal will use to connect to MariaDB.  His password is valuable.  Each box can have a different drupaluser password.
  - write some new password in file ./playbooks/conf/drupal/settings.php
  - remember this password for the step "Create a new database"

### Build a dev box

  - ```vagrant up --provision```

### Secure the database

  - ```vagrant ssh```
  - ```sudo mysql_secure_installation```
  
  - root is the account with complete control over MariaDB & his password is very valuable.  Each box can have a different root password.
  - when asked, root initially has no password.
  - give root a new password, then "y" for all the subsequent options.

### Create a new database

  - after securing the database above

  - ```mysql -u root -p```  (use root password set above)

  - using the drupaluser password saved in ./playbooks/conf/drupal/settings.php:

  - ```CREATE USER 'drupaluser'@'localhost' IDENTIFIED BY '{password}';```

  - ```CREATE DATABASE drupal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;```

  - ```GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON drupal.* TO 'drupaluser'@'localhost';```

  - ```exit```

### Import existing database

  if ```mysql --version``` is earlier than 5.56-MariaDB, fix the encoding with: 

  - while still in the vagrant box
  - ```cd /vagrant```
  - ```sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' drupal8_sandbox_db.sql```

  import:

  - ```cd /vagrant```
  - ```mysql -u root -p drupal < drupal8_sandbox_db.sql```
  - using the new root password

### Add our drupal_sync

  - while in the vagrant box
  - ```git config --global user.name {username}```
  - ```git config --global user.email {email}```
  - ```cd /var/www/html/drupal_site/```
  - next, we're git cloning and setting user apache permissions
  - ```sudo git clone https://github.com/lsulibraries/drupal_sync```
  - ```sudo chown -R apache:apache drupal_sync```
  - ```sudo -u apache chmod -a a+w /var/www/html/drupal_site/drupal_sync```

### Replace theme/contrib with our symlinked repo

  - ```cd /var/www/html/drupal_site/web/themes/```
  - ```sudo rm -rf contrib```
  - ```sudo -u apache ln -s /vagrant/drupal8_theme contrib```

### Sync the config settings

  - ```cd /var/www/html/drupal_site/```
  - ```drush config-import -y```

### Restart apache httpd

  - ```sudo systemctl restart httpd```

### Browser interface opened

  - localhost:8080
  - for Windows: from outside box, ```vagrant rsync-auto```

# Version control theme or config settings

### Version control of theme

  - edit the drupal_theme repo on your outside box.  
  - git commit from outside box

### Version control of drupal_sync


  - ```drush cex``` exports the feature changes you made to the drupal database
 
  An example:

  - ```cd /var/www/html/drupal_site/drupal_sync```
  - ```git checkout -b feature/{feature_name}```
  - ```git add *```
  - ```git commit```
  - ```git push origin```

  - git does not preserve user:group permissions for good reasons, so you may find yourself fixing permissions errors after a git pull

### Updating composer.json, drupal settings.php, httpd.conf

  - these files belong to the drupal8_vagrant repo

  - you may revise these files inside your vagrant box.  The revisions will instantly take effect.  Possibly a service restart httpd is required.

  - to get those changes into the drupal8_vagrant repo, you'll need to revise your host computer's repo & git push/pull from your host computer

  - to prove the changes are good, run a ```vagrant destroy && vagrant up --provision```, etc. to complete rebuild of the box.

# Making a Production box:

## set up ansible connection

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

```
[drupal8dev]
127.0.0.1 ansible_connection=local

[drupal8staging]
96.125.26.115   # libwebtest.lsu.edu
```

#### check connection

 - check that your local ansible can connect to the remote server
 - ```ansible all -m ping -u USERNAME```
 - or ```ansible drupal8staging -m ping -u USERNAME```
 - run a command on the remote computers
 - ```ansible all -u USERNAME -a "/bin/echo hello"```

## run the ansible build

### Pull this repo to your computer

  - ```git clone https://github.com/lsulibraries/drupal8_vagrant```
  - ```cd drupal8_vagrant```

### Change the drupaluser db password

  - drupaluser is the account drupal will use to connect to MariaDB.  His password is valuable.  This drupaluser account/password will last a long time, so remember it & share it.
  - write that longterm password in file ./playbooks/conf/drupal/settings.php
  - remember this password for the step "Create a new database"

### run the ansible build against production

 - the "hosts" variable in the playbook.yaml names which group of servers listed in /etc/ansible/hosts to target
 - if your username is different on remote and local, use -u USERNAME flag.  USERNAME is an existing user on the remote server.
 - if your playbook runs any commands as become sudo, you must add the --ask-become-pass flag.

 ```ansible-playbook playbooks/prod_build.yaml -u USERNAME --ask-become-pass --limit="drupal8staging,"```

## manual steps

### Secure the database

  - ssh to remote
  - ```sudo mysql_secure_installation```
  
  - root is the account with complete control over MariaDB & his password is very valuable.  Each box can have a different root password.
  - when asked, root initially has no password.
  - give root a new password, then "y" for all the subsequent options.

### Create a new database

  - after securing the database above

  - ```mysql -u root -p```  (use root password set above)

  - using the drupaluser password saved in ./playbooks/conf/drupal/settings.php:

  - ```CREATE USER 'drupaluser'@'localhost' IDENTIFIED BY '{password}';```

  - ```CREATE DATABASE drupal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;```

  - ```GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON drupal.* TO 'drupaluser'@'localhost';```

  - ```exit```

### Import existing database

  - rsync the drupal8_sandbox_db.sql file to remote
  - ```rsync -avz desktopuser@130.39.61.01:/home/desktopuser/Desktop/lsugit/drupal8_vagrant/drupal8_sandbox_db.sql ~```

  if ```mysql --version``` is earlier than 5.56-MariaDB, fix the encoding with: 
  - ```sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' drupal8_sandbox_db.sql```

  import:

  - ```mysql -u root -p drupal < drupal8_sandbox_db.sql```

### Add our drupal_sync

  - ```cd /var/www/html/drupal_site/```
  - ```sudo git clone https://github.com/lsulibraries/drupal_sync```
  - ```sudo chown -R apache:apache drupal_sync```

### Add our drupal8_theme

  - ```cd /var/www/html/drupal_site/web/themes/```
  - ```sudo rm -R contrib/```
  - the next step is complicated
    - We run the command as the user "apache", so that the permissions get assigned to "apache".  
    - We also rename the folder from "drupal8_theme" to "contrib", in order to make drupal happy.  The rename is shallow & we can still git push & pull from "contrib" folder to the github "drupal8_theme" repo.
  - ```sudo -u apache git clone https://github.com/lsulibraries/drupal8_theme contrib/```

### Sync the config settings

  - ```cd /var/www/html/drupal_site/```
  - ```drush config-import -y```


### Restart apache httpd

  - ```sudo systemctl restart httpd```


# Creating new playbooks for an existing machine:

### run one playbook against a box:

 ```ansible-playbook playbooks/build.yaml} -u USERNAME --ask-become-pass --limit="drupal8staging,"``


# Misc

  - using the /usr/share/doc/php-common/php.ini-development would display php errors & var dumps onto the user's browser.  So I'm making this difficult to mess up by forcing us to manually move php.ini-production to the production box's /etc/php.ini with root:root permissions.  NOTE:  Fix this later to do ansible commands based on which host(localhost vs production.)
  - tbd
