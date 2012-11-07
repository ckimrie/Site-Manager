define(["jquery", 'site_configs', "../lib/Site"], function($, site_configs, Site) {

	var sites = [];

	//Initialise eachone
	$.each(site_configs, function(i, site_config) {
		var s = new Site(site_config);
		s.setParentDiv($("#site-"+s.config.site_id));
		s.ping().done(function(data){
			s.parentDiv.find(".app_version").text(data.app_version);
		});
		sites.push(s);
	});





});


