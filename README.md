Wireless Universal Resource File (WURFL) Auto Update Script
=================

First, you will need to download the [WURFL Database PHP Code](http://sourceforge.net/projects/wurfl/files/WURFL%20Database/1.4.4/ "Go to Sourceforge"). I've copied their file into this repo (wurfl-dbapi-1.4.4.0.zip) should there be any issues downloading it from Sourceforge.

You will need to do an initial installation. To do this, just go to wherever you installed WURFL and access their admin section.  e.g. http://wurfl.mywebsite.com/admin/

__Note:__ I personally like putting WURFL on its own subdomain so I can hit it from different projects like an API.

Once installed, you can just copy this repo directly into the root of WURFL you extracted from the zip file.

You will have the option to install the local XML file at this time.  However, the option to update from the remote site will no longer work, that is where this code comes in.

You can now either access the page directly, or better yet, setup a cron job to do this for you automatically:

`curl http://wurfl.mywebsite.com/update.php`

__Note:__ There is no HTML output from this script.  There are log files however, which are explained below.

This script hits SourceForge's RSS file and does the following:
---

* looks for the most recent download
* Gets the version number for that download
* Compares new version new version number to one we have installed
* If current and latest are identical an entry is added to ./logs/wurfl.log to give some transparency that an update was at least attempted
* If the there is an update, we download the zip file, save it locally, unzip it and copy it over to the WURFL data directory
* Then we run the WURFL API's ubdatedb to use the current updates XML file we just downloaded
* Then we tell WURFL to clear its cache so all new requests get the latest data ( cache will start rebuilding itself
* Cleanup is performed to remove files no longer needed

Requirements:
---

* __./update.php__ Does all the work for you, just make sure you hit this script somehow
* __./logs__ Folder needs to exist and be writable _(chmod 777)_
* __./downloads__ Folder needs to exist and be writable _(chmod 777)_

Log Files Explained:
---

* __./logs/wurfl_version.log__ - This will contain the version number of the last successful update ( it does not get updated unless we were able to download and use an update )

* __./logs/wurfl_updates.log__ - This keeps track of anytime WURFL is updated, and when.  Each line looks like this: Updated to version 2.3.4 ( 2013-07-19 01:23:45 )

* __./logs/wurfl_errors.log__ - This will keep track of any fatal errors that prevents updates

* __./logs/wurfl.log__ - This will keep track of when an update was requested, but none were needed ( helpful for those just wanting to make sure the updates are at least running ).
