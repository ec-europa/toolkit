## Upgrade from 2.0.x to 3.0.x
   
<a href="http://www.youtube.com/watch?feature=player_embedded&v=cwGZilB3BjQ
" target="_blank"><img src="http://img.youtube.com/vi/cwGZilB3BjQ/0.jpg" 
alt="IMAGE ALT TEXT HERE" width="240" height="180" border="10" align="left" /></a>

Subsite Starterkit 3.0.0 introduces itself as a Composer package. In order
to provide that a new building procedure has been put in place. These
upgrade instructions assume that your subsite is a "git fork" of the old
repository at https://github.com/ec-europa/subsite-starterkit.

### Upgrade steps

>```bash
> curl https://raw.githubusercontent.com/ec-europa/toolkit/master/includes/templates/subsite/composer.json > composer.json
> curl https://raw.githubusercontent.com/ec-europa/toolkit/master/includes/templates/subsite/build.xml > build.xml
> rm -rf ./vendor ./bin ./composer.lock
> composer install
> ./toolkit/phing toolkit-upgrade-starterkit
>```
