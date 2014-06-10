(function($){
	$(function(){
		var $InputConsumerKey = $('#pocketwidget_consumer_key')
			,$ResetConsumerKey = $('#reset_consumer_key')
			,$InputAccessToken = $('#pocketwidget_access_token')
			,$ShowAccessToken = $('#show_access_token')
			,$RevokeAccessToken = $('#revoke_access_token')
		;
		$ResetConsumerKey.on('click',function(e){
			e.preventDefault();
			var sOldVal = $InputConsumerKey.val()
				,sDefault = $InputConsumerKey.attr('data-default');
			if (sOldVal!==sDefault) {
				$InputConsumerKey.val(sDefault);
				revokeAccessToken();
			}
		});
		$RevokeAccessToken.on('click',function(e){
			e.preventDefault();
			revokeAccessToken();
		});
		function revokeAccessToken(){
			$InputAccessToken.val(' ');
			$ShowAccessToken.remove();
		}
	});
})(jQuery);