jQuery(document).ready(function($) {
	var fileInput = ''; 
	$('.tag-image').live('click', function() {
		fileInput = $('.tag-image');
		//var post_id = $('.ingredient_page_post_id').val();
		var post_id = 0;
		tb_show('', 'media-upload.php?type=image&amp;post_id='+post_id+'&amp;TB_iframe=true');
		return false;
	}); 
	window.original_send_to_editor = window.send_to_editor;
	window.send_to_editor = function(html) {
		if (fileInput) {
			fileurl = jQuery('img', html).attr('src');
			if (!fileurl) {
				fileurl = jQuery(html).attr('src');
			}
			$(fileInput).val(fileurl);

			tb_remove();
		} else {
			window.original_send_to_editor(html);
		}
	};
});