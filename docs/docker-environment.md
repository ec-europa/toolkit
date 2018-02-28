# Using Docker environment

<big><table><thead><tr><th nowrap> [Using Git hooks](./git-hooks.md#using-git-hooks) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [NextEuropa Toolkit](../README.md#nexteuropa-toolkit) </th></tr></thead></table>

<a href="http://www.youtube.com/watch?feature=player_embedded&v=cwGZilB3BjQ
" target="_blank"><img src="http://img.youtube.com/vi/cwGZilB3BjQ/0.jpg" 
alt="Upgrade screencast" width="240" height="135" align="left" /></a>

The toolkit comes with a docker environment that can help you set up your project
much faster. This feature is still experimental and in progress. You can watch a
very short screencast on how to use this docker environment. It makes use of a
docker-compose.yml with custom FPFIS images. If
[the provided docker-compose.yml](../includes/docker/docker-compose.yml)
does meet your needs we advise you to customize it.

## How it works

Executing the dbash file with up -d will assign your environment a unique id and
symlink a file from your root to the dbash file. This way you can keep track of
your environments and know which one to access.

<details><summary>execute <code>./vendor/ec-europa/toolkit/includes/docker/dbash up -d</code></summary><p>

```
Creating network "5660526056154745951df82537e1f1cf_default" with the default driver
Creating 5660526056154745951df82537e1f1cf_mysql_1 ...
Creating 5660526056154745951df82537e1f1cf_mysql_1
Creating 5660526056154745951df82537e1f1cf_selenium_1 ...
Creating 5660526056154745951df82537e1f1cf_solr_1 ...
Creating 5660526056154745951df82537e1f1cf_solr_1
Creating 5660526056154745951df82537e1f1cf_selenium_1 ... done
Creating 5660526056154745951df82537e1f1cf_web_1 ...
Creating 5660526056154745951df82537e1f1cf_web_1 ... done
```
</p></details>

To access your environment you can execute the symlink file which is just a
connection between the host and your docker containers. The syntax to use after
the script is regular docker-compose. If you are accessing the web service you
can keep the host user and group id by executing your command with the web user.

<details><summary>execute <code>./docker-56605260-5615-4745-951d-f82537e1f1cf exec --user web web ./toolkit/drush status</code></summary><p>

```
 PHP executable         :  /usr/bin/php
 PHP configuration      :  /etc/php.ini
 PHP OS                 :  Linux
 Drush script           :  /home/user/ec-europa/toolkit/vendor/drush/drush/drush.php
 Drush version          :  8.0.5
 Drush temp directory   :  /tmp
 Drush configuration    :
 Drush alias files      :
```
</p></details>

If you are ready using the environment you should clean up afterwards. This can
be accomplished with the down command. It will stop and remove all containers
that are part of the project id.

<details><summary>execute <code>./docker-56605260-5615-4745-951d-f82537e1f1cf down</code></summary><p>

```
Stopping 5660526056154745951df82537e1f1cf_web_1 ... done
Stopping 5660526056154745951df82537e1f1cf_selenium_1 ... done
Stopping 5660526056154745951df82537e1f1cf_solr_1 ... done
Removing 5660526056154745951df82537e1f1cf_web_1 ... done
Removing 5660526056154745951df82537e1f1cf_selenium_1 ... done
Removing 5660526056154745951df82537e1f1cf_solr_1 ... done
Removing 5660526056154745951df82537e1f1cf_mysql_1 ... done
Removing network 5660526056154745951df82537e1f1cf_default
```
</p></details>
