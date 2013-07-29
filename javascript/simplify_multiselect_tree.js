/**
 * MODIFIED (or fixed) so classes can be correctly deselected if selected on load
 * 
 * Mix-in for the tree to enable mulitselect support
 * Usage: 
 *   - tree.behaveAs(MultiselectTree)
 *   - tree.stopBehavingAs(MultiselectTree)
 */
MultiselectTreeFIX = Class.create();
MultiselectTreeFIX.prototype = {
	initialize: function() {
		Element.addClassName(this, 'multiselect');
		this.MultiselectTreeFIX_observer = this.observeMethod('NodeClicked', this.multiselect_handleSelectionChange.bind(this));
		this.selectedNodes = { }
	},
	destroyDraggable: function() {
		this.stopObserving(this.MultiselectTreeFIX_observer);
	},
	
	multiselect_handleSelectionChange : function(selectedNode) {
		var idx = this.getIdxOf(selectedNode);
		
		if(selectedNode.selected || selectedNode.className.indexOf('selected') > -1) {
			selectedNode.removeNodeClass('selected');
			selectedNode.selected = false;
			delete this.selectedNodes[idx];

		} else {
			selectedNode.addNodeClass('selected');
			selectedNode.selected = true;
			this.selectedNodes[idx] = selectedNode.aTag.innerHTML;
		}
		
		return false;
	}
	
}