/**
 * @author froog
 */

var $j = jQuery;
var Simplify = {};

$j(function() {

Simplify = {

	SimplifyHtmlReset : function() {
		$j('.simplifyHtmlDefaults').click(function() {
			$j('#Form_EditForm_HTMLEditorLine1')[0].value = $j(".default1")[0].innerHTML;
			$j('#Form_EditForm_HTMLEditorLine2')[0].value = $j(".default2")[0].innerHTML;
			$j('#Form_EditForm_HTMLEditorLine3')[0].value = $j(".default3")[0].innerHTML;
		});
	},

	TreeActivatorHook : function() {
		$j('#TreeActivator input').click(function() {
			Simplify.TreeActivator();
		});
	} ,
		
	TreeActivator : function() {
			$j('#TreeActivator').addClass('tree-loading');
	
			//load the tree via AJAX
			$j('#TreeActivator').load(			
				"admin/simplify/drawTree/" + $j('.simplifyGroupCode').val(),
				"",
				function() {
					$j('#TreeActivator').removeClass('tree-loading');
					
					//make list a tree
					behaveAs($('perm-tree'), Tree);
					behaveAs($('perm-tree'), MultiselectTreeFIX);
					
					//specific ver of the jsparty/tree/tree.js treeCloseAll func
					var candidates = $('perm-tree').getElementsByTagName('li');
	    			for (var i=0;i<candidates.length;i++) {
	        			var aSpan = candidates[i].childNodes[0];
	        			if(aSpan.childNodes[0] && aSpan.childNodes[0].className == "b") {
	           				if (!aSpan.className.match(/spanClosed/) && candidates[i].id != 'record-0' ) {
	               				aSpan.childNodes[0].onclick();
	           				}
	        			}
	    			}
					
					// add toggle perm handling code
					$j('#Root_Simplify_set #perm-tree a').click(function(){
						//this.rel holds the Page|Name|Group code for each Field permission
						//Do a simple AJAX get call to toggle it..
						$j.get(
							"admin/simplify/toggleFieldPermission/" + this.rel
						);
					});	
					

									
				}
			);	
			return false;	
	},
	
	PermissionToggle : function(obj) {
		//parent p id holds the permission code
		//Do a simple AJAX get call to toggle it..
		$j.get(
			"admin/simplify/togglePermission/" + $j(obj).parent().attr('id')
		);
		
		//Also ensure std perm is toggled too
		var stdPerm = $j("input[value='" + obj.name + "']");
		stdPerm.attr('checked', !stdPerm.attr('checked'));
	}
}
	
/*
 * TODO: Done this via simple old school onclick because bad mix of jQuery & Prototype makes 
 * event handling a pain. Refactor when CMS JS is improved.
 * 
	//Apply the hook to load the Simplify Field tree
	Simplify.TreeActivatorHook();
	
	//Reapply the hook if another group is picked in the main tree
	$j("#sitetree .Group").click(function() {
		Simplify.TreeActivatorHook();
	});
*/	

Simplify.SimplifyHtmlReset();	
	
})

