FROM mysql:5.7.28
ENV MYSQL_RANDOM_ROOT_PASSWORD 1
ENV MYSQL_DATABASE sdcbar
RUN /bin/rm -f /etc/mysql/conf.d/docker.cnf; \
    /bin/cp -f /usr/share/zoneinfo/Asia/Tokyo /etc/localtime
COPY docker/product/mysql/charset.cnf /etc/mysql/conf.d
COPY server-side/sql/build.sql docker-entrypoint-initdb.d
