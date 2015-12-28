
$(function() {
    $('#block_group')
        .bind('dragenter', function(event) {
            $(this).addClass('dropHere');
            return false;
        })
        .bind('dragleave', function(event) {
            $(this).removeClass('dropHere');
            return false;
        })
        .bind('dragover', function(event) {
            return false;
        })
        .bind('drop', function(event) {
            $(this).removeClass('dropHere');
            var data = event.originalEvent.dataTransfer.getData('text/plain');
            $(this).append($('<div class="group">' + data + '</div>'));
            return true;
        });

        $('#block_group_for_steel')
        .bind('dragenter', function(event) {
            $(this).addClass('dropHere');
            return false;
        })
        .bind('dragleave', function(event) {
            $(this).removeClass('dropHere');
            return false;
        })
        .bind('dragover', function(event) {
            return false;
        })
        .bind('drop', function(event) {
            $(this).removeClass('dropHere');
            var data = event.originalEvent.dataTransfer.getData('text/plain');
            $(this).append($('<div class="group_for_steel">' + data + '</div>'));
            $('.textBlock')
                .attr('draggable', 'true')
                .bind('dragstart', function(event) {
                    event.originalEvent.dataTransfer.setData('text/plain', $(this).html());
                    return true;
                });
            return true;
        });
});