$(document).ready(function () {
    $('body').on('click', '.ajaxModalButton', function (event) {
        $('#myModal').removeData('bs.modal');
        $('#myModal').modal({remote: $(this).attr('name')});
        $('#myModal').modal('show');
    });

    $('.categoryButton').click(function (event) {
        $.get($(this).attr('name'), function (response) {
            $('#tasks').empty().append(response);
        });
    });
});