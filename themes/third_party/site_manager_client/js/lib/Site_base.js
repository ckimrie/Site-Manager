
define(['jquery'],function($) {

	function Site_base() {}


	/**
	 * Ping the site to establish
	 * @return {[type]} [description]
	 */
	Site_base.ping = function() {
		var def = new $.Deferred(),
			ping = this.get("ping");

		ping.done(function(data) {
			//SUCCESS
			$(".status").removeClass("active").filter(".live").addClass("active");
			setTimeout(function() {
				$(".status").parent().addClass("loaded");
			}, 2000);

			$(".app_version").text(data[0].app_version);

			def.resolve(data);
		});

		ping.fail(function(status, jqXHR) {
			$(".status").removeClass("active").filter(".offline").addClass("active").parent().addClass("loaded");
			def.reject("Unable to communicate with site. Status: " + status);
		});

		return def;
	};


	Site_base.get = function(method) {
		var def = new $.Deferred();

		$.ajax({
			url: this.url(method),
			data: {},
			success: function(data) {
				def.resolve(data);
			},
			error: function(jqXHR, textStatus) {
				def.reject(textStatus, jqXHR);
			},
			dataType: "json"
		});

		return def;
	};


	Site_base.url = function(a) {
		
		return this.config.api_url += "&method="+a;
	};

	return Site_base;
});