//append custom loader (appendLoader is in jsparty/loader.js)
appendLoader(function() {
		//this is bad - fix it!
		if (jQuery('.CMSMain #TreeTools #sortitems').attr('checked') == false) {
			jQuery('.CMSMain #TreeTools #sortitems').click();
		}
});

