# create a new group and add www-data (apache user) to it
# we do this is because we're using a shared folder

echo "Handling shared folder permission"
groupadd -g 998 myvboxsf
usermod -aG myvboxsf www-data

# set identifier file used by dockerenv/bootstrap.php
echo "Adding is_legion_docker.txt file into /home"
touch /home/is_legion_docker.txt

echo ""
echo "ALL READY!"
echo ""

# execute apache
exec "apache2-foreground"
