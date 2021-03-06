/**
 * Initialize a new video instance
 * Important: If an init function is specified, it must handle it's own tracking
 */

define('wikia.videoBootstrap', ['wikia.loader', 'wikia.nirvana'], function videoBootstrap(loader, nirvana) {
	var trackingTimeout = 0;

	// vb stands for video bootstrap
	function vb (element, json, clickSource) {
		var self = this,
			init = json.init,
			html = json.html,
			scripts = json.scripts,
			jsParams = json.jsParams;

		this.element = element;
		this.clickSource = clickSource;
		this.title = json.title;
		this.provider = json.provider;

		// Insert html if it hasn't been inserted already
		function instertHtml() {
			if(html && !json.htmlPreloaded) {
				element.innerHTML = html;
			}
		}

		// Load any scripts needed for the video player
		if(scripts) {
			var i,
				args = [];

			for(i=0; i<scripts.length; i++){
				args.push({
					type: loader.JS,
					resources: scripts[i]
				});
			}

			loader
			.apply(loader, args)
			.done(function() {
				// wait till all assets are loaded before overriding any loading images
				instertHtml();
				// execute the init function
				if(init) {
					require([init], function(init) {
						init(jsParams, self);
					});
				}
			});
		} else {
			instertHtml();
		}

		// If there's no init function, just send one tracking call so it counts as a view
		if(!init) {
			self.timeoutTrack();
		}
	}

	vb.prototype = {
		/**
		 * This is a full reload of the video player. Use this when you
		 * need to reset the player with altered settings (such as autoplay).
		 * Note: Reloading videos without JS api's can result in extra views
		 * tracked. Not sure it's worth fixing at this time b/c it's edge-casey.
		 */
		reload: function(title, width, autoplay, clickSource) {
			var element = this.element;
			nirvana.getJson(
				'VideoHandler',
				'getEmbedCode',
				{
					fileTitle: title,
					width: width,
					autoplay: autoplay ? 1 : 0 // backend needs an integer
				}
			).done(function(data) {
				new vb(element, data.embedCode, clickSource);
			});
		},
		track: function(action) {
			Wikia.Tracker.track({
				action: action,
				category: 'video-player-stats',
				label: this.provider,
				trackingMethod: 'internal',
				value: 0
			}, {
				title: this.title,
				clickSource: this.clickSource
			});
		},
		/**
		 * Use this when the video provider doesn't offer a player api for tracking
		 */
		timeoutTrack: function() {
			var self = this;
			clearTimeout(trackingTimeout);
			trackingTimeout = setTimeout(function() {
				self.track('content-begin');
			}, 3000);
		},
		/**
		 * Some video providers require unique DOM id's in order to initialize
		 * videos. Timestamping DOM id's makes it so you can create more than
		 * one instance of the same video on a page.
		 */
		timeStampId: function(id) {
			var time = new Date().getTime(),
				container = document.getElementById(id),
				newId = id + "-" + time;

			container.id = newId;

			return newId;
		}
	}

	return vb;
});