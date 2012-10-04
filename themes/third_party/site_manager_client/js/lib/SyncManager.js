define(['jquery'], function($) {

	function SyncManager() {
		this.sites = [];
	}



	/**
	 * Add site
	 * @param {object} site 
	 * @param {jquery} dom  
	 */
	SyncManager.prototype.add = function(site, dom) {
		this.sites.push({
			site : site,
			node : dom
		});
	};


	SyncManager.prototype.compare = function(critera) {
		if(critera === "channels") {
			this.compareChannels();
		}
		if(critera === "fieldgroups") {
			this.compareFieldgroups();
		}
		if(critera === "categories") {
			this.compareCategories();
		}
	};


	SyncManager.prototype.compareChannels = function() {
		var here = this;


		//When data from both has been fetched
		$.when(this.sites[0].site.channels(), this.sites[1].site.channels()).done(function(site_1_channels, site_2_channels) {
			var i, j, 
				channel_name_map = {},
				largest_channel_collection = 0, 
				channels = {};

			//Sort alphabetically
			site_1_channels.sort(this.channelCompare);
			site_2_channels.sort(this.channelCompare);


			//Go through site one and assign positions
			for(i = 0; i < site_1_channels; i++){
				if(channel_name_map[site_1_channels[i].channel_name]) {
					//WTF!!!
					/*
						We need: 

						blog	-	blog
						news	-	.
						about	-	about
						.		- 	site
						general	-	general


					 */

				}
				
			}

		});
	};


	
	SyncManager.prototype.channelCompare = function(a,b) {
		if (a.channel_title < b.channel_title)
			return -1;
		if (a.channel_title > b.channel_title)
			return 1;
		return 0;
	};


	return SyncManager;
});