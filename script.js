$(document).ready(function() {
    $("#download").click(function() {
        $.post("download.php", function(data) {
            $("#result").html(data);
        });
    });

    $("#parse").click(function() {
        $.post("parse.php", function(data) {
            $("#result").html(data);
        });
    });

    $("#delete").click(function() {
        $.post("delete.php", function(data) {
            $("#result").html(data);
        });
    });
});
