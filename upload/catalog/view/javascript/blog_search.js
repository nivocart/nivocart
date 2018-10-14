/* Blog Search | NivoCart */
$(document).ready(function() {
	$('.button-blog-search').bind('click', function() {
		url = $('base').attr('href') + 'index.php?route=blog/search';

		var search = $('input[name=\'blog_search\']').attr('value');

		if (search) {
			url += '&blog_search=' + encodeURIComponent(search);
		}

		location = url;
	});

	$('#header input[name=\'blog_search\']').bind('keydown', function(e) {
		if (e.keyCode == 13) {
			url = $('base').attr('href') + 'index.php?route=blog/search';

			var search = $('input[name=\'blog_search\']').attr('value');

			if (search) {
				url += '&blog_search=' + encodeURIComponent(search);
			}

			location = url;
		}
	});
});
