define(["jquery", 'site_config', "../lib/Site"], function($, site_config, Site) {

	var site = new Site(site_config);


	//Ping Site
	site.ping().done(function(data) {
		$(".app_version").text(data.app_version);
	});


	site.addons().done(function(data) {
		var table,
			$native = $(".native"),
			$third_party = $(".third_party");

		/**
		 * Native
		 */

		//Modules
		table = $native.find(".modulesTable tbody");
		$.each(data.native.modules, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.native.modules.length === 0) {
			emptyTable(table);
		}

		//Fieldtypes
		table = $native.find(".fieldtypesTable tbody");
		$.each(data.native.fieldtypes, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.native.fieldtypes.length === 0) {
			emptyTable(table);
		}



		//Extensions
		table = $native.find(".extensionsTable tbody");
		$.each(data.native.extensions, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.native.extensions.length === 0) {
			emptyTable(table);
		}


		//Plugins
		table = $native.find(".pluginsTable tbody");
		$.each(data.native.plugins, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.native.plugins.length === 0) {
			emptyTable(table);
		}


		//Accessories
		table = $native.find(".accessoriesTable tbody");
		$.each(data.native.accessories, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.native.accessories.length === 0) {
			emptyTable(table);
		}


		/**
		 * Third Party
		 */

		//Modules
		table = $third_party.find(".modulesTable tbody");
		$.each(data.third_party.modules, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.third_party.modules.length === 0) {
			emptyTable(table);
		}

		//Fieldtypes
		table = $third_party.find(".fieldtypesTable tbody");
		$.each(data.third_party.fieldtypes, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.third_party.fieldtypes.length === 0) {
			emptyTable(table);
		}



		//Extensions
		table = $third_party.find(".extensionsTable tbody");
		$.each(data.third_party.extensions, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.third_party.extensions.length === 0) {
			emptyTable(table);
		}


		//Plugins
		table = $third_party.find(".pluginsTable tbody");
		$.each(data.third_party.plugins, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.third_party.plugins.length === 0) {
			emptyTable(table);
		}


		//Accessories
		table = $third_party.find(".accessoriesTable tbody");
		$.each(data.third_party.accessories, function(key, obj){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(obj.label),
				td2 = $(document.createElement("td")).text(obj.version),
				td3 = $(document.createElement("td")).html(obj.installed ? "<span class='label label-success'>Yes</span>" : "<span class='label label-negative'>No</span>");

			tr.append(td1);
			tr.append(td2);
			tr.append(td3);
			tr.appendTo(table);
		});
		if (data.third_party.accessories.length === 0) {
			emptyTable(table);
		}



		//Bind Click event Handlers
		$("dt").click(function() {

			$(this).toggleClass("open").next().toggle();
		});
	});



	function emptyTable (table) {
		table.append($("<tr><td colspan='3'><em>None</em></td></tr>"));
	}
});


