define(["jquery"], function() {

	//Cycle through all sites and ping to test connecton
	$(".sm-site").each(function() {
		var here = this;
			site_id = $(this).data("siteId"),
			def = $.get(window.siteManager.js_api + "&js_method=ping_site&site_id="+site_id);

		def.success(function() {
			$(here).find(".status").removeClass("active").filter(".live").addClass("active");
		});
		def.error(function() {
			$(here).find(".status").removeClass("active").filter(".offline").addClass("active");
		});
	})

});