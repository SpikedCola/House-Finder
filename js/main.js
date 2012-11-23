$(function() {        
        $("#map-container").resizable(); // only allow bottom resizing
        $("#map-button").on('click', function(e) {
                e.preventDefault();
                
                if ($(this).hasClass('up-arrow')) {
                        $("#map-container").slideUp(500, function() {
                                $("#map-button").removeClass('up-arrow').addClass('down-arrow');
                        });
                }
                else {
                        $("#map-container").slideDown(500, function() {
                                $("#map-button").removeClass('down-arrow').addClass('up-arrow');
                        });
                }
        });
});