
define(['jquery', './Site_base'], function($, Site_base) {

	function Site(site_config) {
		
		this.config = {};

		$.extend(this.config, site_config);
	}

	Site.prototype = Site_base;
	Site.fn = Site.prototype;


	Site.fn.configuration = function() {
		return this.get("config");
	};

	return Site;
});