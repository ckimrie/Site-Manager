define(["jquery", 'site_configs', "../lib/Site", "../lib/SyncManager"], function($, site_configs, Site, SyncManager) {

	/**
	 * Constructor
	 */
	function Sync() {

		var //Config


			//Runtime vars
			here = this,
			sites_loading = 0,
			sites_ready = new $.Deferred();


		//Storage
		this.wrapper = $("#syncWrapper");
		this.select1 = $("#sm-site1");
		this.select2 = $("#sm-site2");
		this.field_group_selector = $("#sm-fieldgroup");
		this.site_1_selected_group_id = "";
		this.site_2_selected_group_id = "";
		this.sync_type = $("#sm-sync_type");
		this.sync = null;
		this.site_1 = null;
		this.site_1_node = $("#sm-site1-body");
		this.site_2 = null;
		this.site_2_node = $("#sm-site2-body");
		this.gutter_node = $("#sm-gutter-body");
		this.all_sites = {};

		$("#sm-refresh").click(function(e) {
			e.preventDefault();
			here.site_selection_changed();
		});


		//Initialise each one and add to select menus
		$.each(site_configs, function(i, site_config) {

			var there = here,
				s = new Site(site_config),
				id = s.config.site_id;

			//Increase the counter so we can trigger when all sites have been pinged
			sites_loading++;

			//Set the parent div to a fake parent
			s.setParentDiv(document.createElement("div"));

			//Fetch license
			s.ping().fail(function() {
				there.site_loaded();
			}).done(function(data) {
				there.all_sites['s'+s.config.site_id] = s;
			}).then(function() {
				there.site_loaded();
			});

		});



		//When all sites loaded, add them to the DOM
		sites_ready.done(function() {
			var ordered = [],
				there = here;

			$.each(here.all_sites, function(i, s) {
				ordered.push({
					"id" :  s.config.site_id,
					"name" : s.config.site_name
				});
			});


			ordered.sort(function(a, b) {
				if(a.name > b.name) return +1;
				if(a.name < b.name) return -1;
				return 0;
			});


			//Add to DOM
			for(var i = 0; i < ordered.length; i++) {
				there.select1.append($(document.createElement("option")).text(ordered[i].name).val('s' + ordered[i].id));
				there.select2.append($(document.createElement("option")).text(ordered[i].name).val('s' + ordered[i].id));
			}

			//Display the UI
			here.wrapper.removeClass("initialLoad");


			// Site Selection Change
			here.select1.change(function() {
				if($(this).val() !== "") {
					there.site_1 = there.all_sites[$(this).val()];
				} else {
					there.site_1 = null;
				}

				there.sync_type.trigger("change");
			});

			// Site Selection Change
			here.select2.change(function() {
				if($(this).val() !== "") {
					there.site_2 = there.all_sites[$(this).val()];
				} else {
					there.site_2 = null;
				}

				there.sync_type.trigger("change");
			});

			//Sync type change
			here.sync_type.change(function() {
				var overthere = there,
					d = new $.Deferred();


				//If user has selected "Fields"to sync, we need to figure out what field groups are in common
				if($(this).val() === "fields") {

					there.field_group_selector.show().parent().addClass("double");

					//Fetch the fieldgroups from each site and populate the select list with groups present in BOTH sites.
					$.when(overthere.site_1.fieldgroups(), overthere.site_2.fieldgroups()).done(function(site_1_fieldgroups, site_2_fieldgroups) {

						var success = overthere.populateFieldGroupList(site_1_fieldgroups, site_2_fieldgroups);

						if (success) {
							d.resolve();
						} else {
							d.reject();
						}
					});

				} else {
					there.field_group_selector.hide().parent().removeClass("double");
					d.resolve();
				}

				//When fieldgroups have been set
				d.done(function() {
					var option = overthere.field_group_selector.find("option:selected");
					overthere.site_1_selected_group_id = option.data("site_1_group_id");
					overthere.site_2_selected_group_id = option.data("site_2_group_id");

					overthere.site_selection_changed();
				});

				//Field group match fail
				d.fail(function() {
					overthere.field_group_selector.hide();
					overthere.site_1_node.empty();
					overthere.site_2_node.empty();
					overthere.gutter_node.empty();
					overthere.site_1_selected_group_id = "";
					overthere.site_2_selected_group_id = "";
				});
			});

			//Field group selection change.  The group_id for each site is embedded in the
			//option as a data attribute
			here.field_group_selector.change(function() {
				var option = $(this).find("option:selected");

				there.site_1_selected_group_id = option.data("site_1_group_id");
				there.site_2_selected_group_id = option.data("site_2_group_id");

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

				//Loading indicator
				this.wrapper.addClass("loading");

				//Clearout the current displayed items

				this.sync = new SyncManager();
				this.sync.add(this.site_1, this.site_1_node);
				this.sync.add(this.site_2, this.site_2_node);

				if(this.sync_type.val() == "fields") {
					this.sync.site_1_selected_group_id = this.site_1_selected_group_id;
					this.sync.site_2_selected_group_id = this.site_2_selected_group_id;
				}

				this.sync.compare(this.sync_type.val()).done(function(data) {
					here.renderComparison(data);
					here.wrapper.removeClass("loading");
				});




			} else {
				this.field_group_selector.hide();
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
		 * @param  {object}    data
		 * @return {null}
		 */
		this.renderComparison = function(data) {
			var i,a, node, meta,
				d = new $.Deferred(),
				here = this,
				target_1 = this.site_1_node,
				target_2 = this.site_2_node,
				gutter  = this.gutter_node;

			this.site_1_node.empty();
			this.site_2_node.empty();
			this.gutter_node.empty();

			this.wrapper.removeClass("no-results");

			//Just in case somethings gone wrong with the comparison
			if(data.site_1.length !== data.site_2.length) {
				alert("Error encountered while trying to list data");
				return;
			}

			for (i = 0; i < data.site_1.length; i++) {
				//Site 1
				node = $(document.createElement("div"))
						.addClass(data.site_1[i].blank ? "sm-sync-block blank" : "sm-sync-block");

				//Meta data
				meta = data.site_1[i].meta_label ? "<span class='meta'>" + data.site_1[i].meta_label + "</span>" : "";
				node.html(data.site_1[i].blank ? "&nbsp;" : data.site_1[i].sync_label + meta);
				node.appendTo(target_1);


				//Site 2
				node = $(document.createElement("div"))
						.addClass(data.site_2[i].blank ? "sm-sync-block blank" : "sm-sync-block");

				//Meta data
				meta = data.site_2[i].meta_label ? "<span class='meta'>" + data.site_2[i].meta_label + "</span>" : "";
				node.html(data.site_2[i].blank ? "&nbsp;" : data.site_2[i].sync_label + meta);

				node.appendTo(target_2);


				//Gutter
				node = $(document.createElement("div"))
						.addClass("sm-sync-block");


				// Render sync buttons in gutter
				// Figure out what direction is allowed
				if(!data.site_1[i].blank && data.site_2[i].blank) {

					//Site 1 --> Site 2
					a = $(document.createElement("div")).addClass("sm-sync-btn btn-right");
					here.bindSyncButton(a, "right", i);
					node.append(a);
				}
				if(data.site_1[i].blank && !data.site_2[i].blank) {

					//Site 1 <-- Site 2
					a = $(document.createElement("div")).addClass("sm-sync-btn btn-left");
					here.bindSyncButton(a, "left", i);
					node.append(a);
				}
				if(!data.site_1[i].blank && !data.site_2[i].blank) {

					//Site 1 <-> Site 2  (nothing for now...)
					a = $(document.createElement("div")).addClass("sm-sync-btn btn-left");
					here.bindSyncButton(a, "left", i);
					//node.append(a);
					a = $(document.createElement("div")).addClass("sm-sync-btn btn-right");
					here.bindSyncButton(a, "right", i);
					//node.append(a);
				}
				node.appendTo(gutter);
			}

			//No Results?
			if(data.site_1.length == 0 && data.site_1.length == 0) {
				this.wrapper.addClass("no-results");
			}


		};


		/**
		 * Binds SyncManager action to a sync button
		 *
		 * @author Christopher Imrie
		 *
		 * @param  {object}    node			Button node
		 * @param  {string}    direction	Sync transfer direction (left/right)
		 * @param  {integer}   key			Data row key
		 * @return {null}
		 */
		this.bindSyncButton = function(node, direction, key) {
			var here = this;

			node.click(function(e) {
				var there = here,
					k = key,
					dir = direction,
					def;

				e.preventDefault();

				//Kick off the transfer
				def = there.sync.transfer(direction, key);

				//Start the animation
				here.animateSync(direction, key).done(function() {

					//Bind the sync complete after the animation has completed
					def.done($.proxy(there, "syncComplete"));
					def.fail($.proxy(there, "syncError"));

				});


			});
		};





		/**
		 * Animate Sync Action
		 *
		 * @author Christopher Imrie
		 *
		 * @param  {string}    direction	Visual left/right sync direction
		 * @param  {integer}    key			Row key for item being synced
		 * @return {object}					jQuery deferred
		 */
		this.animateSync = function(direction, key) {
			var total_block_height = 31,
				left_start = "-138%",
				target = $("#sm-site2-body"),
				left_finish = "0%",
				node,
				def = new $.Deferred();

			//Remove all current sync items in case
			$(".sm-sync-block").removeClass("current-sync");


			if(direction == "left") {
				left_start = "138%";
				left_finish = "0%";
				target = $("#sm-site1-body");

				node = $("#sm-site2-body").find(".sm-sync-block").eq(key).clone();
			} else {
				node = $("#sm-site1-body").find(".sm-sync-block").eq(key).clone();
			}

			//So we can track and undo this if needed
			node.addClass("current-sync");

			node.css({
				"position" : "absolute",
				"top" : total_block_height * key,
				"left" : left_start
			}).appendTo(target);
			node.animate({
				left : left_finish
			}, 400, function() {
				def.resolve();
			});

			return def;
		};



		this.undoSyncAnimation = function() {
			var def = new $.Deferred(),
				node = $(".sm-sync-block").filter(".current-sync");

			node.addClass("error");
			$(node).fadeOut(1000, function(){
				node.remove();
			});
		};



		/**
		 * Populate Field Group Sub Selection Drop Down
		 *
		 * @author Christopher Imrie
		 *
		 * @param  {array}    a     Site 1 field groups
		 * @param  {array}    b     Site 2 field groups
		 * @return {boolean}        Success / failure
		 */
		this.populateFieldGroupList = function(a, b) {
			var i, j, option, valid =[];

			this.field_group_selector.empty();


			//Cycle through both site field group lists and figure which they have in common
			for(i = 0; i < a.length; i++) {
				for(j = 0; j < b.length; j++) {
					if (a[i].group_name === b[j].group_name) {
						valid.push({
							group_name : a[i].group_name,
							site_1_group_id : a[i].group_id,
							site_2_group_id : b[j].group_id
						});
					}
				}
			}

			//No fieldgroups in common?
			if(valid.length === 0) {
				this.unableToSyncWarning("There are no field groups that match on either site.  Both sites must have at least one field group in common in order to sync fields");
				return false;
			}

			//Loop through and create the valid group options
			for(i = 0; i < valid.length; i++) {
				option = $(document.createElement("option"))
							.text(valid[i].group_name)
							.data('site_1_group_id', valid[i].site_1_group_id)
							.data('site_2_group_id', valid[i].site_2_group_id);
				option.appendTo(this.field_group_selector);
			}

			//Setup initial selected values
			this.site_1_selected_group_id = valid[0].site_1_group_id;
			this.site_2_selected_group_id = valid[0].site_2_group_id;

			return true;
		};



		/**
		 * Display message when we sync items
		 *
		 * @author Christopher Imrie
		 *
		 * @param  {string}    msg
		 * @return {null}
		 */
		this.unableToSyncWarning = function(msg) {
			$.ee_notice(msg, {open: true, type:"error"});
		};



		/**
		 * Sync Complete
		 *
		 * Fired after animation and remote confirmation of sync action.
		 * At the moment it simply rebuilds the interface using fresh data
		 *
		 * @author Christopher Imrie
		 *
		 * @return {null}
		 */
		this.syncComplete = function() {
			this.site_selection_changed();
		};


		this.syncError = function(data) {
			var msg = "Unknown error encountered. Unable to sync.";
			if(data.error) {
				msg = data.error;
			}

			this.unableToSyncWarning(msg);

			this.undoSyncAnimation();
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
