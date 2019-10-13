# drupal8_vagrant
A vagrant box to match production

# install dependencies:
  
  - ansible, vagrant, virtualbox just like for dora repo

# build a dev box:

  - git clone --recurse-submodule https://github.com/lsulibraries/drupal8_vagrant
  - cd drupal8_vagrant
  - vagrant up --provision
  - site will be at localhost:8080

# run one playbook against local box:

  - ansible-playbook -i hosts.ini { playbook_filename }.yml

# run one playbook against production:
    
  - tbd

# version control of drupal_sync & gnatty_theme (Prod or Dev):

  - vagrant ssh or ssh into the production box
  
  - cd /var/www/html
  - sudo git clone https://github.com/lsulibraries/drupal_sync
  - sudo chown -R ???:??? drupal_sync

  - cd /var/www/html/web/themes/
  - sudo git clone https://github.com/lsulibraries/gnatty_theme contrib/
  - (this clones the gnatty_theme repo as the foldername contrib/.)
  - sudo chown -R apache:apache contrib/

  - (or git add/push/checkout/whatever).  we're treating these folders as independent repos.

  - Setting the permissions as described above is necessary after pulling any new files from github (git does not preserve user:group for good reasons).
