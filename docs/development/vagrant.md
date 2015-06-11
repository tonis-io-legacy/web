Vagrant
=======

Tonis comes with a Vagrantfile. Utilizing [Vagrant](https://www.vagrantup.com), the working directory will be synced 
with the virtualized environment. You will need to have both [Vagrant](https://www.vagrantup.com) and 
[Docker](https://www.docker.com) installed. You are responsible for running composer and bower from the host.

```sh
sudo vagrant up --provider=docker
```

SSH is not enabled, but you can still get a shell prompt in the container.

```sh
$ sudo docker ps
CONTAINER ID        IMAGE                             COMMAND             CREATED             STATUS              PORTS                  NAMES
45d0c5d94ee0        czeeb/tonis-docker-nginx:latest   "/sbin/my_init"     13 minutes ago      Up 13 minutes       0.0.0.0:8080->80/tcp   tonis_nginx_1433860664

$ sudo docker exec -t -i tonis_nginx_1433860664 bash -l
root@45d0c5d94ee0:/#
```
