/**
 * Header menu toggle.
 *
 * Progressive enhancement, not a dependency. The markup in header.php is a
 * working header on its own: without this script the .e4c-js class is never
 * added, CSS leaves the toggle display:none, and the nav falls back to its own
 * centred row below the breakpoint. Everything here is additive.
 *
 * State lives in one place, the button's aria-expanded. The class on the panel
 * is derived from it rather than tracked separately, because two sources of
 * truth for "is it open" is how a menu ends up visually closed and still
 * reachable by keyboard.
 */
( function () {
	'use strict';

	var toggle = document.querySelector( '.e4c-navtoggle' );
	var panel  = document.getElementById( 'e4c-headerpanel' );

	if ( ! toggle || ! panel ) {
		return;
	}

	// Must match the max-width in style.css. Read from a custom property so the
	// stylesheet stays the single source of truth for the number: hardcoding it
	// here is how the two drift and the panel starts opening at a width where
	// the links are already visible in the row.
	var bpRaw = getComputedStyle( document.documentElement )
		.getPropertyValue( '--e4c-nav-collapse' );
	var breakpoint = parseInt( bpRaw, 10 );

	if ( isNaN( breakpoint ) ) {
		breakpoint = 920;
	}

	var mq = window.matchMedia( '(max-width: ' + breakpoint + 'px)' );

	function isOpen() {
		return 'true' === toggle.getAttribute( 'aria-expanded' );
	}

	function setOpen( open ) {
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		panel.classList.toggle( 'is-open', open );
	}

	function close( refocus ) {
		if ( ! isOpen() ) {
			return;
		}
		setOpen( false );
		if ( refocus ) {
			toggle.focus();
		}
	}

	toggle.addEventListener( 'click', function () {
		setOpen( ! isOpen() );
	} );

	// Escape closes and hands focus back to the button. Without the refocus the
	// keyboard user is dropped at the top of the document with no idea where
	// they are.
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key ) {
			close( true );
		}
	} );

	// A click outside closes, but only a click that is genuinely outside: the
	// toggle is excluded so its own click is not counted twice and immediately
	// reopened.
	document.addEventListener( 'click', function ( event ) {
		if ( ! isOpen() ) {
			return;
		}
		if ( panel.contains( event.target ) || toggle.contains( event.target ) ) {
			return;
		}
		close( false );
	} );

	// Following a link inside the panel navigates, but on an in-page anchor
	// nothing reloads and the panel would stay open over the target.
	panel.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( 'a' ) ) {
			close( false );
		}
	} );

	// Widening past the breakpoint puts the links back in the row. The open
	// state has to be cleared with it, or aria-expanded stays true on a button
	// that is no longer displayed and the next narrow resize opens nothing.
	function syncToViewport() {
		if ( ! mq.matches ) {
			close( false );
		}
	}

	if ( mq.addEventListener ) {
		mq.addEventListener( 'change', syncToViewport );
	} else if ( mq.addListener ) {
		// Safari below 14.
		mq.addListener( syncToViewport );
	}
}() );
