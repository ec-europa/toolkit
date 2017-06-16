FROM fpfis/php56-dev

ENV PATH="/var/www/html/vendor/ec-europa/ssk/includes/composer/bin:${PATH}"

ADD configs/composer.json /tmp/.composer/composer.json

RUN composer update --working-dir=/tmp/.composer

RUN curl -o ~/registry_rebuild-7.x-2.5.tar.gz -sS "https://ftp.drupal.org/files/projects/registry_rebuild-7.x-2.5.tar.gz" && \
    mkdir -p ~/.drush/registry_rebuild && \
    tar xzf ~/registry_rebuild-7.x-2.5.tar.gz -C ~/.drush/registry_rebuild && \
    rm ~/registry_rebuild-7.x-2.5.tar.gz

USER root
