######
# How to build
# sudo docker build --tag contaomariadb .
######

FROM mariadb:latest

ENV MYSQL_ROOT_PASSWORD=contao

COPY init.sql /docker-entrypoint-initdb.d/

RUN ["/usr/local/bin/docker-entrypoint.sh"]

