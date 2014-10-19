(function($) {
	$(document).ready(function () {
		$('.tagadd').click(function () {
			var data = {
				action            : 'wp_pizzeria_add_ingredient',
				addIngredientNonce: wp_pizzeria_addIngredient.addIngredientNonce,
				postID            : wp_pizzeria_addIngredient.postID,
				tag               : $('#new-tag-wp_pizzeria_ingredient').val()
			};
			$.post(ajaxurl, data, function (response) {
				$('#new-tag-wp_pizzeria_ingredient').val('');
			});
		});
	});
})(jQuery);