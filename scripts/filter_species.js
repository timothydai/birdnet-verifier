$(document).ready(function () {
    $('#filterMultipleSelection').on('input', function () {
        var val = this.value.toLowerCase();
        $('#species_common_name > option').hide()
            .filter(function () {
                return this.value.toLowerCase().indexOf(val) > -1;
            })
            .show();
    });
});