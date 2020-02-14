# create a new group and add www-data (apache user) to it
# we do this is because we're using a shared folder
# TODO: possibly if [ ! group exists vboxsf ] so that I can reboot this container
echo "Handling shared folder permission"
groupadd -g 998 myvboxsf
usermod -aG myvboxsf www-data


# TODO: move this stuff to webserver dockerfile

# # install docker
# # TODO: possibly if [ which docker > /dev/null ] to see if need to install to allow me to reboot containers
# apt update
# apt install -y apt-transport-https ca-certificates curl gnupg2 software-properties-common
# curl -fsSL https://download.docker.com/linux/debian/gpg | apt-key add -
# add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
# apt update
# apt install -y docker-ce docker-ce-cli containerd.io

# # test docker works:
# # docker run hello-world

# # install docker-compose
# curl -L "https://github.com/docker/compose/releases/download/1.25.3/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
# chmod +x /usr/local/bin/docker-compose

# test docker-compose
# docker-compose --version

echo ""
echo "ALL READY!"
echo ""
echo "SSH:"
echo "docker exec -it $(docker ps | grep _webserver | sed -n -r 's/^([^ ]+).+$/\1/p') bash"
echo ""
echo "MySQL (from within websever):"
echo "mysql -uroot -proot -h database"
echo ""

# execute apache
exec "apache2-foreground"
