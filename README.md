simplify-silverstripe
=====================

Simplify is a module for the open source Silverstripe CMS (www.silverstripe.org) It allows for finer grained control over the CMS than the default install.

Features:

* Simplify the Silverstripe admin, provides an easy way of turning features on and off
* Make your CMS Admin easier to use for non-technical end-users
* Permission based, so you can enable options for some users but not others.
* Over 24 new permissions to fully configure how the admin looks
* Easily add in your own custom Simplify permissions
* Supports i18n of permission descriptions

Requirements
* Silverstripe 2.3.1+
(May work on older versions, has not been tested on any previous to this)

Installation Instructions
Manually: Extract the simplify folder into the top level of your site, and visit /dev/build?flush=all to rebuild the database.
Composer/Packagist: TODO: Write this

Quick start
* After installing Simplify, login to the admin interface as a full admin user
* Click on the Security menu
* Click on the Group you want to Simplify, or create a new one.
* Click on the Simplify tab
* Browse the available tabs and options, and select some you find interesting
* Save the group and logout
* Login as one of your users and see the difference!
