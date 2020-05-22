

jQuery( document ).ready( function() {

	jQuery( function() {
	    jQuery( "table.tc-discount-rule-table tbody" ).sortable();
	});
	
	var html_to_append = '<tr>';
			html_to_append += '<td>';
				html_to_append += '<input type="text" name="_tc_discount_rule[cart_total][]" />';
			html_to_append += '</td>';					
			html_to_append += '<td>';
				html_to_append += '<input type="text" name="_tc_discount_rule[discount][]" />';
			html_to_append += '</td>';
			html_to_append += '<td>';
				html_to_append += '<span class="del">x</span>';
			html_to_append += '</td>';
		html_to_append += '</tr>';

	jQuery( document.body ).on( 'click', '#tc-discount-rule-book span.del', function() {
		jQuery( this ).parent().parent().remove();
	} );
	jQuery( document.body ).on( 'click', '#tc-discount-rule-book .add-new-rule input', function( event ) {
		event.preventDefault();
		event.stopPropagation();
		jQuery( 'table.tc-discount-rule-table  tbody' ).append( html_to_append );
	} );

} );