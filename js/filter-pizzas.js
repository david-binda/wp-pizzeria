(function($) {
	var filterPizzas = function(ingredience) {

		if (jQuery('ul.wp-pizzeria-ingredienceFilter input.' + ingredience).is(':checked')) {
			jQuery('table.pizzas tbody tr').not('.' + ingredience).hide();
		} else {
			jQuery('table.pizzas tbody tr').not('.' + ingredience).show();
			jQuery('ul.wp-pizzeria-ingredienceFilter input:checked').each(function () {
				jQuery('table.pizzas tbody tr').not('.' + jQuery(this).attr('class')).hide();
			})
		}
		return;
	};

	$(document).ready(function () {
		$('ul.wp-pizzeria-ingredienceFilter li img').click(function () {
			$(this).parents('li').find('input').each(function () {
				if ($(this).attr('checked') == 'checked') {
					$(this).removeAttr('checked');
				} else {
					$(this).attr('checked', 'checked');
				}
				var ingredience = $(this).siblings('input').attr('class');
				filterPizzas(ingredience);
			});
		});
		$('ul.wp-pizzeria-ingredienceFilter li input').change(function () {
			var ingredience = $(this).attr('class');
			filterPizzas(ingredience);
		});
		$('.pizzas .description').addClass('hidden');
		$('.pizza-title').click(function (e) {
			e.preventDefault();
			if (!$(this).hasClass('withdesc')) {
				$('.withdesc').removeClass('withdesc');
			}
			$(this).toggleClass('withdesc');
			$('.appended-description').remove();
			if ($(this).hasClass('withdesc')) {
				var desc = $(this).parents('tr').find('td.description div').html();
				$(this).parents('tr').after('<tr class="appended-description" style="display: none;"><td colspan="7">' + desc + '</td>');
				$('.appended-description').show('slow', 'swing', function () {
					;
				});
			}
		});
	});
})(jQuery);