define(["jquery", 'site_configs', "../lib/Site"], function($, site_configs, Site) {

	var sites = [];

	//Initialise eachone
	$.each(site_configs, function(i, site_config) {
		var s = new Site(site_config),
			id = s.config.site_id;

		s.setParentDiv($("#site-"+s.config.site_id));
		
		//Ping for activity
		//s.ping();
		
		//Fetch license
		s.installation_details().done(function(data) {
			s.updateUIStatus(true);	
			s.parentDiv.find(".app_version").text(data.app_version);
			s.parentDiv.find(".license_number").text(data.license_number);
		}).fail(function() {
			s.updateUIStatus(false);
		})

		sites.push(s);
	});
	




});
