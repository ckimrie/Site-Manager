define(["jquery", 'site_config', "../lib/Site"], function($, site_config, Site) {

	var site = new Site(site_config);


	site.installation_details().done(function(data) {
		site.updateUIStatus(true);

		$(".app_version").text(data.app_version);
		$(".license_number").text(data.license_number);
		$(".is_system_on").text(data.is_system_on ? "Yes" : "No");
	});
});


