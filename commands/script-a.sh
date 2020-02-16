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
