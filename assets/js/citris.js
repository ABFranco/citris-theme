/*! Citris - v0.1.0 - 2014-10-03
 * http://wordpress.org/themes
 * Copyright (c) 2014; * Licensed GPLv2+ */
var Citris = ( function ( jQuery, window, undefined ) {
	'use strict';

	// variables available in every method
	var document  = window.document,
		slider    = document.getElementById( 'slider' ),
		filterNav = document.getElementById( 'archive-nav' ),
		initiativeNav = document.getElementById( 'initiative-nav' ),
		$slidesNav,
		$active,
		$slides,
		$filterNavItems,
		$initiativeNavItems;

	/**
	 * Bound to a click on the slide Navigation.
	 */
	function onClickSliderNav( e ) {
		if ( e.target.hash ) {
			e.preventDefault();
		}

		var $this = jQuery( e.currentTarget );

		var maxWidth = 60,
			minWidth = 122,
			windowWidth = jQuery(window).width();

		if ( windowWidth < 1260 && windowWidth > 1020 ) {
			maxWidth = 50;
		} else if ( windowWidth < 1020 && windowWidth > 860 ) {
			maxWidth = 40;
		}

		if ( ! $this.hasClass( 'active' ) ) {
			$slidesNav.removeClass( 'active' ).find( 'arrow-dn' ).remove();
			$this.addClass( 'active' ).append( '<div class="arrow-dn"></div>' );

			if ( windowWidth > 450 ) {
				if ( windowWidth > 860 ) {
					jQuery( $active ).animate({ width: minWidth+'px' }, { queue: false, duration: 400 });
					jQuery( $this ).animate({ width: maxWidth+'%' }, { queue: true, duration: 400 });
				}

				$active = $this;

				$slides.fadeOut( 300 );
				var newActive = $this.find( 'a' ).attr( 'href' );
				jQuery( newActive ).fadeIn( 1000 );
			}
		}
	}

	/**
	 * When a campus is selected for events, start the ajax request.
	 */
	function onChangeCampusFilter( e ) {
		e.preventDefault();

		var $this = jQuery( e.currentTarget ),
			campus = $this.val(),
			date = jQuery( document.getElementById( 'month-filter' ) ).val();

		getFilteredEvents( campus, date );
	}

	/**
	 * When a month is selected for events, start the ajax request.
	 */
	function onChangeMonthFilter( e ) {
		e.preventDefault();

		var $this = jQuery( e.currentTarget ),
			campus = jQuery( document.getElementById( 'ctrs-campus-filter' ) ).val(),
			date = $this.val();

		getFilteredEvents( campus, date );
	}

	/**
	 * Ajax filter Events when a Campus or Month is selected.
	 */
	function getFilteredEvents( campus, date ) {
		var $loading = jQuery( document.getElementById( 'loading' ) ),
			$eventsContainer = jQuery( document.querySelectorAll( '.events-container' ) ),
			ctrs_filter = window.ctrs_filter,
			data,
			month,
			year;

		$loading.show();
		$eventsContainer.empty();

		if ( date && '0' !== date ) {
			date = date.split( ' ' );
			month = date[0];
			year = date[1];
		} else {
			month = 0;
			year = 0;
		}
		// set up our data we will pass to our script
		data = {
			action: 'ctrs_filter_event',
			campus: campus,
			month: month,
			year: year
		};

		jQuery.ajax( {
			type: 'POST',
			url: ctrs_filter.ajaxurl,
			data: data,
			xhrFields: {
				withCredentials: true
			},
			success: function( response ) {
				$loading.hide();
				if ( response && '0' !== response ) {
					$eventsContainer.html( response );
				} else {
					$eventsContainer.html( '<div class="tribe-events-notices"><ul><li>There were no results found.</li></ul></div>' );
				}
			}
		} );
	}

	/**
	 * Ajax filter Projects when a Group is clicked.
	 */
	function onClickPostFilter( e ) {
		e.preventDefault();

		var $this = jQuery( e.currentTarget ),
			ctrs_filter = window.ctrs_filter,
			$loading = jQuery( document.getElementById( 'loading' ) ),
			term,
			campus,
			data,
			action,
			page;

		if ( ! $this.hasClass( 'active' ) ) {
			$filterNavItems.removeClass( 'active' );
			$this.addClass( 'active' );

			$loading.show();
			jQuery( filterNav ).siblings( '[class*=col-]' ).remove();

			term = e.currentTarget.getAttribute( 'data-term' );
			campus = filterNav.getAttribute( 'data-campus' );

			if ( 'none' === campus ) {
				action = 'ctrs_filter_project';
				page = filterNav.getAttribute( 'data-page' );
			} else {
				action = 'ctrs_filter_group';
				page = 1;
			}

			// set up our data we will pass to our script
			data = {
				action: action,
				filter_nonce: ctrs_filter.filter_nonce,
				group: term,
				campus: campus,
				page: page
			};

			jQuery.ajax( {
				type: 'POST',
				url: ctrs_filter.ajaxurl,
				data: data,
				xhrFields: {
					withCredentials: true
				},
				success: function( response ) {
					$loading.hide();
					if ( response && '0' !== response ) {
						jQuery( filterNav ).after( response );
					} else {
						jQuery( filterNav ).after( '<div class="col-1-2" style="min-height: 100px;"><article><p>No Results Found</p></article></div>' );
					}
				}
			} );
		}
	}

	function onClickInitiativeFilter( e ) {
		e.preventDefault();

		var $this = jQuery( e.currentTarget ),
			ctrs_filter = window.ctrs_filter,
			$loading = jQuery( document.getElementById( 'loading' ) ),
			$container = jQuery( document.getElementById( 'initiative-container' ) ),
			term,
			page,
			data;

		if ( ! $this.hasClass( 'active' ) ) {
			$initiativeNavItems.removeClass( 'active' );
			$this.addClass( 'active' );

			$loading.show();
			$container.empty();

			term = e.currentTarget.getAttribute( 'data-term' );
			page = initiativeNav.getAttribute( 'data-page' );

			// set up our data we will pass to our script
			data = {
				action: 'ctrs_filter_initiatives',
				filter_nonce: ctrs_filter.filter_nonce,
				group: term,
				page: page
			};

			jQuery.ajax( {
				type: 'POST',
				url: ctrs_filter.ajaxurl,
				data: data,
				xhrFields: {
					withCredentials: true
				},
				success: function( response ) {
					$loading.hide();
					if ( response && '0' !== response ) {
						jQuery( $container ).html( response );
					} else {
						jQuery( $container ).html( '<div class="col-1-3" style="min-height: 100px;"><article><p>No Results Found</p></article></div>' );
					}
				}
			} );
		}
	}

	/**
	 * Used to set up event handlers.
	 */
	function init() {
		var $campusFilter = jQuery( document.getElementById( 'ctrs-campus-filter' ) ),
			$monthFilter = jQuery( document.getElementById( 'month-filter' ) ),
			$trigger = jQuery( '.share-this-btn' );

		if ( slider !== null && slider !== undefined ) {
			$slides    = jQuery( slider.querySelectorAll( '.slide' ) );
			$active    = jQuery( '.active' );
			$slidesNav = jQuery( slider.querySelectorAll( '.nav li' ) );
			jQuery( slider.querySelectorAll( '.slider .nav li' ) ).on( 'click', onClickSliderNav );
		}

		if ( filterNav !== null && filterNav !== undefined ) {
			$filterNavItems = jQuery( filterNav.querySelectorAll( 'li' ) );
			$filterNavItems.on( 'click', onClickPostFilter );
		}

		if ( initiativeNav !== null && initiativeNav !== undefined ) {
			$initiativeNavItems = jQuery( initiativeNav.querySelectorAll( 'li' ) );
			$initiativeNavItems.on( 'click', onClickInitiativeFilter );
		}

		// Event filtering
		if ( $campusFilter !== null && $campusFilter !== undefined ) {
			jQuery( $campusFilter.on( 'change', onChangeCampusFilter ) );
		}

		if ( $monthFilter !== null && $monthFilter !== undefined ) {
			jQuery( $monthFilter.on( 'change', onChangeMonthFilter ) );
		}

		// Handle showing social share buttons
		$trigger.parent().hover( function() {
			jQuery( this ).toggleClass( 'active' );
		});
		$trigger.click( function( e ) {
			e.preventDefault();
		});
	}

	// Call our initializer function
	init();

	/*
	 * set up explicit functions and values to expose.
	 * Available outside this closure via Citris.methodName()
	 */
	return {
	};

} )( jQuery, window );
( function() {
	'use strict';

	var container, button, menu, windowWidth = jQuery(window).width();

	container = document.getElementById( 'site-navigation' );
	if ( ! container ) {
		return;
	}

	button = container.getElementsByTagName( 'h2' )[0];
	if ( 'undefined' === typeof button ) {
		return;
	}

	menu = container.getElementsByTagName( 'ul' )[0];

	// Hide menu toggle button if menu is empty and return early.
	if ( 'undefined' === typeof menu ) {
		button.style.display = 'none';
		return;
	}

	if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
		menu.className += ' nav-menu';
	}

	button.onclick = function() {
		if ( -1 !== container.className.indexOf( 'toggled' ) ) {
			container.className = container.className.replace( ' toggled', '' );
		} else {
			container.className += ' toggled';
		}
	};

	if ( windowWidth < 750 ) {
		jQuery( menu ).on( 'click', 'li.menu-item-has-children > a', function( e ) {
			if ( e.target === this ) {
				var $this = jQuery( e.currentTarget );
				var parent = $this.parent( 'li' );
				parent.toggleClass( 'active-menu-item' );

				e.preventDefault();
			}
		});
	}

} )();
( function() {
	'use strict';

	var is_webkit = navigator.userAgent.toLowerCase().indexOf( 'webkit' ) > -1,
		is_opera  = navigator.userAgent.toLowerCase().indexOf( 'opera' )  > -1,
		is_ie     = navigator.userAgent.toLowerCase().indexOf( 'msie' )   > -1;

	if ( ( is_webkit || is_opera || is_ie ) && 'undefined' !== typeof( document.getElementById ) ) {
		var eventMethod = ( window.addEventListener ) ? 'addEventListener' : 'attachEvent';
		window[ eventMethod ]( 'hashchange', function() {
			var element = document.getElementById( location.hash.substring( 1 ) );

			if ( element ) {
				if ( ! /^(?:a|select|input|button|textarea)$/i.test( element.tagName ) ) {
					element.tabIndex = -1;
				}

				element.focus();
			}
		}, false );
	}
})();