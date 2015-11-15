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

    $(document).on('click', '.commentDelete', function (event) {
        event.preventDefault();
        $.post($(this).attr('name'));
        $(this).parentsUntil("li", 0, 2).remove();
    });
});