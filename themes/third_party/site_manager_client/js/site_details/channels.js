define(["jquery", 'site_config', "../lib/Site"], function($, site_config, Site) {

	var site = new Site(site_config);

	//Ping Site
	site.ping().done(function(data) {
		$(".app_version").text(data.app_version);
	});

	//Site configuration
	site.channels().done(function(data) {

		var $channelTemplate = $(".channelTemplate").children(),
			$target = $("#channelTarget");

		//Cycle through channels
		$.each(data, function(i, channel) {

			var $template = $channelTemplate.clone().removeClass("channelTemplate"),
				$templateFieldTableBody = $template.find(".fieldTable tbody");

			$template.detach();

			//Cycle through all channel properties and update the template (html classes = object keys)
			$.each(channel, function(key, value) {

				//Skip the fields & channel URL
				if(key == "fields" || key == "channel_url" ) return;

				$template.find("."+key).text(value);
			});

			//Channel Title
			$template.eq(0).text(channel.channel_title);

			//Channel URL link
			//$template.find(".channel_title").attr("href", channel.channel_url);

			//Fields table
			$.each(channel.fields, function(i, field){
				var tr = $(document.createElement("tr")),
					td1 = $(document.createElement("td")).text(field.field_label),
					td2 = $(document.createElement("td")).text(field.field_name),
					td3 = $(document.createElement("td")).text(field.field_type);

				tr.append(td1);
				tr.append(td2);
				tr.append(td3);
				tr.appendTo($templateFieldTableBody);
			});


			//No Fields?
			if (channel.fields.length === 0) {
				var tr = $(document.createElement("tr")),
					td1 = $(document.createElement("td"))
							.html("<em>No Fields are associated with this Channel</em>")
							.attr("colspan", "3");
				tr.append(td1);
				tr.appendTo($templateFieldTableBody);
			}
			$template.appendTo($target);
		});


		//Bind Click event Handlers
		$("dt").click(function() {
			$(this).toggleClass("open").next().toggle();
		});
	});
});


