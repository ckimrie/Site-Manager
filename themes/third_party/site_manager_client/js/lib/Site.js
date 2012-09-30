
define(['jquery', './Site_base'], function($, Site_base) {

	function Site(site_config) {
		
		this.config = {};
		this.parentDiv = $("body"); //Used for automatically updating status indicators

		$.extend(this.config, site_config);
	}

	Site.prototype = Site_base;
	Site.fn = Site.prototype;


	/**
	 * Remote Resources
	 */
	
	Site.fn.configuration = function() {
		return this.get("config");
	};


	Site.fn.channels = function() {
		return this.get("channels");
	};

	Site.fn.installation_details = function() {
		return this.get("installation_details");
	};


	Site.fn.addons = function() {
		return this.get("addons");
	};



	/**
	 * Page Configs
	 */
	Site.fn.setParentDiv = function(parent) {
		this.parentDiv = $(parent);
	};

	return Site;
});