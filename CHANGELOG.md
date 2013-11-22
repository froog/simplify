##Simplify Change Log##

1.3.0
 *

1.2.0
 *

1.1.0
 * Initial SilverStripe 3.x support

1.0.0
 * PostgreSQL support
 * If classes are removed and still have perms they won't error

0.0.8
 * Fixed SimplifyGroupDecorator to not apply perms if Simplify disabled
 * Now with SS 2.4 support
 * Permissions now toggled in Permissions tab if changed in Simplify tab

0.0.7
 * Fix to prevent Content tab being removed when Content field is being removed (in SimplifyDataObjectDecorator)
 * Fixed markingClasses() in SimplifyDataObjectDecorator to allow Pages to be hidden (using tree under Simplify/Fields tab)
 * Added Security tab and permissions to hide Security fields
 * Added Hide Delete and Unpublish buttons for specific groups to Access tab 

0.0.6
 * Added HTML Editor functionality - customise the buttons on the tiny MCE HTML Editor
 * Removed SimplifyCMSMainDecorator - moved code to SimplifyLeftAndMainDecorator
 * Removed Demo Set - was not an accurate indicator of what Simplify does anymore

0.0.5
 * added error checking for dodgy custom classes in SimplifyAction tree creation
 * Other minor bug fixes

0.0.4
 * Minor bug fixes

0.0.3
 * SimplifyPermission now no longers extends Permission - was causing too many issues
   Its only used by the tree for fields to hide.
   Normal Simplify permissions are still standard Permissions
 * Disabled overridding function markingClasses() in SimplifyDataObjectDecorator until it can be
   figured out why its causing issues - not a big deal, only affects disabling of Pages via the tree

0.0.2
  * Added helper tab + fields, so don't need to edit Permissions manually
  * Added tree to select Pages and Fields to be hidden
  * Added new permissions
  * Permissions can now be applied to Admin accounts

0.0.1
  * Initial alpha release




 
