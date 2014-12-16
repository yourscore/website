/**
 * Capture Slideshow - A jQuery plugin for fullscreen slideshow functionality in the Capture WordPress theme.
 *
 * License: GPL2
 * Copyright Scott Sousa (Slocum Studio), http://slocumthemes.com/
 */

( function( $ ) {
	'use strict';

	// Global vars
	var $window = $( window ), timers = [], capture_options = { current: false, previous: false }, Image, Images, ImageCollection, CaptureSlideshowRouter, CaptureSlideshowRoutes, ImageView, CaptureImageView, pushState, isiPhone;

	$( function( ) {
		// Make sure Capture Slideshow element exists
		if ( $( '#capture-slideshow-template' ).length ) {
			// Define an Image Model
			Image = Backbone.Model.extend( {
				defaults: {
					// Image properties
					img: {
						src: false,
						width: false,
						height: false
					},
					// Post Properties
					post: {
						id: false,
						title: false,
						date: false,
						permalink: false
					}
				}
			} );


			// Define an Images Collection
			Images = Backbone.Collection.extend( {
				model: Image,
				url: capture.ajaxurl,
				initialize: function ( options ) {
					this.options = options || {}; // Store options

					_.bindAll( this, 'getModel', 'setModel', 'nextModel', 'prevModel' ); // Bind "this" to all functions
				},
				comparator: function( model ) {
					return model.get( 'id' );
				},
				getModel: function() {
					return this.currentModel;
				},
				setModel: function( model ) {
					if ( this.currentModel ) {
						this.currentModel.unset( 'current_image' ); // Remove current image flag
					}
					this.currentModel = model; // Set current model
					this.currentModel.set( { current_image: true } ); // Set current image flag

					// Trigger "change"
					this.trigger( 'change', model );
				},
				nextModel: function() {
					this.setModel( this.at( this.indexOf( this.getModel() ) + 1 ) );

					return this;
				},
				prevModel: function() {
					this.setModel( this.at( this.indexOf( this.getModel() ) - 1 ) );

					return this;
				}
			} );

			// Instantiate the new collection
			ImageCollection = new Images();


			// Define an ImageView View
			// TODO: Use the model property more effectively
			ImageView = Backbone.View.extend( {
				el: '#capture-slideshow .capture-slideshow',
				capture_slideshow_elements: {},
				model: new Image(), // Default model
				template: _.template( $( '#capture-slideshow-template' ).html() ),
				initialize: function( options ) {
					this.options = options || {}; // Store options

					_.bindAll( this, 'setModel', 'initElements', 'render', 'getImages' ); // Bind "this" to all functions

					// Re-render this view when the model changes
					this.listenTo( this.collection, 'change', this.setModel );
					this.listenTo( this.model, 'change', this.render );

					// Initialize Capture Slideshow Elements
					this.initElements();
				},
				setModel: function( model ) {
					this.model = model;

					return this;
				},
				initElements: function() {
					this.capture_slideshow_elements.$parent = this.$el.parent();
					this.capture_slideshow_elements.$img = $( '.capture-slideshow-photo img', this.$el );
					this.capture_slideshow_elements.$img_parent = this.capture_slideshow_elements.$img.parent();
					this.capture_slideshow_elements.$close = $( '.capture-slideshow-close', this.capture_slideshow_elements.$parent );
					this.capture_slideshow_elements.$prev = $( '.capture-slideshow-prev', this.capture_slideshow_elements.$parent );
					this.capture_slideshow_elements.$next = $( '.capture-slideshow-next', this.capture_slideshow_elements.$parent );
					this.capture_slideshow_elements.$ui_elements = $( '.capture-slideshow-ui', this.capture_slideshow_elements.$parent );
					this.capture_slideshow_elements.$loader = $( '.loader-container', this.capture_slideshow_elements.$parent );

					return this;
				},
				render: function() {
					// If we have images
					if ( this.collection.length ) {
						var _this = this; // Cache "this"

						// Render template with model data
						this.$el.html( this.template( this.model.attributes ) );

						// Cache various Capture Slideshow elements
						_this = this.initElements();

						this.capture_slideshow_elements.$loader.removeClass( 'hidden hide' ); // Show the loader

						// Once all images are done loading
						this.$el.imagesLoaded().done( function() {
							_this.capture_slideshow_elements.$parent.addClass( 'loaded' ); // Slideshow container
							_this.capture_slideshow_elements.$ui_elements.removeClass( 'hidden hide initial-hide' );
							_this.capture_slideshow_elements.$loader.addClass( 'hidden hide' );

							// Previous & Next Buttons
							if ( _this.collection.getModel().attributes.hasOwnProperty( 'no_more_images' ) ) {
								if ( _this.collection.indexOf( _this.collection.getModel() ) === 0 ) {
									_this.capture_slideshow_elements.$prev.addClass( 'disabled' );
								}
								if ( _this.collection.indexOf( _this.collection.getModel() ) === ( _this.collection.length - 1 ) ) {
									_this.capture_slideshow_elements.$next.addClass( 'disabled' );
								}
							}

							// Fade in Image
							_this.capture_slideshow_elements.$img.fadeIn( 400, function() {
								$( this ).addClass( 'loaded' );
							} );

							// If only lightbox, we need to allow for fullscreen request
							if ( _this.options.hasOwnProperty( 'lightbox' ) && _this.options.lightbox ) {
								_this.capture_slideshow_elements.$img_parent.addClass( 'lightbox' );
								_this.capture_slideshow_elements.$img.on( 'click.capture-slideshow', function( e ) {
									e.preventDefault();

									openCaptureSlideshow();
								} );
							}

							// On swipe events - Hammer
							// TODO: Add to event hashing if possible
							if ( capture.is_mobile ) {
								if( _this.capture_slideshow_elements.$img.data( 'capture-slideshow' ) !== 'active' ) {
									// Left (Next)
									Hammer( _this.capture_slideshow_elements.$img[0] ).on( 'dragleft swipeleft', function( e ) {
										//e.preventDefault();
										//e.gesture.preventDefault();

										_this.capture_slideshow_elements.$next.click();
									} );

									// Right(Prev)
									Hammer( _this.capture_slideshow_elements.$img[0] ).on( 'dragright swiperight', function( e ) {
										//e.preventDefault();
										//e.gesture.preventDefault();

										_this.capture_slideshow_elements.$prev.click();
									} );

									_this.capture_slideshow_elements.$img.data( 'capture-slideshow', 'active' );
								}
							}

							centerInWindow( _this.capture_slideshow_elements.$img ); // Initial element centering

							$( window ).on( 'resize.capture-slideshow', function() {
								clearTimeout( timers['window-resize'] );
								timers['window-resize'] = setTimeout( function() { centerInWindow( _this.capture_slideshow_elements.$img ); }, 100 );
							} );
						} );
					}

					return this;
				},
				// Fetch image models from server through the view's collection
				getImages: function( num_posts, callback ) {
					var _this = this;

					this.collection.fetch( { 
						data: {
							action: 'capture_slideshow',
							nonce: capture.nonce,
							current_post_id: $( '.capture-slideshow-current-post-id', _this.$el ).val(),
							initial_post_id: $( '.post', '.content-wrapper' ).attr( 'id' ).replace( 'post-', '' ),
							num_next_prev_posts: num_posts,
						},
						success: function( collection, response ) {
							// Find and set current model
							_.each( collection.models, function( model ) {
								if ( model.attributes.hasOwnProperty( 'current_image' ) ) {
									collection.setModel( model );
								}
							} );

							// Run the callback function
							callback( collection, response );
						},
						error:  function( collection, response ) {
							closeCaptureSlideshow(); // Close the slideshow
							alert( 'Capture Slideshow: There was an error fetching models from the server. Please try again.' );
						}
					} );
				}
			} );

			// Define a Router
			CaptureSlideshowRouter = Backbone.Router.extend( {
				prev_url_path: false,
				routes: {
					'*notFound': 'defaultRoute',
					'': 'defaultRoute'
				},
				defaultRoute: function( path ) {
					// Determine if user has clicked the back/forward buttons
					// In the future we'll use backbone to actually navigate in this case
					if ( pushState && this.prev_url_path && path !== this.prev_url_path ) {
						window.location.reload();
					}

					this.prev_url_path = path;
				}
			} );

			CaptureSlideshowRoutes = new CaptureSlideshowRouter();

			// Start the History API/Route handler
			pushState = !! ( window.history && window.history.pushState ); // Check to disable pushState for older browsers (bool)

			Backbone.history.start( { pushState: pushState, root: '' } );

			// Determine if user is on an iPhone
			isiPhone = ( navigator.userAgent.match( /iPhone/i ) ) ? true : false;
		}
	} );


	/**
	 * Capture Slideshow Plugin
	 */
	$.fn.captureSlideshow = function( options ) {
		var $document = $( document ), opts = $.extend( {}, $.fn.captureSlideshow.defaults, options ), path = ( capture.permalinks ) ? window.location.pathname : window.location.pathname + window.location.search;

		// New CaptureImageView
		if( ! ( CaptureImageView instanceof Backbone.View ) ) {
			CaptureImageView = new ImageView( {
				collection: ImageCollection,
				lightbox: ( opts.hasOwnProperty( 'lightbox' ) && opts.lightbox ) ? true : false
			} );
		}

		// Fetch initial data and render the view based on that data
		if ( CaptureImageView.collection.length === 0 ) {
			CaptureImageView.capture_slideshow_elements.$ui_elements.addClass( 'hidden hide initial-hide' ); // Initial hide

			// Fetch images from server
			// TODO: Move this functionality into the backbone view
			CaptureImageView.getImages( 2, function( collection, response ) {
				CaptureImageView.render();
			} );
		}

		// Trim trailing slash off of URL
		if ( path.substr( -1 ) === '/' ) {
			path = ( pushState ) ? path.substr( 0, path.length - 1 ) : window.location.hash.substr( 0, window.location.hash.length - 1 );
		}
		// Set path to window hash (no pushState)
		else if ( ! pushState ) {
			path = window.location.hash;
		}

		// Navigate to current URL + /lightbox/ (pushState)
		if ( pushState && ( ( capture.permalinks && path.split( '/' ).pop() !== 'lightbox' ) || ( ! capture.permalinks && path.indexOf( '&lightbox=true' ) === -1 ) ) ) {
			CaptureSlideshowRoutes.navigate( ( capture.permalinks ) ? path + '/lightbox/': path + '&lightbox=true', { replace: true } );
		}
		// No pushState
		else if ( ( capture.permalinks && path === '#lightbox' ) || ( ! capture.permalinks && path.indexOf( '&lightbox=true' ) === -1 ) ) {
			CaptureSlideshowRoutes.navigate( ( capture.permalinks ) ? '/lightbox': '&lightbox=true', { replace: true } );
		}

		// Request fullscreen access (start the slideshow)
		openCaptureSlideshow();

		// Bind all events
		// TODO: Move to event hashes inside of Backbone View
		if ( CaptureImageView.capture_slideshow_elements.$parent.data( 'capture-slideshow' ) !== 'active' ) {
			// On document keyup
			$document.on( 'keyup.capture-slideshow', function( e ) {
				// Esc - close slideshow
				if ( e.keyCode === 27 ) {
					closeCaptureSlideshow();
				}

				// Left Arrow - advance to prev image
				if ( e.keyCode === 37 ) {
					CaptureImageView.capture_slideshow_elements.$prev.click();
				}

				// Right Arrow - advance to next image
				if ( e.keyCode === 39 ) {
					CaptureImageView.capture_slideshow_elements.$next.click();
				}
			} );

			// On fullscreen event
			$document.on( 'webkitfullscreenchange mozfullscreenchange fullscreenchange', function( e ) { // Fullscreen change
				var has_fullscreen = document.fullscreen || document.mozFullScreen || document.webkitIsFullScreen || false;

				if ( ! has_fullscreen ) {
					closeCaptureSlideshow();
				}
			} );

			// On close button click cancel fullscreen access
			CaptureImageView.capture_slideshow_elements.$close.on( 'click.capture-slideshow', function( e ) {
				e.preventDefault();

				closeCaptureSlideshow();
			} );

			// Next/Previous Buttons
			CaptureImageView.capture_slideshow_elements.$prev.on( 'click.capture-slideshow', function( e ) {
				var _this = $( this ), clicks, num_posts;

				e.preventDefault();

				// If button is disabled, return
				if ( $( this ).hasClass( 'disabled' ) ) {
					return false;
				}

				// Store total number of clicks
				clicks = parseInt( $( this ).data( 'capture-slideshow.clicks' ), 10 ) || 0;
				clicks++;
				num_posts = ( clicks > 2 ) ? 5 : 2; // Set number of posts to fetch (on both sides of current post)
				$( this ).data( 'capture-slideshow.clicks', clicks );

				// Enable next button
				CaptureImageView.capture_slideshow_elements.$next.removeClass( 'disabled' );

				// Render previous image in collection
				if ( CaptureImageView.collection.indexOf( CaptureImageView.collection.getModel() ) !== 0 ) {
					CaptureImageView.collection.prevModel();
					CaptureImageView.capture_slideshow_elements.$img.removeClass( 'loaded' ).fadeOut( 400, function() {
						CaptureImageView.render();
					} );

					// If we have no more previous images and we're at the last one disable the button
					if ( CaptureImageView.collection.getModel().attributes.hasOwnProperty( 'no_more_images' ) ) {
						$( this ).addClass( 'disabled' );
					}
				}
				// Fetch new images for collection
				else {
					CaptureImageView.capture_slideshow_elements.$ui_elements.addClass( 'hidden hide initial-hide' );
					CaptureImageView.capture_slideshow_elements.$loader.removeClass( 'hidden hide' );

					CaptureImageView.capture_slideshow_elements.$img.removeClass( 'loaded' ).fadeOut( 400, function() {

						CaptureImageView.getImages( num_posts, function( collection, response ) {
							if ( CaptureImageView.collection.indexOf( CaptureImageView.collection.getModel() ) !== 0 ) {
								CaptureImageView.collection.prevModel(); // Switch to previous model
								CaptureImageView.render();

								// If we have no more next images and we're at the last one disable the button
								if ( CaptureImageView.collection.getModel().attributes.hasOwnProperty( 'no_more_images' ) ) {
									_this.addClass( 'disabled' );
								}
							}
						} );
					} );
				}
			} );

			CaptureImageView.capture_slideshow_elements.$next.on( 'click.capture-slideshow', function( e ) {
				var _this = $( this ), clicks, num_posts;

				e.preventDefault();

				// If button is disabled, return
				if ( $( this ).hasClass( 'disabled' ) ) {
					return false;
				}

				// Store total number of clicks
				clicks = parseInt( $( this ).data( 'capture-slideshow.clicks' ), 10 ) || 0;
				clicks++;
				num_posts = ( clicks > 2 ) ? 5 : 2; // Set number of posts to fetch (on both sides of current post)
				$( this ).data( 'capture-slideshow.clicks', clicks );

				// Enable previous button
				CaptureImageView.capture_slideshow_elements.$prev.removeClass( 'disabled' );

				// Render next image in collection
				if ( CaptureImageView.collection.indexOf( CaptureImageView.collection.getModel() ) !== ( CaptureImageView.collection.length - 1 ) ) {
					CaptureImageView.collection.nextModel();
					CaptureImageView.capture_slideshow_elements.$img.removeClass( 'loaded' ).fadeOut( 400, function() {
						CaptureImageView.render();
					} );

					// If we have no more previous images and we're at the last one disable the button
					if ( CaptureImageView.collection.getModel().attributes.hasOwnProperty( 'no_more_images' ) ) {
						$( this ).addClass( 'disabled' );
					}
				}
				// Fetch new images for collection
				else {
					CaptureImageView.capture_slideshow_elements.$ui_elements.addClass( 'hidden hide initial-hide' );
					CaptureImageView.capture_slideshow_elements.$loader.removeClass( 'hidden hide' );

					CaptureImageView.capture_slideshow_elements.$img.removeClass( 'loaded' ).fadeOut( 400, function() {

						CaptureImageView.getImages( num_posts, function( collection, response ) {
							if ( CaptureImageView.collection.indexOf( CaptureImageView.collection.getModel() ) !== 0 ) {
								CaptureImageView.collection.nextModel(); // Switch to previous model
								CaptureImageView.render();

								// If we have no more next images and we're at the last one disable the button
								if ( CaptureImageView.collection.getModel().attributes.hasOwnProperty( 'no_more_images' ) ) {
									_this.addClass( 'disabled' );
								}
							}
						} );
					} );
				}
			} );


			// Post Details
			CaptureImageView.capture_slideshow_elements.$parent.on( 'mousemove.capture-slideshow', function() {
				// Make sure we have UI Elements loaded
				if ( ! CaptureImageView.capture_slideshow_elements.$ui_elements ) {
					return false;
				}

				// If we're not on the initial hide
				if ( ! CaptureImageView.capture_slideshow_elements.$ui_elements.hasClass( 'initial-hide' ) && ! capture.is_mobile ) {
					CaptureImageView.capture_slideshow_elements.$ui_elements.removeClass( 'hidden hide' ); // Unhide UI elements
					clearTimeout( timers['capture-slideshow-mousemove'] );
					timers['capture-slideshow-mousemove'] = setTimeout( function() { 
						CaptureImageView.capture_slideshow_elements.$ui_elements.addClass( 'hidden hide' );
					}, 2500 );
				}
			} );

			// Set capture slideshow data
			CaptureImageView.capture_slideshow_elements.$parent.data( 'capture-slideshow', 'active' );
		}

		return this;
	};

	// Default options
	$.fn.captureSlideshow.defaults = {
	};


	/********************
	 * Helper Functions *
	 ********************/

	/**
	 * This function centers an element within the window.
	 */
	function centerInWindow( element ) {
		var img, wind;
		img = { orig_height: parseInt( element.attr( 'height' ), 10 ), orig_width: parseInt( element.attr( 'width' ), 10 ), smaller_than_window: false };
		img.aspect_ratio = img.orig_width / img.orig_height;
		img.aspect_ratio = img.aspect_ratio.toFixed( 2 ); // Trim to 2 decimal points
		wind = { aspect_ratio: $window.width() / $window.height() };
		wind.aspect_ratio = wind.aspect_ratio.toFixed( 2 ); // Trim to 2 decimal points

		// Check to see if the window width/height are larger than the original image
		if ( $window.width() > img.orig_width && $window.height() > img.orig_height ) {
			element.css( {
				position: 'absolute',
				top: ( ( $window.height() - element.height() ) / 2 ) + 'px',
				left: ( ( $window.width() - element.width() ) / 2 ) + 'px',
				width: 'auto',
				height: 'auto'
			} );

			// Set flag for image being smaller than window
			img.smaller_than_window = true;
		}

		// Check to see if window width/height aspect ratio are the same ratio as original image
		if ( wind.aspect_ratio === img.aspect_ratio && ! img.smaller_than_window ) {
			element.css( {
				width: $window.width(),
				height: $window.height(),
				top: 0,
				left: 0
			} );
		}

		// Check to see if window width/height aspect ratio is larger than original image (window width larger than height)
		if ( wind.aspect_ratio > img.aspect_ratio && ! img.smaller_than_window ) {
			element.css( {
				width: 'auto',
				height: $window.height(),
				top: 0,
				left: ( ( $window.width() - ( $window.height() * img.aspect_ratio ) ) / 2 ) + 'px'
			} );
		}

		// Check to see if window width/height aspect ratio is smaller than original image (window height larger than width)
		if ( wind.aspect_ratio < img.aspect_ratio && ! img.smaller_than_window ) {
			element.css( {
				width: $window.width(),
				height: ( $window.width() / img.aspect_ratio ),
				top: ( ( $window.height() - ( $window.width() / img.aspect_ratio ) ) / 2 ) + 'px',
				left: 0
			} );
		}
			
		return element;
	}

	/**
	 * This function requests fullscreen via the HTML5 Fullscreen API (if available).
	 */
	function openCaptureSlideshow() {
		var $body = $( 'body' );

		// Exit if an element already has fullscreen access
		if ( document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement ) {
			return false;
		}

		// W3C
		if ( CaptureImageView.capture_slideshow_elements.$parent[0].requestFullScreen ) {
			CaptureImageView.capture_slideshow_elements.$parent[0].requestFullScreen();
			CaptureImageView.capture_slideshow_elements.$parent.addClass( 'capture-slideshow-full capture-slideshow-fullscreen capture-slideshow-full-w3c capture-slideshow-fullscreen-w3c full-screen-w3c fullscreen-w3c' );
		}
		// Mozilla
		else if ( CaptureImageView.capture_slideshow_elements.$parent[0].mozRequestFullScreen ) {
			CaptureImageView.capture_slideshow_elements.$parent[0].mozRequestFullScreen();
			CaptureImageView.capture_slideshow_elements.$parent.addClass( 'capture-slideshow-full capture-slideshow-fullscreen capture-slideshow-full-moz capture-slideshow-fullscreen-moz full-screen-moz fullscreen-moz' );
		}
		// Webkit
		else if ( CaptureImageView.capture_slideshow_elements.$parent[0].webkitRequestFullScreen ) {
			CaptureImageView.capture_slideshow_elements.$parent[0].webkitRequestFullScreen();
			CaptureImageView.capture_slideshow_elements.$parent.addClass( 'capture-slideshow-container capture-slideshow-full capture-slideshow-fullscreen capture-slideshow-full-webkit capture-slideshow-fullscreen-webkit full-screen-webkit fullscreen-webkit' );
		}
		// MS
		else if ( CaptureImageView.capture_slideshow_elements.$parent[0].msRequestFullScreen ) {
			CaptureImageView.capture_slideshow_elements.$parent[0].msRequestFullScreen();
			CaptureImageView.capture_slideshow_elements.$parent.addClass( 'capture-slideshow-container capture-slideshow-full capture-slideshow-fullscreen capture-slideshow-full-ms capture-slideshow-fullscreen-ms full-screen-ms fullscreen-ms' );
		}
		// All other browsers/devices
		else {
			CaptureImageView.capture_slideshow_elements.$parent.addClass( 'capture-slideshow-full capture-slideshow-fullscreen capture-slideshow-no-fullscreen-api' );
		}

		$body.addClass( 'capture-slideshow-active lightbox' );

		return CaptureImageView.capture_slideshow_elements.$parent;
	}

	/**
	 * This function cancels fullscreen via the HTML5 Fullscreen API (if available) and also any Capture Slideshow functionality.
	 */
	function closeCaptureSlideshow() {
		var $body = $( 'body' ), path = ( capture.permalinks ) ? window.location.pathname : window.location.pathname + window.location.search;

		// Remove events and set slideshow flag to inactive
		CaptureImageView.capture_slideshow_elements.$parent.data( 'capture-slideshow', 'inactive' );
		CaptureImageView.capture_slideshow_elements.$parent.off( 'mousemove.capture-slideshow' );
		CaptureImageView.capture_slideshow_elements.$close.off( 'click.capture-slideshow' );
		CaptureImageView.capture_slideshow_elements.$prev.off( 'click.capture-slideshow' );
		CaptureImageView.capture_slideshow_elements.$next.off( 'click.capture-slideshow' );
		$( document ).off( 'keyup.capture-slideshow webkitfullscreenchange mozfullscreenchange fullscreenchange' );

		// Trim trailing slash off of URL
		if ( ( pushState && path.substr( -1 ) === '/' ) || window.location.hash.substr( -1 ) === '/' ) {
			path = ( pushState ) ? path.substr( 0, path.length - 1 ) : window.location.hash.substr( 0, window.location.hash.length - 1 );
		}
		// Set path to window hash (no pushState)
		else if ( ! pushState ) {
			path = window.location.hash;
		}

		// Navigate to current URL without "lightbox" (pushState)
		if ( pushState && ( ( capture.permalinks && path.split( '/' ).pop() === 'lightbox' ) || ( ! capture.permalinks && path.indexOf( '&lightbox=true' ) !== -1 ) ) ) {
			CaptureSlideshowRoutes.navigate( ( capture.permalinks ) ? path.replace( 'lightbox', '' ) : path.replace( '&lightbox=true', '' ), { replace: true } );
		}
		// No pushState
		else if ( ( capture.permalinks && path === '#lightbox' ) || ( ! capture.permalinks && path.indexOf( '&lightbox=true' ) !== -1 ) ) {
			CaptureSlideshowRoutes.navigate( ( capture.permalinks ) ? '/' : window.location.hash.replace( '&lightbox=true', '' ), { replace: true } );
		}

		// Check to see if we've navigated away from the current post
		if ( pushState && CaptureImageView.collection.length && ! CaptureImageView.collection.getModel().attributes.hasOwnProperty( 'initial_image' ) ) {
			// Make sure we have UI Elements loaded
			if ( CaptureImageView.capture_slideshow_elements.$ui_elements ) {
				CaptureImageView.capture_slideshow_elements.$img.removeClass( 'loaded' ).fadeOut( 400, function() {
					CaptureImageView.capture_slideshow_elements.$ui_elements.addClass( 'hidden hide initial-hide' );
					CaptureImageView.capture_slideshow_elements.$loader.removeClass( 'hidden hide' );

					// Backbone navigate to post
						CaptureSlideshowRoutes.navigate( CaptureImageView.collection.getModel().attributes.post.permalink, { trigger: true } );
						window.location.reload( CaptureImageView.collection.getModel().attributes.post.permalink ); // Force a reload of current location to ensure new post is loaded
				} );
			}
		}
		else {
			CaptureImageView.capture_slideshow_elements.$parent.attr( 'class', 'capture-slideshow-container' );
			$body.removeClass( 'capture-slideshow-active lightbox' );
		}

		// W3C
		if ( document.cancelFullScreen ) {
			document.cancelFullScreen();
		}
		// Mozilla
		else if ( document.mozCancelFullScreen ) {
			document.mozCancelFullScreen();
		}
		// Webkit
		else if ( document.webkitCancelFullScreen ) {
			document.webkitCancelFullScreen();
		}
		// MS
		else if ( document.msCancelFullScreen ) {
			document.msCancelFullScreen();
		}
	}
}( jQuery ) );