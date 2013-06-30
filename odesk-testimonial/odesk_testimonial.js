jQuery(document).ready( function($){ 
    $( "#odesk_slider" ).slider({
			value:( $( "#jinnovate_odesk_min_score" ).val() ? $( "#jinnovate_odesk_min_score" ).val() : 1),
			min: 0,
			max: 5,
			step: 1,
			slide: function( event, ui ) {
				$( "#jinnovate_odesk_min_score" ).val( ui.value );
                            }
			});
}
)