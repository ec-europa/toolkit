FROM composer/composer

RUN groupadd -g 1000 jenkins
RUN useradd -g jenkins -u 1000 -m jenkins
RUN chown -R jenkins:jenkins /composer

USER jenkins

ADD configs/composer.json /composer/composer.json

RUN composer config -g github-oauth.github.com 9fdd1e7949597527a5f1fb982b486999a9c76767
RUN composer global install
