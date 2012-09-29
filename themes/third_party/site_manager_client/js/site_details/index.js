define(["jquery", 'site_config', "../lib/Site"], function($, site_config, Site) {

	var site = new Site(site_config);


	//Ping Site
	site.ping();
});


