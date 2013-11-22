<?php
/** 
 * SimplifyLeftAndMainDecorator
 * 
 * Decorates LeftAndMain, applies the Simplify permissions using CSS and JS via alternateAccessCheck()
 * Also calls any code related to permissions
 * 
 * @package simplify
 */
class SimplifyLeftAndMainDecorator extends LeftAndMainExtension {
	
	private static $js_files = array();
	private static $css_files = array();

	/**
	 * checkPermissions
	 * loop through each permission, check if the current user has it, 
	 * and add the permission.js or permission.css file as a requirement
	 * 
	 * @param array $permissions List of permissions to check
	 * @param string cssPath ss relative path to the permissions css files
	 * @param string jsPath ss relative path to the permissions js files
	 * @return null
	 */	
	private static function checkPermissions($permissions, $cssPath, $jsPath) {
		//
		if ($permissions) {
			foreach ($permissions as $permission => $permissionValue) {
				if (SimplifyPermission::check($permission)) {
					$cssFile = $cssPath.strtolower($permission).'.css';
					$jsFile = $jsPath.strtolower($permission).'.js';

                    Requirements::css($cssFile);
					self::$css_files[] = $cssFile;

                    Requirements::javascript($jsFile);
					self::$js_files[] = $jsFile;
				}
			}
		}
	}

	/**
	 * Checks Simplify default and custom permission, also combines the CSS and JS.
	 * Called from LeftAndMain
	 * 
	 * @return null
	 */
    function init() {
		//add global js + css required by Simplify - these aren't permissions, just support code

        //DEPREACATED?
        //TODO - what does/did this do? No longer works with new 3.x JS
        // Look at simplify_global to see use of MultiselecttreeFIX
        //$treeJS = "simplify/javascript/simplify_multiselect_tree.js";
		//LeftAndMain::require_javascript($treeJS);
        //self::$js_files[] = $treeJS;

        $globalJS = "simplify/javascript/simplify_global.js";
        Requirements::javascript($globalJS);

		self::$js_files[] = $globalJS;

		//TODO -BUG FIX ME - uses old JS, need to fix this to work with new 3.x CMS JS
		//Block the JS file used to perform Permission/Full admin rights toggle - replace it with our own
		/*Requirements::block(SAPPHIRE_DIR . '/javascript/PermissionCheckboxSetField.js');
		$checkboxJS = "simplify/javascript/simplify_PermissionCheckboxSetField.js";
		LeftAndMain::require_javascript($checkboxJS);
		self::$js_files[] = $checkboxJS;
		*/
		
		$globalCSS = "simplify/css/simplify_global.css";
		Requirements::css($globalCSS);
		self::$css_files[] = $globalCSS;

		//Set defaults if initial load (on ALL groups)
		SimplifyGroupDecorator::set_html_editor_defaults();

		//only apply Simplify perms is they're not disabled
		if (!SimplifyPermission::check("SIMPLIFY_DISABLED")) {

            $permissionProvider = new SimplifyPermissionProvider();

			//check default permissions
			self::checkPermissions(
                $permissionProvider->providePermissions(),
				'simplify/css/', 
				'simplify/javascript/'
			);

			//check custom (user) permissions
			self::checkPermissions(
				SimplifyPermissionProvider::getCustomPermissions(),
				SimplifyPermissionProvider::getCustomCSSPath(),
				SimplifyPermissionProvider::getCustomJSPath() 
			);

			//Some permissions require code - execute that here

			//Hide the help menu
			if (SimplifyPermission::check("SIMPLIFY_HIDE_HELP")) {
				CMSMenu::remove_menu_item('Help');
			}
			
			//Get the HTML Editor button lists for this user and customise the editor
			if (SimplifyPermission::check("SIMPLIFY_CUSTOM_HTML_EDITOR")) {
				//Put all lines into an array, iterate over each - if they have content it will be "button1,button2,button3"
				//explode this into an array and use HtmlEditorconfig to set the line
				//TODO: this gets the first group the member belongs to - they may belong to many
				//need to sort out how this is handled	
				$group = Member::currentUser()->Groups()->First();
				$lines = array($group->HTMLEditorLine1, $group->HTMLEditorLine2, $group->HTMLEditorLine3);
				$config = HtmlEditorConfig::get('cms');
				$i = 1;
				foreach ($lines as $line) {
					$lineArray = array();
					if ($line) $lineArray = explode(",", $line);
					$config->setButtonsForLine($i, $lineArray);
					$i++;
				}	
			}

			//Hide page classes from create dropdown if set
			//First, get all no create permissions for the current user
			$groupList = Member::currentUser()->Groups()->getIdList();
			$groupCSV = implode(", ", $groupList);
	
			$perms = DataObject::get("Permission", 
				"\"Code\" like 'SIMPLIFY_NO_CREATE_%' AND \"GroupID\" IN ($groupCSV)");

			if ($perms) {
				foreach($perms as $perm) {
					//TODO - do this more elegantly
					$page = str_replace("SIMPLIFY_NO_CREATE_", "", $perm->Code);
					
					//This 'hack' uses the hide_ancestor static to remove itself
					//See SiteTree::page_type_classes() for the call
                    Config::inst()->update($page, "hide_ancestor", $page);

					//Note that this hides it from Behaviour/Page type which is bad - 
					//SimplifyDataObjectDecorator updateCMSFields ensures it is set
				} 
			}
		
	
			//TODO: this seems to fail, only adds the global css + js - fix
			
			//Combine js and css for live deployment
			/*
			Requirements::combine_files(
				'assets/simplify.css',
				self::$css_files
			);
			
			Requirements::combine_files(
				'assets/simplify.js',
				self::$js_files
			);
			*/
			
		}
	}
}

?>