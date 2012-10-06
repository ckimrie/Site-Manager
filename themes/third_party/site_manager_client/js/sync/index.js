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
		this.gutter_node = $("#sm-gutter-body"),
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

			//Sync type change
			here.sync_type.change(function() {
				there.site_selection_changed();
			});
		});




		/**
		 * Site Selection or Sync type Change
		 *
		 * This site is the central dispatcher for creating a site
		 * to site comparison.  It takes each selected site and adds
		 * them to a newly create SyncManager instance.
		 *
		 * The SyncManager will initiate the data fetching required from
		 * each site and it will then return (via deferred) a nicely organised
		 * data structure that can be rendered easily.
		 *
		 * @author Christopher Imrie
		 *
		 * @return {null}
		 */
		this.site_selection_changed = function() {
			var here = this;

			if(this.site_1 && this.site_2) {
				this.sync_type.removeAttr("disabled");

				this.sync = new SyncManager();
				this.sync.add(this.site_1, this.site_1_node);
				this.sync.add(this.site_2, this.site_2_node);


				this.sync.compare(this.sync_type.val()).done(function(data) {
					here.renderComparison(data);
				});
			} else {
				this.sync_type.attr("disabled", "disabled");
				this.sync = null;
			}

		};



		/**
		 * Render Comparison Result
		 *
		 * Renders the results of a data comparison between two sites. The SyncManager
		 * that provides the data parameter creates a generic "sync_label" property on each
		 * item.  This allows this method to render any data comparison.
		 *
		 * @author Christopher Imrie
		 *
		 * @param  {[type]}    data  [description]
		 * @return {[type]}          [description]
		 */
		this.renderComparison = function(data) {
			var i, node,
				target_1 = this.site_1_node,
				target_2 = this.site_2_node,
				gutter  = this.gutter_node;


			target_1.empty();
			target_2.empty();
			gutter.empty();

			//Just in case somethings gone wrong with the comparison
			if(data.site_1.length !== data.site_2.length) {
				alert("Error encountered while trying to list data");
				return;
			}

			for (i = 0; i < data.site_1.length; i++) {
				//Site 1
				node = $(document.createElement("div"))
						.addClass(data.site_1[i].blank ? "sm-sync-block blank" : "sm-sync-block")
						.html(data.site_1[i].blank ? "&nbsp;" : data.site_1[i].sync_label);
				node.appendTo(target_1);

				//Site 2
				node = $(document.createElement("div"))
						.addClass(data.site_2[i].blank ? "sm-sync-block blank" : "sm-sync-block")
						.html(data.site_2[i].blank ? "&nbsp;" : data.site_2[i].sync_label);
				node.appendTo(target_2);

				//Gutter
				node = $(document.createElement("div"))
						.addClass("sm-sync-block");

				// Render sync buttons in gutter
				//
				// Figure out what direction is allowed
				if(!data.site_1[i].blank && data.site_2[i].blank) {
					//Site 1 --> Site 2
					node.append($(document.createElement("div")).addClass("sm-sync-btn btn-right"));
				}
				if(data.site_1[i].blank && !data.site_2[i].blank) {
					//Site 1 <-- Site 2
					node.append($(document.createElement("div")).addClass("sm-sync-btn btn-left"));
				}
				if(!data.site_1[i].blank && !data.site_2[i].blank) {
					//Site 1 <-> Site 2
					//node.append($(document.createElement("div")).addClass("sm-sync-btn btn-left"));
					//node.append($(document.createElement("div")).addClass("sm-sync-btn btn-right"));
				}
				node.appendTo(gutter);
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
