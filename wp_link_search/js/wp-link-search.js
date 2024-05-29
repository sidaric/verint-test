jQuery(document).ready(function($) {
    $('#wp-link-search-btn').on('click', function() {
        var url = $('#wp-link-search-url').val();

        if (url === '') {
            alert('Please enter a URL.');
            return;
        }

        $('#wp-link-search-results').html('Searching...');

        $.ajax({
            url: wpLinkSearch.ajax_url,
            method: 'POST',
            data: {
                action: 'wp_link_search',
                url: url
            },
            success: function(response) {
                if (response.success) {
                    var results = response.data;
                    var output = '<ul>';

                    if (results.length > 0) {
                        results.forEach(function(result) {
                            output += '<li><a href="' + result.link + '" target="_blank">' + result.title + '</a></li>';
                        });
                    } else {
                        output += '<li>No links found.</li>';
                    }

                    output += '</ul>';
                    $('#wp-link-search-results').html(output);
                } else {
                    $('#wp-link-search-results').html(response.data);
                }
            },
            error: function() {
                $('#wp-link-search-results').html('An error occurred while searching.');
            }
        });
    });
});
