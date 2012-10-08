
define(['require', 'jquery', './Site_base'], function(require, $, Site_base) {

	function Site(site_config) {

		this.config = {};
		this.parentDiv = $("body"); //Used for automatically updating status indicators

		$.extend(this.config, site_config);

		this.XID = SM.XID;
		this.encryptionServiceUrl = SM.js_encryption_api + "&local_site_id="+this.config.site_id;
		this.decryptionServiceUrl = SM.js_decryption_api + "&local_site_id="+this.config.site_id;
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

	Site.fn.fieldgroups = function() {
		return this.get("fieldgroups");
	};

	Site.fn.fields = function(fieldgroup_id) {
		return this.get("fields", {group_id : fieldgroup_id});
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



	Site.fn.syncChannel = function(data) {
		//We need to fetch site 1 and 2's url data, so we have to fetch the current
		//SM instance in order to access this without needing an extra HTTP request to
		//remote site
		var SyncManager = require("../lib/SyncManager"),
			sm = SyncManager.getInstance();

		//Remove data that we have added:
		delete data.sync_label;
		delete data.meta_label;
		delete data.fields;


		//Remove data hazardous to data integrity
		delete data.channel_id;
		delete data.site_id;

		//Set certain fields to reasonable values
		data.total_entries = "0";
		data.total_entries = "0";

		//Channel URL
		if(sm.direction === "right") {
			from	= sm.sites[0].site;
			to 		= sm.sites[1].site;
		} else {
			from	= sm.sites[1].site;
			to 		= sm.sites[0].site;
		}
		//Replace from base URL with to base URL
		data.channel_url = String(data.channel_url).replace(from.config.base_url, to.config.base_url);


		return this.post("create_channel", data);
	};



	/**
	 * Syncronise Category Group and Categories
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {object}     data
	 * @return {object}		jQuery Deferred
	 */
	Site.fn.syncCategoryGroup = function(data) {

		//Remove data that we have added:
		delete data.sync_label;
		delete data.meta_label;
		delete data.fields;
		delete data.total_categories;


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
	 * Synchronise FieldGroup
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {object}    data
	 * @return {object}          jQuery Deferred
	 */
	Site.fn.syncFieldGroup = function(data) {

		//Remote data we have added
		delete data.sync_label;
		delete data.meta_label;

		//Remove data hazardous to data integrity
		delete data.group_id;


		return this.post("create_fieldgroup", data);
	};


	/**
	 * Synchronise Field
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {integer}    group_id
	 * @param  {object}		data
	 * @return {object}             jQuery Deferred
	 */
	Site.fn.syncField = function(group_id, data) {

		//Remote data we have added
		delete data.sync_label;
		delete data.meta_label;

		//Remove data hazardous to data integrity
		delete data.field_id;
		delete data.group_id;
		delete data.site_id;

		return this.post("create_field", data, {group_id : group_id});
	};


	/**
	 * Page Configs
	 */
	Site.fn.setParentDiv = function(parent) {
		this.parentDiv = $(parent);
	};

	return Site;
});