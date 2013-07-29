<?php
/**
 * SimplifyDataObjectDecorator
 * Decorates DataObject to remove fields from a Page
 * TODO: Might be better to make this more specific??
 * 
 * @package simplify
 */
class SimplifyDataObjectDecorator extends SiteTreeDecorator {

	
	function extraStatics() {
		return array(
			"many_many" => array (
				"SimplifyHideDeleteGroups" => "Group",
				"SimplifyHideUnpublishGroups" => "Group"
			)
		);
	}


	
	function updateCMSActions(FieldSet &$actions) {
		
		//Remove delete and/or unpublish buttons if set for this users group
		// Do this by getting the list of Groups set in the Access  tab, looping through
		// them and comparing them to the current members group
		$hideDeleteGroups = $this->owner->SimplifyHideDeleteGroups();
		$hideUnpublishGroups = $this->owner->SimplifyHideUnpublishGroups();
		$memberGroup = Member::currentUser()->Groups()->First();
		
		foreach($hideDeleteGroups as $deleteGroup) {
			if ($deleteGroup->Code == $memberGroup->Code) {
				$actions->removeByName("action_delete");
			}
		}
		
		foreach($hideUnpublishGroups as $unpublishGroup) {
			if ($unpublishGroup->Code == $memberGroup->Code) {
				$actions->removeByName("action_unpublish");
			}
		}		
	}
	

	function updateCMSFields(FieldSet &$fields) {
		//Add extra fields to Access tab to hide action buttons
		$fields->addFieldsToTab("Root.Access", array(
			new HeaderField('SimplifyHideDelete',_t('Smplify.HIDEDELETEHEADER', "Simplify - Hide Delete button from these users"), 2),
			new TreeMultiselectField("SimplifyHideDeleteGroups", 'Hide Delete Groups'),
			new HeaderField('SimplifyHideUnpublish',_t('Smplify.HIDEUNPUBLISHHEADER', "Simplify - Hide Unpublish button from these users"), 2),
			new TreeMultiselectField("SimplifyHideUnpublishGroups", 'Hide Unpublish Groups')			
		));
		
		//Get the list of options for the Page Type dropdown ("ClassName")
		$classList = $fields->dataFieldByName("ClassName")->getSource();
		
		//Ensure this decorated class is added to the dropdown(its the default) - this is because the code in 
		//SimplifyCMSMainDecorator may remove it
		$classList[$this->owner->class] = $this->owner->i18n_singular_name();
		$fields->dataFieldByName("ClassName")->setSource($classList);
		
		//Only remove fields if remove is enabled and Simplify isn't disabled
		if (SimplifyPermissionProvider::getRemoveEnabled() && !SimplifyPermission::check("SIMPLIFY_DISABLED")) {
			
			//Get all SimplifyPermissions that refer to this DAO class
			$hideFields = SimplifyPermission::getPermissionsByPage($this->owner->class);
			
			//Remove them..
			if ($hideFields) {
				foreach($hideFields as $hideField) {
					$dataFieldOnly = false;
					
					//TODO: Make better - fix to prevent Content tab being removed when Content field is being removed
					if ($hideField->HideName == "Content") {
						if ($hideField->HideType != "Tab" && $hideField->HideType != "TabSet") $dataFieldOnly = true;
					} 
					
					$fields->removeByName($hideField->HideName, $dataFieldOnly);
					
				} 
			}
		}
	}
	
	
	/**
	 * 
	 *This attaches custom classes to the tree items - use this to hide pages
	 * 
	 */
	public function markingClasses() {
		$classes = "";
		
		//Copy of markingClasses from Hierarchy.php (which extends DataObjectDecorator)
		if(!$this->owner->isExpanded()) {
			$classes .= " unexpanded";
		}
		if(!$this->owner->isTreeOpened()) {
			$classes .= " closed";
		}
		
		//Hide pages that have been selected in the top level of the Simplify fields tree -
		//These will be where the field = page, ie; HidePage = HideName
		$hideThisPage = DataObject::get("SimplifyPermission", 
			"HidePage = HideName AND HidePage = '{$this->owner->class}'");
			
		if ($hideThisPage) {
			$classes .= " hide";
		}
		return $classes;
	}
}

?>
