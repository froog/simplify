<?php
/** 
 * SimplifyGroupDecorator
 * Decorates Group, adding the Simplify tab and building all child tabs and checkboxes based on permissions
 * Also adds HTMLEditor tab and Field tab
 * 
 * @package simplify
 */
class SimplifyGroupDecorator extends DataExtension {

	static $db = array(
		"HTMLEditorLine1" => "Text",
		"HTMLEditorLine2" => "Text",
		"HTMLEditorLine3" => "Text",
		"SimplifyDefaultsLoaded" => "Boolean"
	);

	/**
	 * default HTML editor buttons, stolen from HtmlEditorConfig.php because they're protected and theres no get :(
	 * TODO - this also includes new ss buttons + changes made to the defaults - if they change this will break stuff
	*/
	static $editor_buttons = array(
		1 => array('bold','italic','underline','strikethrough','separator','justifyleft','justifycenter','justifyright','justifyfull','styleselect','formatselect','separator','bullist','numlist','outdent','indent','blockquote','hr','charmap'),
		2 => array('undo','redo','separator','cut','copy','paste','pastetext','pasteword','spellchecker','separator','ssimage','ssflash','sslink','unlink','anchor','separator','advcode','search','replace','selectall','visualaid','separator'),
		3 => array('tablecontrols')
	);

	/**
	 * Set defaults if initial load (on ALL groups) 
	 */
	public static function set_html_editor_defaults() {
		if (DataObject::get_one("Group", "\"SimplifyDefaultsLoaded\" = 0")) {
			$line = implode(",", self::$editor_buttons[1]);
			DB::query("update \"Group\" set \"HTMLEditorLine1\" = '{$line}'");
			$line = implode(",", self::$editor_buttons[2]);
			DB::query("update \"Group\" set \"HTMLEditorLine2\" = '{$line}'");
			$line = implode(",", self::$editor_buttons[3]);
			DB::query("update \"Group\" set \"HTMLEditorLine3\" = '{$line}'");
			DB::query("update \"Group\" set \"SimplifyDefaultsLoaded\" = 1");						
		}
	}

	/*function updateCMSActions(FieldSet &$actions) {
		print_r($actions);
		$actions = null;
	}*/

	/**
	 * Add the Simplify Tab to the Group edit page
	 * 
	 * @param FieldSet $fields	List of CMS fields to update 
	 */
    public function updateCMSFields(FieldList $fields) {

        if ($this->owner->class == "Group") {
			
			//print_r($this->owner->Permissions());
			
			$groupID = $this->owner->ID;

			//Only remove fields if Simplify isn't disabled
			if (!SimplifyPermission::check("SIMPLIFY_DISABLED")) {
				//Check if any Simplify Permissions are disabling Secutity Group fields..
				if (SimplifyPermission::check("SIMPLIFY_SECURITY_HIDE_MEMBERS")) $fields->removeByName("Members");
				if (SimplifyPermission::check("SIMPLIFY_SECURITY_HIDE_PERMISSIONS")) $fields->removeByName("Permissions");
				if (SimplifyPermission::check("SIMPLIFY_SECURITY_HIDE_IP")) $fields->removeByName("IP Addresses");
				//If the permisison to hide the Simplify tab itself is set, exit now - as we don't want to create the tab
				if (SimplifyPermission::check("SIMPLIFY_SECURITY_HIDE_SIMPLIFY")) return;
			}

			//Create the Simplify TabSet
			$fields->addFieldToTab("Root", new TabSet("Simplify"));			
			
			//loop Through permissions and build header tabs and checkboxes
			foreach(SimplifyPermissionProvider::mergedPermissions() as $title => $grouping) {
				$tab = str_replace(" ", "", $title);
				$fields->findOrMakeTab("Root.Simplify.{$tab}");
				
				foreach ($grouping as $code => $label) {
					//See if perm exists
					$perm = DataObject::get_one("Permission", "\"Code\"='{$code}' AND \"GroupID\"={$groupID}");
					$setChecked = "";
					if ($perm) { 
						$checked = 1;
						$setChecked = "checked='checked'";
					} else {
						$checked = 0;
					}
					//$fields->addFieldToTab("Root.Simplify.{$tab}", new CheckboxField($code."|".$groupID, $label, $checked));
					
					//TODO: Should be line above, have to hack for now with onclick to avoid race cond.
					
					$fields->addFieldToTab("Root.Simplify.{$tab}", new LiteralField(
						$code."|".$groupID,
						"<p id='{$code}|{$groupID}' class='checkbox'>
							<input type='checkbox' value='1' {$setChecked} name='{$code}' onclick='Simplify.PermissionToggle(this)'/>
							<label class='right' for='{$code}'>{$label}</label>							
						</p>"
					));
				}
			}				

			//Add button lists to the HTML Editor tab
			$fields->addFieldsToTab("Root.Simplify.HTMLEditor", array(
				new TextField("HTMLEditorLine1", "Line 1"),
				new TextField("HTMLEditorLine2", "Line 2"),
				new TextField("HTMLEditorLine3", "Line 3"),
				new LiteralField("HTMLNote", "
					<p class='simpHead'>Default Buttons</p>
					<p class='simpHead'>Line 1</p>
					<p class='default1'>".implode(",", self::$editor_buttons[1])."</p>
					<p class='simpHead'>Line 2</p>
					<p class='default2'>".implode(",", self::$editor_buttons[2])."</p>
					<p class='simpHead'>Line 3</p>
					<p class='default3'>".implode(",", self::$editor_buttons[3])."</p>
					<p><button class='simplifyHtmlDefaults action' type='button'>Reset to defaults</button></p>										
					")
			));

			//Add select/deselect all to Page Creation
			$pageCreation = $fields->findOrMakeTab("Root.Simplify.PageCreation");
			$firstField = $pageCreation->Fields()->First();
			$fields->addFieldToTab("Root.Simplify.PageCreation", 
									new LiteralField("SelectDeselect", "<button class='simplifyPageCreationAll'>Select all</button><button class='simplifyPageCreationNone'>Deselect all</button>"),
									$firstField->getName()
			);			
			
			
			//TODO - these are future niceities..impl them!
			//$fields->addFieldToTab("Root.Simplify", new CheckboxField("FieldTreeInherit", "Inherit Permissions"));
			//$fields->addFieldToTab("Root.Simplify", new CheckboxField("FieldTreeDisable", "Disable instead of hiding"));
			
			//Create Field Tree tab
			
			//This is hacky until SS 2.4 comes with its improved admin JS
			//Tree is loaded by AJAX button as its too big to load whole tree
			//TODO: Eventually replace with tree with AJAX branches
			
			$fields->addFieldsToTab("Root.Simplify.Fields", array(
				new LiteralField("FieldTreeNote", 
					"<p>Hide the following Pages, Tabs and Fields from this group (in the CMS).</p>"),			
				new LiteralField("TreeActivator", 
					'<div id="TreeActivator"> 
							<input class="simplifyGroupCode" type="hidden" name="groupCode" value="' .$this->owner->Code . '"> 
							<button onclick="Simplify.TreeActivator()" type="button" class="action">Load Field Tree...</button>
					</div>')	
			));
		}
	}
}

?>
