jQuery( document ).ready( function( $ ) {
	mw.loader.using( 'jquery.tablesorter', function() {
		$('table.gnd-doku').tablesorter( {sortList: [ { 2: 'asc'} ]} )
	} );
} );
