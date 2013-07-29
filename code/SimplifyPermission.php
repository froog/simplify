<?php
/**
 * SimplifyDataObjectDecorator
 * Creates a Simplify specific permission - used to hide Pages, Tabs and Fields
 * 
 * @package simplify
 */
class SimplifyPermission extends DataObject {
	
	static $db = array (
		"Code" => "Varchar",	//The generated Simplify permission code
		"HidePage" => "Text",	//The page classname to hide a field on
		"HideName" => "Text",	//The name of the field to hide
		"HideType" => "Text"	//The type of the field (Page, Tab, Tabset, Field, etc)
	);

	static $has_one = array(
		"Group" => "Group"
	);
	
	/**
	 * Get all SimplifyPermissions that refer to the $page DAO class
	 * 
	 * @param String page class name of the page to return perms for
	 * @return DataObjectSet Set of simplifyPermissions
	 *  
	 */		
	public static function getPermissionsByPage($page) {
		$member = Member::currentUser();
		$memberID = (is_object($member)) ? $member->ID : $member;
		 
		$groupList = Permission::groupList($memberID);
		if(!$groupList) return false;		
		$groupCSV = implode(", ", $groupList);

		$perms = DataObject::get("SimplifyPermission", 
			"\"HidePage\"='".$page."'  
			 AND \"GroupID\" IN ($groupCSV)");
			 
		return $perms;
	}

	/**
	 * Use the core Permission check method to see if the given Simplify Permission is set
	 * 
	 * @param object $code
	 * @return boolean true if the perm is set, false otherwise
	 */
	public static function check($code) {
		//admin_implies_all=false needs to be set due to Simplifys inverted security model:
		//By default Admin users can access everything, so check() would always return true.
		//This way admins can use Simplify perms correctly
        Config::inst()->update('Permission', 'admin_implies_all', false);
		
		$check =  Permission::check($code);
		
		//Reset this back - its a static on Permission, so would break normal permissions
        Config::inst()->update('Permission', 'admin_implies_all', true);
		
		return $check; 
	}
 
	/**
	 * Check to see if the given group has a specific page/field hide permission
	 * 
	 * @param String $page Class name of the page
	 * @param String $field Name of the field to hide
	 * @param String $type Type of the field to hide
	 * @param int|Group $group The group instance or group ID to check the perms for 
	 * @return SimplifyPermission The permission if found, false if not
	 */
	public static function checkField($page, $field, $type, $group) {
		if ($group) {
			
			if(is_numeric($group)) {
				$groupId = $group;
			} elseif($group instanceof Group) {
				$groupId = $group->ID;
			}
			
			return DataObject::get_one("SimplifyPermission", 
				"\"HidePage\" = '{$page}' AND \"HideName\" = '{$field}' AND " .
				"\"HideType\" = '{$type}' AND \"GroupID\" = '{$groupId}'");
		} else {
			return false;
		}
	}
}

?>