$(document).ready(function () {
    $('input[name="data_source"]').on('input', function () {
        $('#location_instructions').css({
            'display': 'none'
        });
        var selected_location = this.value;
        if (selected_location == 'bat') {
            $('#bat_locations').css({
                'display': 'inline',
            });
            $('#cam_locations').css({
                'display': 'none'
            });
            $('#audio_locations').css({
                'display': 'none'
            });
        } else if (selected_location == 'video') {
            $('#bat_locations').css({
                'display': 'none',
            });
            $('#cam_locations').css({
                'display': 'inline',
            });
            $('#audio_locations').css({
                'display': 'none'
            });
        } else if (selected_location == 'audio') {
            $('#bat_locations').css({
                'display': 'none',
            });
            $('#cam_locations').css({
                'display': 'none',
            });
            $('#audio_locations').css({
                'display': 'inline',
            });
        }
    });
});