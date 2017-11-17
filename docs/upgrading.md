## Upgrade from 2.0.x to 3.0.x
   
Subsite Starterkit 3.0.0 introduces itself as a Composer package. In order
to provide that a new building procedure has been put in place. These
upgrade instructions assume that your subsite is a "git fork" of the old
repository at https://github.com/ec-europa/subsite-starterkit.

[![Screencast](https://img.youtube.com/vi/cwGZilB3BjQ/0.jpg)](https://www.youtube.com/watch?v=cwGZilB3BjQ)

### 1.1 Phing upgrade

>```bash
> curl https://raw.githubusercontent.com/ec-europa/toolkit/master/includes/templates/subsite/composer.json > composer.json
> curl https://raw.githubusercontent.com/ec-europa/toolkit/master/includes/templates/subsite/build.xml > build.xml
> rm -rf ./vendor ./bin ./composer.lock
> composer install
> ./toolkit/phing toolkit-upgrade-starterkit
>```
