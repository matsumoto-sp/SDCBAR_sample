FROM mysql:5.7.28
ENV MYSQL_ALLOW_EMPTY_PASSWORD 1
ENV MYSQL_DATABASE sdcbar
RUN /bin/rm -f /etc/mysql/conf.d/docker.cnf; \
    /bin/cp -f /usr/share/zoneinfo/Asia/Tokyo /etc/localtime
COPY charset.cnf /etc/mysql/conf.d
