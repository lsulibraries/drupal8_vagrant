Vagrant.configure("2") do |config|

  config.vm.box = "centos/8"
  config.vm.box_version = "1905.1"

  config.vm.network "forwarded_port", guest: 80, host: 8080

  config.vm.post_up_message = "Done. enjoy..."

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "4196"
    vb.cpus = 2
  end

  require 'rbconfig'
  is_windows = (RbConfig::CONFIG['host_os'] =~ /mswin|mingw|cygwin/)
  if is_windows
    config.vm.provision "ansible_local" do |ansible|
      ansible.playbook = "playbooks/dev_build.yml"
      ansible.verbose = 'vv'
      ansible.install = true
      ansible.extra_vars = {
        mysql_local_installation: "true",
        attach_mounts: false,
        drupal_reverse_proxy: false,
        fqdn_suffix: 'library.local'
      }
    end
  else
    config.vm.provision "ansible" do |ansible|
      ansible.playbook = "playbooks/dev_build.yml"
      ansible.verbose = 'vv'
      ansible.extra_vars = {
        mysql_local_installation: "true",
        attach_mounts: false,
        drupal_reverse_proxy: false,
        fqdn_suffix: 'library.local'
      }
    end
  end
end
