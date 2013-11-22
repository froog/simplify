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
			  "SIMPLIFY_HIDE_SUBSITES_DROP" => "Hide the Subsites dropdown"
		  ),
		  
		  "Pages" => array(
		  	  "SIMPLIFY_HIDE_NON_EDIT_PAGES" => "Hide pages this group cannot view or edit",
              "SIMPLIFY_HIDE_EDIt_TREE" => "Hide the Edit Tree button",
              "SIMPLIFY_HIDE_ADD_NEW" => "Hide the Add New button",
			  "SIMPLIFY_HIDE_MULTI_SELECTION" => "Hide the Multi-selection button",
			  "SIMPLIFY_HIDE_FILTER" => "Hide the Filter panel",
	  		  "SIMPLIFY_DRAGGABLE_OFF" => "Turn off drag & drop reordering",
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
	public static function getCustomPermissions() {
  		return self::$custom_permissions;
  	}

  	/**
	 * Get the custom style sheet path
	 *
	 * @return string customCSSPath the custom CSS path
	 */
    public static function getCustomCSSPath() {
		return self::$custom_css_path;
	}

  	/**
	 * Get the custom javascript path
	 *
	 * @return string customJSPath the custom JS path
	 */
    public static function getCustomJSPath() {
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
  
  public static function getRemoveEnabled() {
  	return self::$removeEnabled;
  }
  
  public static function setRemoveEnabled($removeEnabled) {
  	self::$removeEnabled = $removeEnabled;
  }
  
  //Merges default and any other extended permissions
  public static function mergedPermissions() {
  	$permissions = array_merge( 
		self::$default_permissions,
		//TODO: Can't impl this until there is an easy way to override SiteTreeFilterPageTypeField in CMSMain.php
		self::pageCreationPermissions()
	);
	
	return $permissions;
  }
  
  //Converts a list of extended permissions into a flat key => value array
  //with Simplify identifier and grouping title
  public static function formatPermissions($permissions) {
	$formattedPerms = array();
	foreach($permissions as $title => $grouping) {
		foreach ($grouping as $code => $label) {

            $formattedPerms[$code] = array(
                "category" => "Simplify - {$title}",
                "name" => "{$label}"
            );
		}
	}	
	return $formattedPerms;
  }
  
 //Loop through all page types to create list of extended permissions for disabling creation
  public static function pageCreationPermissions() {
	$pageCreation = array(
		"Page Creation" => array()
	);
	
	//Get all the page types
	//(Not done with SiteTree::page_type_classes as this will remove already hidden pages)
	$classes = ClassInfo::getValidSubClasses("SiteTree");

	foreach($classes as $class) {
        //Exclude SiteTree from the list
        if ($class != "SiteTree") {
            $code = "SIMPLIFY_NO_CREATE_" . $class;
            $label = "Hide create " . $class;
            $pageCreation["Page Creation"][$code] = $label;
        }
	}
	
	return $pageCreation;
  }
  
  
	/**
	 * providePermissions
	 * Returns a list of all default Simplify & custom permissions (flat, NOT extended)
	 * @return array A list of all default Simplify & custom permissions
	 */   
    public function providePermissions() {
        $permissions = array_merge(
            self::$custom_permissions,
            self::formatPermissions(self::mergedPermissions())
        );

        return $permissions;
  }
}

?>