# drupal8_vagrant
A vagrant box to match production

# install dependencies:
  
  - ansible, vagrant, virtualbox just like for dora repo

# run one playbook against local box:

  - ansible-playbook -i hosts.ini { playbook_filename }.yml



/etc/hosts file -- adding localhost or production or whatever
versus using -i local_hosts.ini