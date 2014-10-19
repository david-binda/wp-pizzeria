=== WP Pizzeria ===
Contributors: david.binda	
Tags: pizza, pizzeria, wordpress pizzeria, restaurant, menu, restaurant menu, carte, carte du jour
Requires at least: 3.4
Tested up to: 3.5.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Turns WordPress instalation into powerful pizzeria site backend with ability to add pizzas, beverages and pasta. 

== Description ==

Turns WordPress instalation into powerful pizzeria site backend with ability to add pizzas, beverages and pasta.

= Attract your customer =

Plugin displays pizzas in clean menu table with all your customer needs to know - menu number, title, ingrediences, price for various sizes.

But that's not all. Your site visitor is able to filter pizzas by ingrediences and thus he/she is able to find her favorite pizza in few seconds!

= Multiple pizza sizes and prices =

You get an ability to manage your pizzas in clean, fast and primarily WordPress style. Via plugin's settings page you can add multiple pizza sizes and for each size, you can set custom price.

= Works in any theme =

This plugin provides both template_functions and shortcodes - thus works in any theme you'd like to use.

= Translations =

Czech - David Biňovec - david.binda
French - Stephane CRASNIER

== Installation ==

1. Upload plugin's directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Clean and useful pizza overview for administrators
2. Frontend pizza filtering for easy choose
3. Handy extended description for your pizzas
4. Easy to use settins page
5. Simple and Quick admin menu
6. Edit your pizza's fast right from the overview
7. Ability to add ingredient image
8. Extended dashboard Right now widget

== Do you want more functionality? ==

Do not hesitate to ask for it. I'm open to any suggestions. Use the plugin's [support forum](http://wordpress.org/support/plugin/wp-pizzeria "WP Pizzeria support forum on wordpress.org")!

== Usage ==

To add content, it's easy as adding post. In your administration, you can see another post types except default post and pages. These are called Pizzas, Beverages and Pasta.

You can add them in a way you're used to add standard post or page. Plus, you can set extra params in custom metaboxes on your edit page.

In order to get new post types (pizza, beverage, pasta) to actually appear on your web, you'll have to either:

1. Place `<?php pizza_loop(); ?>` into a copy of your archive template called archive-wp_pizzeria_pizza.php instead of default loop.
1. Place `<?php beverages_loop(); ?>` into a copy of your archive template called archive-wp_pizzeria_beverage.php instead of default loop.
1. Place `<?php pasta_loop(); ?>` into a copy of your archive template called archive-wp_pizzeria_pasta.php instead of default loop.
1. Place `<?php dessert_loop(); ?>` into a copy of your archive template called archive-wp_pizzeria_dessert.php instead of default loop.

or

1. Place `[pizzas]` shortcode into a page where you'd like to display pizzas.
1. Place `[beverages]` shortcode into a page where you'd like to display beverages.
1. Place `[pasta]` shortcode into a page where you'd like to display pasta.
1. Place `[desserts]` shortcode into a page where you'd like to display desserts.

or

1. Place `[pizzas cat="term-slug"]` shortcode into a page where you'd like to display pizzas only of selected term.
1. Place `[beverages cat="term-slug"]` shortcode into a page where you'd like to display beverages only of selected term.
1. Place `[pasta cat="term-slug"]` shortcode into a page where you'd like to display pasta only of selected term.
1. Place `[desserts cat="term-slug"]` shortcode into a page where you'd like to display desserts only of selected term.

== Changelog ==

= 1.1 =
New Dessert post type to you in your pizzeria
Shortcodes suport category filter
Can use decimals in price
Added French translation
= 1.0.4 =
Prevent prices being saved when using quick edit
Reflect menu order on both shorcode and the loop display
Translation ready
Added Czech translation
= 1.0.3 =
Fix lorem ipsum message in shortcode mode
= 1.0.2 =
Fix array_key_exists() in pizza-display.php on line 14
= 1.0.1 =
Fix Warning: in_array() [function.in-array]: Wrong datatype for second argument in ...\wp-content\plugins\wp-pizzeria\nav-menu-modifications.php on line 8
= 1.0 =
* Inital release
