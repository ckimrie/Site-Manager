define(['jquery'], function($) {

	function SyncManager() {
		this.sites = [];
		this.comparison_key = "";
		this.sort_key = "";
	}



	/**
	 * Add Site for Comparision
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {object}    site  Site details object
	 * @param  {object}    dom   jQuery DOM Node
	 */
	SyncManager.prototype.add = function(site, dom) {
		this.sites.push({
			site : site,
			node : dom
		});
	};


	/**
	 * New comparison
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {string}    critera The criteria to use for comparision
	 * @return {mixed}            -
	 */
	SyncManager.prototype.compare = function(critera) {
		console.log("Compare", critera);
		if(critera === "channels") {
			this.comparison_key = "channel_name";
			this.sort_key = "channel_title";

			return this.compareData(this.sites[0].site.channels(), this.sites[1].site.channels());
		}
		if(critera === "fieldgroups") {
			this.comparison_key = "group_name";
			this.sort_key = "group_name";

			return this.compareData(this.sites[0].site.fieldgroups(), this.sites[1].site.fieldgroups());
		}
		if(critera === "categorygroups") {
			this.comparison_key = "group_name";
			this.sort_key = "group_name";

			return this.compareData(this.sites[0].site.categorygroups(), this.sites[1].site.categorygroups());
		}
	};


	/**
	 * Compare Remote Data
	 *
	 * This method takes two deferreds (from remote data request) and when both
	 * have resolved, compares the returned array of objects from each and sorts
	 * into a site_1 and site_2 data array that accounts for missing data from each
	 * site.
	 *
	 * The method reads from two instance variables to compare (this.comparison_key) and
	 * to sort (this.sort_key).  This allows this method to compare any two arrays of objects
	 * and successfully process them.
	 *
	 * The idea is that this data can be looped over to generate a comparison UI like so:
	 *
	 *		-------------------------
	 *		|	site_1	|  site_2	|
	 *		|-----------|-----------|
	 *		|	news	|	news	|
	 *		|	blog	|			|
	 *		|	about	|	about	|
	 *		|			|	events	|
	 *		-------------------------
	 *
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {object}    d1    jQuery Deferred for site 1
	 * @param  {object}    d2    jQuery Deferred for site 2
	 * @return {object}          jQuery Deferred that resolves with ordered data
	 */
	SyncManager.prototype.compareData = function(d1, d2) {
		var def = new $.Deferred(),
			here = this;


		//When data from both has been fetched
		$.when(d1, d2).done(function(remote_data_1, remote_data_2) {
			var i, j, o, blank,
				largest_channel_collection = 0,
				data = {
					site_1: [],
					site_2: []
				};




			//Go through site one and assign positions
			for(i = 0; i < remote_data_1.length; i++){

				//Add generalised label column based on sort_key
				remote_data_1[i].sync_label = remote_data_1[i][here.sort_key];

				//Add the left column
				data.site_1.push(remote_data_1[i]);

				//Right column: is it an existing channel in site_2 or blank?
				blank = true;
				for(j = 0; j < remote_data_2.length; j++) {
					if(!remote_data_2[j]) continue;

					if (remote_data_2[j][here.comparison_key] === remote_data_1[i][here.comparison_key]) {
						//Create generalised label based on sort key
						remote_data_2[j].sync_label = remote_data_2[j][here.sort_key];

						data.site_2.push(remote_data_2[j]);
						blank = false;

						//We unset the site_2 data that have been already added since we need
						//to loop through them later to add the left overs to site_1
						delete remote_data_2[j];
						break;
					}
				}
				//If we didnt find the channel in site_2, then create a blank dummy entry
				if (blank) {
					o = {};
					o[here.sort_key]		= remote_data_1[i][here.sort_key];
					o[here.comparison_key]	= remote_data_1[i][here.comparison_key];
					o['blank']				=  true;
					data.site_2.push(o);
				}
			}

			//Go through site_2 assign the left over data
			for(i = 0; i < remote_data_2.length; i++){
				if(!remote_data_2[i]) continue;

				//Create generalised label based on sort key
				remote_data_2[i].sync_label = remote_data_2[i][here.sort_key];

				//Add the right column
				data.site_2.push(remote_data_2[i]);

				//We know that all this data is now not present in either column,
				//so we can safely add a blank row to site_1
				o = {};
				o[here.sort_key]		= remote_data_2[i][here.sort_key];
				o[here.comparison_key]	= remote_data_2[i][here.comparison_key];
				o['blank']				=  true;
				data.site_1.push(o);
			}


			//Sort alphabetically
			data.site_1.sort($.proxy(here, "dataCompare"));
			data.site_2.sort($.proxy(here, "dataCompare"));



			def.resolve(data);
		});

		return def;
	};



	SyncManager.prototype.dataCompare = function(a,b) {
		if (a[this.sort_key] < b[this.sort_key])
			return -1;
		if (a[this.sort_key] > b[this.sort_key])
			return 1;
		return 0;
	};


	return SyncManager;
});