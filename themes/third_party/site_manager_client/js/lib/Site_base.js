
define(['jquery'],function($) {

	var loadingCount = 0;


	function Site_base() {}


	Site_base.error = false;


	/**
	 * Ping the site to establish
	 * @return {[type]} [description]
	 */
	Site_base.ping = function() {
		var here = this,
			def = new $.Deferred(),
			ping = this.get("ping");

		ping.done(function(data) {
			var there = here;

			//SUCCESS
			here.updateUIStatus(data);

			def.resolve(data);
		});

		ping.fail(function(status, jqXHR) {
			here.updateUIStatus(false);
			def.reject("Unable to communicate with site. Status: " + status);
		});

		return def;
	};


	Site_base.updateUIStatus = function (success) {
		var here = this;

		if(success){
			this.parentDiv.find(".status").removeClass("active").filter(".live").addClass("active");
			setTimeout(function() {
				here.parentDiv.find(".status").parent().addClass("loaded");
			}, 2000);
		}else {
			this.parentDiv.find(".status").removeClass("active").filter(".offline").addClass("active").parent().addClass("loaded");
		}
	};


	Site_base.get = function(method) {
		var here = this,
			def = new $.Deferred();

		this._startedLoading();

		$.ajax({
			url: this.url(method),
			data: {},
			success: function(data) {
				def.resolve(data);

				here._finishedLoading();
			},
			error: function(jqXHR, textStatus) {
				def.reject(textStatus, jqXHR);

				here.error = true;
				here._finishedLoading();
			},
			dataType: "json"
		});

		return def;
	};


	Site_base.post = function(method, data) {
		var here = this,
			def = new $.Deferred();

		this._startedLoading();

		$.ajax({
			url: this.url(method),
			type: "POST",
			data: data,
			success: function(data) {
				def.resolve(data);

				here._finishedLoading();
			},
			error: function(jqXHR, textStatus) {
				console.log(arguments);
				def.reject(textStatus, jqXHR);

				here.error = true;
				here._finishedLoading();
			},
			dataType: "json"
		});

		return def;
	};


	Site_base.url = function(a) {

		return this.config.api_url + "&method="+a;
	};




	Site_base._startedLoading = function() {
		loadingCount++;

		if(loadingCount > 0) {
			//Add a loading class to the body element
			$("body").addClass("loading");
		}
	};

	Site_base._finishedLoading = function() {
		loadingCount--;

		if(loadingCount <= 0) {
			loadingCount = 0;
			//Add a loading class to the body element
			$("body")
				.removeClass("loading")
				.addClass("first-load")
				.addClass("loading-finished");

			if(this.error) {
				$("body").addClass("communication-error");

				$(".dynamicDataWrapper").empty().append($("<p/>").text("Unable to communicate with site").addClass("communication-error-notice"));
			}
		}
	};

	return Site_base;
});