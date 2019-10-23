FROM centos:7
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_NO_INTERACTION 1
ENV PATH $PATH:/data/vendor/bin
RUN /bin/cp -f /usr/share/zoneinfo/Asia/Tokyo /etc/localtime; \
    yum install -y epel-release; \
    rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm; \
    yum install -y --enablerepo=remi --enablerepo=remi-php72 git unzip mysql php php-cli php-common php-devel php-gd php-mbstring php-mysqlnd php-opcache php-pdo php-pecl-jsonc php-pecl-jsonc-devel php-pecl-xdebug php-pecl-zip php-process php-tidy php-xml php-pear composer
COPY ./startup.sh /
WORKDIR /data
CMD /startup.sh
