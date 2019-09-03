# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "centos/7"

  config.vm.network "private_network", ip: "192.168.33.10"

  config.vm.synced_folder ".", "/vagrant", disabled: true
  config.vm.synced_folder ".", "/var/www/vhosts/development", 
    create: true, 
    type: 'sshfs'
    
  config.vm.synced_folder "../devops", "/var/www/vhosts/devops",
    create: true, 
    type: 'sshfs'

  config.vm.synced_folder "../server_bin", "/root/bin",
    create: true, 
    type: 'sshfs'

  
	config.vm.provider "virtualbox" do |vb|
	  vb.gui = false
          vb.memory = "2048"
          vb.customize ["modifyvm", :id, "--cableconnected1", "on"]
   end
  
  config.vm.provision "shell" do |s|
    s.inline ="/bin/sh /root/bin/provisioning/vagrant_provisioning_laravel-spark.sh homestead"
  end
end
