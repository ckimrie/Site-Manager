define(["jquery"], function() {

	//Cycle through all sites and ping to test connecton
	
		var site_id = window.SM.site_id,
			def = $.get(window.SM.js_api + "&js_method=ping_site&site_id="+site_id);

		def.success(function(data) {
			var status = $(".status").removeClass("active").filter(".live").addClass("active");
			setTimeout(function() {
				status.parent().addClass("loaded");
			}, 2000);

			$(".app_version").text(data.app_version);
			$(".total_time").text(data.total_time);
		});
		def.error(function() {
			$(".status").removeClass("active").filter(".offline").addClass("active").parent().addClass("loaded");
		});


});