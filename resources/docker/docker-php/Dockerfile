FROM fpfis/php56-dev

ENV PATH="/var/www/html/vendor/ec-europa/ssk/includes/composer/bin:${PATH}"

#RUN groupadd -g 1000 jenkins
#RUN useradd -g jenkins -u 1000 -m jenkins
#RUN gpasswd -a jenkins apache
#RUN usermod apache --append --groups jenkins

#RUN chown -R jenkins:jenkins /root/.composer

#USER jenkins

ADD configs/composer.json /root/.composer/composer.json

RUN composer global install

RUN curl -o ~/registry_rebuild-7.x-2.5.tar.gz -sS "https://ftp.drupal.org/files/projects/registry_rebuild-7.x-2.5.tar.gz" && \
    mkdir -p ~/.drush/registry_rebuild && \
    tar xzf ~/registry_rebuild-7.x-2.5.tar.gz -C ~/.drush/registry_rebuild && \
    rm ~/registry_rebuild-7.x-2.5.tar.gz

USER root
#USER web
