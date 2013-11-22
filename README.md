simplify
=====================

Simplify is a module for the open source SilverStripe CMS (www.silverstripe.org)
It allows for finer grained control over the CMS than the default install.

##Features##

* Simplify the SilverStripe admin, provides an easy way of turning features on and off
* Make your CMS Admin easier to use for non-technical end-users
* Permission based, so you can enable options for some users but not others.
* Over 24 new permissions to fully configure how the admin looks
* Easily add in your own custom Simplify permissions, using CSS & JS
* Supports i18n of permission descriptions

##Requirements##
 * SilverStripe 3.0.0+
   (for older versions of SilverStripe, use release 0.0.8)

##Installation##
* Manual: Extract the simplify folder into the top level of your site, and visit /dev/build?flush=all to rebuild the database.
* Composer/Packagist: Install composer and then run `composer require froog/simplify dev-master`

##Quick start##
* After installing Simplify, login to the admin interface as a full admin user
* Click on the Security menu
* Click on the Group you want to Simplify, or create a new one.
* Click on the Simplify tab
* Browse the available tabs and options, and select some you find interesting
* Save the group and logout
* Login as one of your users and see the difference!

##Notes##

Security: It's worth noting that the majority of Simplify permissions are just removed or hidden from the UI on the client side, using CSS and/or Javascript.
This means that while the controls may not be there, users could still run those functions, if they enabled them using web developer tools.
Simplify is not intended to securely prevent someone from doing a action, merely to hide the functionality from the average CMS user.
You should still configure the user/group with standard permissions with this in mind.


