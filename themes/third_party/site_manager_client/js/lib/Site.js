
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

	Site.fn.categorygroups = function() {
		return this.get("categorygroups");
	};

	Site.fn.installation_details = function() {
		return this.get("installation_details");
	};


	Site.fn.addons = function() {
		return this.get("addons");
	};



	/**
	 * Sync Methods
	 */

	Site.fn.syncCategoryGroup = function(data) {

		//Remove data that we have added:
		delete data.sync_label;
		delete data.fields;


		//Remove data hazardous to data integrity
		delete data.group_id;
		delete data.site_id;

		//Make the categories POST friendly
		for(var i = 0; i < data.categories.length ; i++){
			$.each(data.categories[i], function(key, value) {
				data['categories[' + i + '][' + key + ']'] = value;
			});
		}


		delete data.categories;

		return this.post("create_categorygroup", data);
	};


	/**
	 * Page Configs
	 */
	Site.fn.setParentDiv = function(parent) {
		this.parentDiv = $(parent);
	};

	return Site;
});