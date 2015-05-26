jQuery( document ).ready( function ( e ) {
	
	// Generate Options
	jQuery('#triplette_cookies_codegen').on('change', function (e) {
		var valueSelected = this.value;
		 switch(valueSelected) {
		 case 'analytics':
		   jQuery("#triplette_cookie_output").val ('<script type="text/plain" class="cc-onconsent-analytics">');
		   break;
		 case 'advertising':
		   jQuery("#triplette_cookie_output").val ('<script type="text/plain" class="cc-onconsent-inline-advertising" >');
		   break;
		 case 'social':
		   jQuery("#triplette_cookie_output").val ('<script type="text/plain" class="cc-onconsent-social">');
		   break;
		 }
	});
});