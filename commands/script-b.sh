# create a new group and add www-data (apache user) to it
# we do this is because we're using a shared folder

# echo "Handling shared folder permission"
# groupadd -g 998 myvboxsf
# usermod -aG myvboxsf www-data

echo ""
echo "ALL READY!"
echo ""

# execute apache
exec "apache2-foreground"
