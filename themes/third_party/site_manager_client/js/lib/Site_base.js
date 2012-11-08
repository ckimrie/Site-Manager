
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
			d1 = new $.Deferred(),
			ping = this.get("ping");

		ping.then(function(data) {
			var there = here;

			//SUCCESS
			if (data.success) {
				here.updateUIStatus(data);
				d1.resolve(data);
			} else {
				here.updateUIStatus(false);
				d1.reject(data.error);
			}

		});

		ping.fail(function(status, jqXHR) {
			here.updateUIStatus(false);
			d1.reject("Unable to communicate with site. Status: " + status);
		});

		return d1;
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


	/**
	 * Send data to be decrypted by the server
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {string}    data  Encrypted data
	 * @return {object}          jQuery Deferred
	 */
	Site_base.decrypt = function(data) {

		//Fetch encrypted data
		var d1 = $.ajax({
			url: this.decryptionServiceUrl,
			type: "POST",
			data: {
				XID : this.XID,
				data : data
			},
			dataType: "json"
		});

		return d1;
	};


	/**
	 * Send data to be encrypted by the server
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {object}    data  Data to be encrypted
	 * @return {object}          jQuery Deferred
	 */
	Site_base.encrypt = function(data) {

		data.XID = this.XID;

		//Fetch encrypted data
		var d1 = $.ajax({
			url: this.encryptionServiceUrl,
			type: "POST",
			data: data,
			dataType: "text"
		});

		return d1;
	};



	/**
	 * Get Data from remote site
	 *
	 * @author Christopher Imrie
	 *
	 * @param  {string}    method
	 * @param  {object}    params
	 * @return {object}           jQuery Deferred
	 */
	Site_base.get = function(method, params) {
		var here = this,
			d1  = new $.Deferred(),
			d2 = new $.Deferred();

		this._startedLoading();

		//Fetch encrypted data
		d1 = $.ajax({
			url: this.url(method, params),
			data: {},
			dataType: "text"
		});


		//Successfuly recieved encrypted data.  Send it to the decryption service
		d1.done(function(data) {
			var there = here,
				d2_proxy = d2,
				d3 = here.decrypt(data);

			//Successfully decrypted
			d3.done(function(data) {
				d2_proxy.resolve(data);

				there._finishedLoading();
			});

			//Error decrypting
			d3.fail(function(jqXHR, textStatus) {
				d2_proxy.reject(textStatus, jqXHR);

				there.error = true;
				there._finishedLoading();
			});

		});

		//Failed to retrieve data
		d1.fail(function(jqXHR, textStatus) {
			d2.reject(textStatus, jqXHR);

			here.error = true;
			here._finishedLoading();
		});

		return d2;
	};


	Site_base.post = function(method, data, params) {
		var here = this,
			d1 = $.Deferred(),
			d2 = this.encrypt(data);

		this._startedLoading();

		//When data has been encrypted by the server...
		d2.done(function(data) {
			var d3,
				there = here,
				d1_proxy = d1;

			//...Send this to the server, which will respond with ...
			d3 = $.ajax({
				url: here.url(method, params),
				type: "POST",
				data: {payload: data},
				dataType: "text"
			});

			//.. encrypted data, so send this to be decrypted...
			d3.done(function(data) {
				var over_there = there,
					d1_proxy_2 = d1_proxy,
					d3 = there.decrypt(data);

				//Which returns our final JSON object !
				d3.done(function(data) {
					if(data.success) {
						d1_proxy_2.resolve(data);
					} else {
						d1_proxy_2.reject(data);
					}
					over_there._finishedLoading();
				});


				//Decryption service choked...
				d3.fail(function(jqXHR, textStatus) {
					d1_proxy_2.reject(textStatus, jqXHR);

					over_there.errorMessage("Error encountered while trying to decrypt data: " + textStatus);
					over_there.error = true;
					over_there._finishedLoading();
				});
			});

			//Remote server choked
			d3.fail(function(jqXHR, textStatus) {
				d1_proxy.reject(textStatus, jqXHR);

				there.errorMessage("The remote site returned an error as a response: " + textStatus);

				there.error = true;
				there._finishedLoading();
			});
		});


		//Encryption server choked
		d2.fail(function(jqXHR, textStatus) {
			d1.reject(textStatus, jqXHR);

			here.errorMessage("Error encountered while trying to encrypt data: " + textStatus);

			here.error = true;
			here._finishedLoading();
		});




		return d1;
	};


	Site_base.errorMessage = function(msg) {
		$.ee_notice(msg, {type:"error"});
	};


	Site_base.url = function(a, params) {
		var query = "";

		if(params) {
			$.each(params, function(key, value) {
				query += "&" + key + "=" + value;
			});
		}
		return this.config.api_url + "&k=" + this.config.public_key + "&method=" + a + query;
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