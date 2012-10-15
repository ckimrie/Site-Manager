define(["jquery", 'site_config', "../lib/Site"], function($, site_config, Site) {

	var site = new Site(site_config),
		table = $("#configTable").hide(),
		target = $("#target");

	//Ping Site
	site.ping().done(function(data) {
		$(".app_version").text(data.app_version);
	});

	//Site configuration
	site.configuration().done(function(data) {
		var urlTable = table.clone(),
			urlBody = urlTable.find("tbody"),
			pathTable = table.clone(),
			pathBody = pathTable.find("tbody"),
			otherTable = table.clone(),
			otherBody = otherTable.find("tbody");

		urlTable.find("th").eq(0).text("URL Settings");
		pathTable.find("th").eq(0).text("Path Settings");
		otherTable.find("th").eq(0).text("All Other Settings");


		data = sortConfig(data);

		$.each(data.urls, function(key, value){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(key),
				td2 = $(document.createElement("td")).text(value);

			tr.append(td1);
			tr.append(td2);
			tr.appendTo(urlBody);
		});

		$.each(data.paths, function(key, value){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(key),
				td2 = $(document.createElement("td")).text(value);

			tr.append(td1);
			tr.append(td2);
			tr.appendTo(pathBody);
		});

		$.each(data.other, function(key, value){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(key),
				td2 = $(document.createElement("td")).text(value);

			tr.append(td1);
			tr.append(td2);
			tr.appendTo(otherBody);
		});

		urlTable.appendTo(target).show();
		pathTable.appendTo(target).show();
		otherTable.appendTo(target).show();

	});


	function sortConfig(obj) {
		var data = {
			urls : {},
			paths : {},
			other : {}
		};

		$.each(obj, function (key, value) {
			if((String(key).indexOf("url") >= 0 && key !== "url_suffix" && key !== "strict_urls") || key === "emoticon_path"){
				data.urls[key] = value;
				return;
			}
			if(String(key).indexOf("path") >= 0){
				data.paths[key] = value;
				return;
			}


			data.other[key] = value;
		});

		return data;
	}
});


