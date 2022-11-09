$(document).ready(function () {
    $('#agree').on('input', function () {
        document.getElementById("species_common_name").disabled = true;
        document.getElementById("unlisted").readOnly = true;
        document.getElementById("comments_text_area").readOnly = true;
        $('#agree-message').css({
            'display': 'block',
            'text-align': 'right'
        });
        $('#disagree-message').css({
            'display': 'none',
            'text-align': 'right'
        });
    });
}); 

$(document).ready(function () {
    $('#disagree').on('input', function () {
        document.getElementById("species_common_name").disabled = false;
        document.getElementById("unlisted").readOnly = false;
        document.getElementById("comments_text_area").readOnly = false;
        $('#agree-message').css({
            'display': 'none',
            'text-align': 'right'
        });
        $('#disagree-message').css({
            'display': 'block',
            'text-align': 'right'
        });
    });
}); 