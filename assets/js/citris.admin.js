/*! Citris - v0.1.0 - 2014-10-03
 * http://wordpress.org/themes
 * Copyright (c) 2014; * Licensed GPLv2+ */
jQuery(document).ready(function($) {
	'use strict';

	function createAutosuggest() {
		var $co = jQuery( '.curate' ),
			ajax_url = window.ctrs_curation.ajaxurl;

		$co.suggest( ajax_url, {
			onSelect: onSelect
		})
		.keydown( onKeydown );

		return $co;
	}

	function onSelect() {
		var $this = jQuery(this),
			$parent = $this.parent(),
			$input = $parent.find('input:hidden');

		var vals = this.value.split('|');
		var post = {};

		post.title = jQuery.trim( vals[0] );
		post.id = jQuery.trim( vals[1] );

		$this.val( post.title );
		$input.val( post.id );
	}

	// Prevent the enter key from triggering a submit
	function onKeydown(e) {
		if ( e.keyCode === 13 ) {
			return false;
		}
	}

	function initializePage() {
		createAutosuggest();
	}

	initializePage();

});