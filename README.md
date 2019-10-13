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
