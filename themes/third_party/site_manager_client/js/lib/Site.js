
define(['jquery', './Site_base'], function($, Site_base) {

	function Site(site_config) {
		
		this.config = {};


		$.extend(this.config, site_config);
	}

	Site.prototype = Site_base;

	return Site;
});