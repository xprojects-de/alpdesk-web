######
# How to build
# sudo docker build --tag contaomariadb .
# also change too apssword inside Container mysqladmin -u root password newpass
# Then Grand accesy by
# sql>  GRANT ALL PRIVILEGES ON *.* TO 'root'@'%'IDENTIFIED BY 'root' WITH GRANT OPTION;
# sql>   FLUSH PRIVILEGES;
######

FROM mariadb:latest

ENV MYSQL_ROOT_PASSWORD=contao

COPY init.sql /docker-entrypoint-initdb.d/

RUN ["/usr/local/bin/docker-entrypoint.sh"]

