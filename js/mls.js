// ideally we should do the requests on the client-side, but we will run
// into cross-domain trouble, so itll have to be server-side :(

function doSearch() {
        // get the rectangle's bounds
        var bounds = rectangle.getBounds();
        $.ajax({
                url: 'receiver.php',
                type: 'post',
                dataType: 'json',
                data: { location: bounds.toUrlValue(10) }, // thanks google!
                success: function(data) {
                        if (data.status && data.status === 'ok') {
                                console.log('win!');
                        }
                        else {
                                showError();
                        }
                },
                error: function() {
                        showError();     
                }
        });
}

// so we dont have duplicate code
function showError() {
        alert('An error occured while trying to get MLS listings. Please try again later.');
}