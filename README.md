
# Migration Demo

Drupal 10 migration demo.

Comes with preconfigured ddev dev environment.

## System requirements:
* [Docker for Mac](https://docs.docker.com/desktop/mac/install/) or [Docker for Windows](https://docs.docker.com/desktop/windows/install/) (Linux doesn’t requires this software)
* [ddev](https://docs.ddev.com/en/stable/)

## Setup Local Environment
* Clone the repository
```
git clone https://github.com/matthewmessmer/migration-demo-jp.git && cd migration-demo-jp
```
* Run the following commands to install the site:
```
ddev composer install --ignore-platform-reqs
ddev start
ddev drush si --existing-config
```

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
