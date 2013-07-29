<?php
/** 
 * SimplifyPermissionProvider
 * Provides an array of all default Simplify permissions and allows the insertion of custom permissions
 * 
 * @package simplify
 */
class SimplifyPermissionProvider implements PermissionProvider {
  
  //Custom permissions and paths
  static $custom_permissions = array();
  static $custom_css_path = null;
  static $custom_js_path = null;
  static $removeEnabled = true;
  
  //list of extended default permissions, with logical groups 
  static $default_permissions = array(
		  "Global" => array(
			  "SIMPLIFY_DISABLED" => "Disable all Simplify permissions",
			  "SIMPLIFY_HIDE_HELP" => "Hide the Help menu",
			  "SIMPLIFY_HIDE_LOGO" => "Hide the logo",
			  "SIMPLIFY_HIDE_HEADER" => "Hide the header",
			  "SIMPLIFY_HIDE_FOOTER" => "Hide the footer",
			  "SIMPLIFY_HIDE_DRAFT" => "Hide the Draft Site link", 
			  "SIMPLIFY_HIDE_PUBLISHED" => "Hide the Published Site link",
			  "SIMPLIFY_HIDE_VERSION" => "Hide the version and link on footer",
			  "SIMPLIFY_HIDE_PROFILE" => "Hide the Profile link"
		  ),
		  
		  "Tree" => array(
		  	  "SIMPLIFY_HIDE_NON_EDIT_PAGES" => "Hide pages this group cannot view or edit",
			  "SIMPLIFY_HIDE_CREATE" => "Hide the Create button",
			  "SIMPLIFY_HIDE_SEARCH" => "Hide the Search button",
			  "SIMPLIFY_HIDE_BATCH_ACTIONS" => "Hide the Batch Actions button",
			  "SIMPLIFY_SHOW_CREATE_OPEN" => "Show Create page dialog by default",
			  "SIMPLIFY_HIDE_TREE_OPTIONS" => "Hide all tree checkboxes",
	  		  "SIMPLIFY_DRAGGABLE_ON" => "Turn on drag & drop reordering by default",
			  "SIMPLIFY_HIDE_DRAGGABLE" => "Hide the drag & drop reordering checkbox"
		  ),
		  
		  "Tree Pane" => array (
			  "SIMPLIFY_HIDE_PANE_SITETREE" => "Hide Site Content and Structure",
			  "SIMPLIFY_HIDE_HEADING_SITETREE" => "Hide the heading Site Content and Structure",
			  "SIMPLIFY_HIDE_PANE_KEY" => "Hide the tree Key",
			  "SIMPLIFY_HIDE_PANE_VERSIONS" => "Hide Page Version History",
			  "SIMPLIFY_HIDE_PANE_REPORTS" => "Hide Site Reports"
		  ),
		  
		  "Security" => array (
		  	"SIMPLIFY_SECURITY_HIDE_CREATE" => "Hide the Create group button",
			"SIMPLIFY_SECURITY_HIDE_DELETE" => "Hide the Delete group button",
			"SIMPLIFY_SECURITY_HIDE_DRAGGABLE" => "Hide the drag & drop reordering checkbox",
			"SIMPLIFY_SECURITY_HIDE_MEMBERS" => "Hide the Members tab",
			"SIMPLIFY_SECURITY_HIDE_PERMISSIONS" => "Hide the Permissions tab",
			"SIMPLIFY_SECURITY_HIDE_IP" => "Hide the IP Address tab",
			"SIMPLIFY_SECURITY_HIDE_SIMPLIFY" => "Hide the Simplify tab"			
		  ),
		  
		  "HTML Editor" => array (
		  	"SIMPLIFY_CUSTOM_HTML_EDITOR" => "Use the following button lists to customise the HTML editor buttons"
		  )
    	);
		
  /**
   * SimplifyPermissionProvider
   * Stub constructor
   */
  function SimplifyPermissionProvider() {
  }

  	/**
	 * Get the list of custom permissions
	 *
	 * @return array customPermissions list of custom permissions
	 */
	function getCustomPermissions() {
  		return self::$custom_permissions;
  	}

  	/**
	 * Get the custom style sheet path
	 *
	 * @return string customCSSPath the custom CSS path
	 */
	function getCustomCSSPath() {
		return self::$custom_css_path;
	}

  	/**
	 * Get the custom javascript path
	 *
	 * @return string customJSPath the custom JS path
	 */	
	function getCustomJSPath() {
		return self::$custom_js_path;
	}  

	/**
	 * setCustomPermissions
	 * Sets the list of custom permissions and the css and js paths
	 * 
	 * @param array customPermissions List of custom permissions
	 * @param string customCSSPath The custom CSS path
	 * @param string customJSPath The custom JS path 
	 * */
  function setCustomPermissions($customPermissions, $customCSSPath, $customJSPath) {
  	self::$custom_permissions = $customPermissions;
	self::$custom_css_path = $customCSSPath;
	self::$custom_js_path = $customJSPath;
  }
  
  function getRemoveEnabled() {
  	return self::$removeEnabled;
  }
  
  function setRemoveEnabled($removeEnabled) {
  	self::$removeEnabled = $removeEnabled;
  }
  
  //Merges default and any other extended permissions
  function mergedPermissions() {
  	$permissions = array_merge( 
		self::$default_permissions,
		//TODO: Can't impl this until there is an easy way to override SiteTreeFilterPageTypeField in CMSMain.php
		self::pageCreationPermissions()
	);
	
	return $permissions;
  }
  
  //Converts a list of extended permissions into a flat key => value array
  //with Simplify identifier and grouping title
  function formatPermissions($permissions) {
	//For SS v2.4.0 and greater, return an array with a category for new Permission style,
	//for older version, just text
	//no easy way to determine version..so use existance of sort_permissions method in 2.4.0 only
	//to detect version
	//TODO: Use version instead (see SapphireInfo) (but requires extra parsing work)
	$ver240 = method_exists(new Permission(), "sort_permissions");
	
	$formattedPerms = array();
	foreach($permissions as $title => $grouping) {
		foreach ($grouping as $code => $label) {

			if ($ver240) {
				$formattedPerms[$code] = array(
					"category" => "Simplify - {$title}",
					"name" => "{$label}"
				);			
			} else {
				$formattedPerms[$code] = "Simplify - {$title} - {$label}";
			}
		}
	}	
	return $formattedPerms;
  }
  
 //Loop through all page types to create list of extended permissions for disabling creation
  function pageCreationPermissions() {
	$pageCreation = array(
		"Page Creation" => array()
	);
	
	//Get all the page types
	//(Not done with SiteTree::page_type_classes as this will remove already hidden pages)
	$classes = ClassInfo::getValidSubClasses();
	array_shift($classes);

	foreach($classes as $class) {
		$code = "SIMPLIFY_NO_CREATE_" . $class;
		$label = "Hide create " . $class;
		$pageCreation["Page Creation"][$code] = $label;
	}
	
	return $pageCreation;
  }
  
  
	/**
	 * providePermissions
	 * Returns a list of all default Simplify & custom permissions (flat, NOT extended)
	 * @return array A list of all default Simplify & custom permissions
	 */   
  function providePermissions() {
	
	$permissions = array_merge( 
		self::$custom_permissions, 
		self::formatPermissions(self::mergedPermissions())
	);
	
	return $permissions;
	
	
  }
}

?>