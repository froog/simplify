<?php
/** 
 * SimplifyPermissionProvider
 * Ensures Simplify permissions are translatable
 * 
 * @package simplify
 */
class Simplifyi18nEntityProvider implements i18nEntityProvider {

	/**
	 * Simplifyi18nEntityProvider
	 * Stub constructor
	 */
	function Simplifyi18nEntityProvider() {
	}
	
	/**
	 * provideI18nEntities
	 * Creates an i18n entry for each permission in the namespace SIMPLIFY.permkey	 * 	  
	 * @return All permission as entites in an associative array
	 */
	function provideI18nEntities() {
		$entites = array();
			
		foreach (SimplifyPermissionProvider::providePermissions() as $permissionKey => $permission) {
			$entities["SIMPLIFY.".$permissionKey] = array (
				$permission
			);
		}
		return $entities;
	}
}
?>

