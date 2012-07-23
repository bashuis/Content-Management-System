$(document).ready(function () {
    /* messages fade away when dismiss is clicked */
    $(".message").live("click", function (event) {
        $(this).slideUp('slow');

        return false;
    });
});