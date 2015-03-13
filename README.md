# Files Mv
Place this app in **owncloud/apps/** and rename it to **files_mv**

## Install from github
Download the version from github and run the following command in the folder:

	make appstore_package

Extract the archive *build/artifacts/appstore/files_mv.tar.gz* to your owncloud app folder.

## Publish to App Store

First get an account for the [App Store](http://apps.owncloud.com/) then run:

    make appstore

**ocdev** will ask for your App Store credentials and save them to ~/.ocdevrc which is created afterwards for reuse.

If the <ocsid> field in **appinfo/info.xml** is not present, a new app will be created on the appstore instead of updated. You can look up the ocsid in the app page URL, e.g.: **http://apps.owncloud.com/content/show.php/News?content=168040** would use the ocsid **168040**

## Running tests
After [Installing PHPUnit](http://phpunit.de/getting-started.html) run:

    phpunit -c phpunit.xml
