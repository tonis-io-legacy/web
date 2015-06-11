Docker
======

Tonis comes with a Dockerfile. The difference between using [Vagrant](https://www.vagrantup.com) and [Docker](https://www.docker.com) is that 
any changes made to the code after the Docker container has been created will not be reflected in the container.  The 
primary usage of the docker container is for production where code only changes on deployment.

The Dockerfile takes care of running both composer and bower when the container is built.
