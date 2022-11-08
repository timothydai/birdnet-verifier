$(document).ready(function () {
    $('#species_common_name').on('click', function () {
        if (document.getElementById("selected_species").value.length === 0) {
            var current_species = [];
        } else {
            var current_species = document.getElementById("selected_species").value.split("\n");
        }

        var i_selected_speces = current_species.indexOf(this.value);
        if (i_selected_speces === -1) {
            if (current_species.length === 0) {
                document.getElementById("selected_species").value = this.value;
            } else {
                document.getElementById("selected_species").value = current_species.join("\n") + "\n" + this.value;
            }
        } else {
            document.getElementById("selected_species").value = current_species.slice(0, i_selected_speces).concat(current_species.slice(i_selected_speces + 1)).join("\n");
        }
    });
});

$(document).ready(function () {
    $('#unlisted').on('input', function () {
        var add_species = this.value.split(",");
        if (add_species[-1] === "") {
            add_species = add_species.slice(0, -1);
        }

        document.getElementById("unlisted_selected_species").value = add_species.join("\n");
    });
}); 