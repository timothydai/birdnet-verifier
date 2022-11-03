$(document).ready(function () {
    $('#species_common_name').on('click', function () {
        var current_species = document.getElementById("selected_species").value
        var i_selected_speces = current_species.indexOf(this.value)
        if (i_selected_speces > -1) {
            document.getElementById("selected_species").value =
                current_species.slice(0, i_selected_speces) + current_species.slice(i_selected_speces + this.value.length + 1, current_species.length);
        } else {
            document.getElementById("selected_species").value += this.value + "\n";
        }
    });
});