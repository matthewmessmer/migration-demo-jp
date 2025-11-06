
# Migration Demo

Drupal 10 migration demo.

Comes with preconfigured ddev dev environment.

## System requirements:
* [Docker for Mac](https://docs.docker.com/desktop/mac/install/) or [Docker for Windows](https://docs.docker.com/desktop/windows/install/) (Linux doesn’t requires this software)
* [ddev](https://docs.ddev.com/en/stable/)

## Setup Local Environment
* Clone the repository
```
git clone git@github.com:matthewmessmer/migration-demo-jp.git && cd migration-demo
```
* Run the following commands to install the site:
```
composer install --ignore-platform-reqs
ddev start
ddev drush si --existing-config
```
* If you are on Linux and have issues starting ddev, try adding `export ddev_SSH_AUTH_SOCK="${SSH_AUTH_SOCK}"` at the end of your ~/.bashrc

## Access the site
* `https://migration-demo.lndo.site` Drupal site
* `http://migration-api.lndo.site` Mock API powered by `https://github.com/typicode/json-server`

## Running Migrations

* List migrations
```
ddev drush ms
```

* Run migration import
```
ddev drush mim *migration_name*
ddev drush mim *migration_name* --update
ddev drush mim *migration_name* --limit=10
ddev drush mim *migration_name* --idlist=123
ddev drush mim --group=*group_name*
```

* Rollback migration
```
ddev drush mr *migration_name*
ddev drush mr --group=*group_name*
```

* Reset migrations status to idle
```
ddev drush mrs *migration_name*
```
