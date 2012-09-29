define(["jquery", 'site_config', "../lib/Site"], function($, site_config, Site) {

	var site = new Site(site_config);

	//Ping Site
	site.ping();

	//Site configuration
	site.configuration().done(function(data) {
		var table = $("#configTable tbody");

		$.each(data, function(key, value){
			var tr = $(document.createElement("tr")),
				td1 = $(document.createElement("td")).text(key),
				td2 = $(document.createElement("td")).text(value);

			tr.append(td1);
			tr.append(td2);
			tr.appendTo(table);
		});
		
	});
});


