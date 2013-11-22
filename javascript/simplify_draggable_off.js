/*
//append custom loader (appendLoader is in jsparty/loader.js)
appendLoader(function() {
		//this is bad - fix it!
		if (jQuery('.CMSMain #TreeTools #sortitems').attr('checked') == false) {
			jQuery('.CMSMain #TreeTools #sortitems').click();
		}
});

*/

(function($) {

    $('.cms-tree').entwine({

        onadd: function(){
            this._super();

            this.bind('before.jstree', function(e, data) {
                if(data.func == 'start_drag') {
                    //Stop drag n drop
                    e.stopImmediatePropagation();
                    return false;
                }
            });
        }
    });

}(jQuery));