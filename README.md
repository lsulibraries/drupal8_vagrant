# Making a Dev box:

### install dependencies:
  
  - ansible, vagrant, virtualbox just like for dora repo

### build a dev box:

  - ```git clone https://github.com/lsulibraries/drupal8_vagrant```
  - ```cd drupal8_vagrant```
  - change the database password in file playbooks/confs/drupal/settings.php (each site can use a different password)
  - ```vagrant up --provision```
  - empty website at localhost:8080

### add drupal_sync & gnatty_theme:

  - vagrant ssh
  
  - ```cd /var/www/html```
  - ```sudo git clone https://github.com/lsulibraries/drupal_sync```
  - ```sudo chown -R apache:apache drupal_sync```
  - ```cd /var/www/html/web/themes/```
  - (this clones the gnatty_theme repo as the foldername contrib/.)
  - ```sudo git clone https://github.com/lsulibraries/drupal8_theme contrib/```
  - ```sudo chown -R apache:apache contrib/```

  - or do git add/push/checkout/whatever.  we're treating these folders as unrelated repos on dev and on production.
  - Setting these permissions is necessary after pulling any new files from github (git does not preserve user:group for good reasons).


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


# Misc

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

  - ansible-playbook --limit="drupal8dev," { playbook_filename }.yml

# run one playbook against production:
    
USERNAME = you username on remote system
REMOTE = ip address or domain name of the remote system


