define(["jquery", 'site_configs', "../lib/Site", "../lib/SyncManager"], function($, site_configs, Site, SyncManager) {

	function Sync() {

		var //Config
			

			//Runtime vars
			here = this,
			sites_loading = 0,
			sites_ready = new $.Deferred();
		

		//Storage
		this.select1 = $("#sm-site1"),
		this.select2 = $("#sm-site2"),
		this.sync_type = $("#sm-sync_type"),
		this.sync = null;
		this.site_1 = null;
		this.site_1_node = $("#sm-site1-body"),
		this.site_2 = null;
		this.site_2_node = $("#sm-site2-body"),
		this.all_sites = {};


		//Initialise eachone and add to select menus
		$.each(site_configs, function(i, site_config) {
			
			var there = here,
				s = new Site(site_config),
				id = s.config.site_id;

			//Increase the counter so we can trigger when all sites have been pinged
			sites_loading++;

			//Set the parent div to a fake parent
			s.setParentDiv(document.createElement("div"));
			
			//Fetch license
			s.ping().done(function(data) {
				there.all_sites['s'+s.config.site_id] = s;
			}).then(function() {
				there.site_loaded();
			});

		});
		


		//When all sites loaded, add them to the DOM
		sites_ready.done(function() {
			var there = here;

			//Add to DOM
			$.each(here.all_sites, function(i, s) {
				there.select1.append($(document.createElement("option")).text(s.config.site_name).val('s' + s.config.site_id));
				there.select2.append($(document.createElement("option")).text(s.config.site_name).val('s' + s.config.site_id));
			});

			// Site Selection Change
			here.select1.change(function() {
				if($(this).val() !== "") {
					there.site_1 = there.all_sites[$(this).val()];
				} else {
					there.site_1 = null;
				}

				there.site_selection_changed();
			});

			// Site Selection Change
			here.select2.change(function() {
				if($(this).val() !== "") {
					there.site_2 = there.all_sites[$(this).val()];
				} else {
					there.site_2 = null;
				}

				there.site_selection_changed();
			});
		});





		this.site_selection_changed = function() {

			if(this.site_1 && this.site_2) {
				this.sync_type.removeAttr("disabled");
				
				//Just in case
				this.site_1_node.empty();
				this.site_2_node.empty();

				this.sync = new SyncManager();
				this.sync.add(this.site_1, this.site_1_node);
				this.sync.add(this.site_2, this.site_2_node);


				this.sync.compare(this.sync_type.val());
			} else {
				this.sync_type.attr("disabled", "disabled");
				this.sync = null;
			}

		};


		/**
		 * Utility method called after each site ping attempt
		 * It resolves a deferred after all sites have been attempted
		 * @return {null} 
		 */
		this.site_loaded = function() {
			sites_loading--;

			if(sites_loading <= 0){
				sites_loading = 0;
				sites_ready.resolve();
			}
		};
	}

	return new Sync();
});
