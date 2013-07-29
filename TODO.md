----------------------------------------------------------------------------------------
Simplify - customise the Silverstripe admin interface
----------------------------------------------------------------------------------------

- HIGH - look at module maintainer docs, adhere to code standards

- Implement read-only option - forces entire admin panel to be read only.
	- currently, indiv perms can be set on each page in Access tab (who can edit)
	- this would force "Who can edit this page?" to no one for all pages if the SimpPerm is enabled...

For 2.4.0 version:

1 - DONE - Impl JS in PermissionCheckboxSetField.js to NOT pick inputs when full admin is picked
1 - Currently using hacky method in JS to toggle permissions - better to use actual Form save/load
	onclick event PermissionToggle attached in SimplifyGroupDecorator
1 - Roles should just work, but test

2 - Site Content and Structure renamed to 'Page Tree' - update perms in Tree Pane
	(func hasn't changed - just rename if version is >= 2.4.0) 
	
3 - Split Tree/Hide pages this group cannot view or edit into two? maybe...



BUGS/FEATURE REQUESTS:

- Create a "Hide single tab labels" option that uses CSS to hide single tabs
	Hide .tabstrip and border
- Create a "Don't allow top level page creation" checkbox. (or look to see if statics can_be_root and can_create work)
- simplify_draggable_on.js doesn't work in IE, needs improvement
- header and footer resize using JS - so hiding them leaves gaps - fix this
- in fresh install of SS, combined CSS doesn't seem to work?
- Look at using SimplifySiteTreeDecorator instead of SimplifyDataObjectDecorator (more specific)

PERMISSIONS TO IMPLEMENT
- Hide Navigation label fields, ensure it is updated with every Page name edit
- Hide Save/Publish/Delete buttons
- Move Page view to top, show Draft Site and Live(Published) Site
- Move logged in, and logout to top





NOTES

Translations
------------

Translations are enabled by Simplifyi18nEntityProvider.php

If you want to translate permission labels, run:
http://<mysite>/dev/tasks/i18nTextCollectorTask/?module=simplify
 
This creates a simplify/lang folder with en_US.php - simply copy and translate this
For more info, refer to http://doc.silverstripe.com/doku.php?id=i18n
 
If you have translated Simplify into any language, it would be great if you could email 
 me the translation and I'll add it into the next release.