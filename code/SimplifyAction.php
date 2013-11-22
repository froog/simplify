<?php
/**
 * SimplifyAction
 * Provides url actions to draw the Simplify Field Tree, and toggle Permissions on or off
 * 
 *  @package simplify
 */
class SimplifyAction extends Controller {
 
	static $group = null;

    public static $allowed_actions = array(
        "drawTree"
    );

	/**
	 * url action function that calls PageTree with the given group
	 * action: SimplifyAction/drawTree/ID action where ID is the group Code
	 * eg; simplify/drawTree/administrators
	 * 
	 * @return string A fully formatted tree as a HTML list
	 */
	public function drawTree() {

		$params = $this->getURLParams();
		//ID = Group Code
		$groupCode = $params["ID"];
		$group = DataObject::get_one("Group", "\"Code\" = '{$groupCode}'");
		if ($group) {
			return self::PageTree($group);
		}
	}
	
	/**
	 * url action function that toggles a given field permission on or off
	 * action: simplify/toggleFieldPermission/ID
	 * where ID = Page|Name|Type|GroupID
	 * eg; simplify/toggleFieldPermission/Page|Behaviour|1
	 * TODO: the pipe delimiting is rather eh. refactor.
	 * 
	 */
	public static function toggleFieldPermission() {
		//ID = Permission "Page|Name|Type|GroupID"
		$code = explode("|", Director::urlParam("ID"));
		$page = $code[0];
		$field = $code[1];
		$type = $code[2];
		$groupID = $code[3];
		
		//Does perm exist - delete if so 
		$perm = SimplifyPermission::checkField($page, $field, $type, $groupID); 
		if ($perm) {
			$perm->delete();		
		} else {
			//it doesn't exist, create it 
			$perm = new SimplifyPermission;
			$perm->HidePage = $page;
			$perm->HideName = $field;
			$perm->HideType = $type;
			$perm->GroupID = $groupID;
			$perm->write();
		}
	}

	/**
	 *  Display a tree of all page types and their children, with perms
	 *
	 *  @param Group group show permissions related to this group
	 *  @return string A fully formatted tree as a HTML list	
	*/
	public static function PageTree($group = null) {
		
		self::$group = $group;
		//Get all the page types
		$classes = SiteTree::page_type_classes();
		$pageTreeList = array();
		
		//We don't want to remove the fields from this list, so disable the remove
		SimplifyPermissionProvider::setRemoveEnabled(false);
		
		//Get an instance of each page type, add to an array
		//TODO: This works, but only returns the initial field state of the objects
		//      Might be a better way to scrape ALL the possible fields?
		foreach($classes as $class) {
			$instance = singleton($class);
			if($instance instanceof HiddenClass) continue;
			if(!$instance->canCreate()) continue;
			
			$pageTreeList[] = $instance;
		}

		//Get the children of each page type and return it as an UL
		$pageTree = self::getChildrenAsUL($pageTreeList, 0, " id='perm-tree' class='tree' ", "SiteTree");

		//Re-enable the remove
		SimplifyPermissionProvider::setRemoveEnabled(true);
		
		return $pageTree;
	}
	
	/** 
	 * 
	 * Custom getChildrenAsUL - specific for Pages/Tabsets/Tabs/Fields
	 * TODO this is very slow - improve it!
	 * TODO could load branches via AJAX instead
	 */
	public static function getChildrenAsUL($fields, $level = 0, $ulExtraAttributes = null, $parentPage, &$itemCount = 0) {
		$output = "";
		$hasNextLevel = false;
		//Set to true to remove any node from being displayed. Its children still will be.
		$removeNode = false;
		
		//Remove Root, as its not really needed and confuses this tree
		if (is_a($fields, "FieldSet") && is_a($fields->First(), "TabSet")) {
			$firstField = $fields->First();
			$firstField = method_exists($firstField, "getName") ? $firstField->getName() : "";
			if ($firstField == "Root"){
		 		$removeNode = true;
			}
		}
		
		if (!$removeNode) $output = "<ul {$ulExtraAttributes}>\n";
		$ulExtraAttributes = null;
		
		foreach($fields as $field) {
			$css = '';
			$display = '';
			$recurse = false;
			$name = '';			
			$type = '';

			//Handle Page classes and children (getCMSFields)
			if (is_a($field, "Page")) {
				$css .= "tree-page ";
				$recurse = true;
				$name = $field->class;
				$display = $field->class;				
				$parentPage = $field->class;
				$children = $field->getCMSFields(null);				
			} else 
			
			//Handle TabSet classes and children (Tabs)
			if (is_a($field, "TabSet")) {
				$css .= "tree-tabset ";
				$recurse = true;
				$display = method_exists($field, "getName") ? $field->getName() : $field->class;
                $name = $display;
				$children = $field->Tabs();	
			} else
			
			//Handle Tab classes and children (Fields)
			if(is_a($field, "Tab")) {
				$css .= "tree-tab ";
				$recurse = true;
				$display = method_exists($field, "getName") ? $field->getName() : $field->class;
				$name = $display;
				$children = $field->Fields();
			} else
			
			//Handle all FormField subclasses - excluding LiteralField
			//If the class doesn't have a Title, display the class instead
			//If the class has a Name, display that in brackets afterwards (maybe, comm for now)
			if(is_subclass_of($field, "FormField") and !is_a($field, "LiteralField")) {
				$title = method_exists($field, "Title") ? $field->Title() : $field->class;
				$name =  method_exists($field, "getName") ? $field->getName() : $field->class;
				if (!$title) {
					$title = $field->class;
				}
				$css .= "tree-field ";
				$display = $title."(".$field->class.")";
			} else 

			//Handle LiteralField classes - the content is HTML, so convert to raw first
			if(is_a($field, "LiteralField")) {
				$css .= "tree-literal ";
				$name =  method_exists($field, "getName") ? $field->getName() : $field->class;
				$display = Convert::xml2raw($field->getContent());
			} else { 
			//If the item isn't any of the above classes, we don't know what it is...
				$css .= "tree-unknown ";
				$name = method_exists($field, "getName") ? $field->getName() : $field->class;
				$display = $field->class." is an unknown type...";
			}

			//Find out if this field has a SimplifyPermission entry for the given group
			if (SimplifyPermission::checkField($parentPage, $name, $field->class, self::$group)) {
				$css .= 'selected ';
			}
			
			//Build the page|field|type|group key 
			$code = $parentPage . "|" . $name . "|" . $field->class . "|" . self::$group->ID;
			
			//Build the node
			if (!$removeNode) $output .= "<li class='{$css}'><a href='#' rel='{$code}'>{$display}</a>\n";
			
			//Do the recursive call
			if ($recurse) {
				$output .= self::getChildrenAsUL($children, $level+1, $ulExtraAttributes, $parentPage);
			}
			
			if (!$removeNode) $output .= "</li>\n";
			
			$itemCount++;
		}

		if (!$removeNode) $output .= "</ul>\n";

		return $output;
	}
}
?>
