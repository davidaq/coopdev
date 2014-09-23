$(function() {
    $('.status').on('click', '.trash', function() {
        var item = $(this).closest('.status-item');
        var id = item.attr('data-id');
        item.fadeOut(300, function() {
            item.remove();
        });
        $.post('post/status?action=delete', {id:id});
    });
    fetchPrev();
});
var fetchNew;
var fetchPrev;
    var currentLatest = 1;
    var currentOldest = 0;
(function() {
    var tpl;
    $(function () {
        tpl = _.template($('#status_template').html());
        setInterval(function() {
            if($('#fetch_new_btn').is(':visible')) return;
            $.get('post/status?action=querynew', {id:currentLatest}, function(result) {
                if(result == 'new') $('#fetch_new_btn').fadeIn(200);
            });
        }, 3000);
    });
    var _fetch = function(start, limit, callback, animate) {
        if(limit > 0) $('#fetch_old_btn').hide();
        $.get('post/status?action=fetch', {start:start,limit:limit}, function(result) {
            var group = document.createElement('div');
            for(k in result) {
                var id = result[k].id;
                if(currentLatest < id) currentLatest = id;
                if(currentOldest == 0 || currentOldest > id) currentOldest = id;
                $(group).append(tpl(result[k]));
            }
            if(animate) $(group).hide();
            callback(group);
            if(animate) $(group).fadeIn(400);
            if(limit > 0 && result.length >= limit) {
                $('#fetch_old_btn').show();
            }
        }, 'JSON');
    }
    fetchPrev = function(animate) {
        _fetch(currentOldest, 18, function(group) {
            $('.events .block .status').append(group);
        }, animate);
    }
    fetchNew = function(animate) {
        $('#fetch_new_btn').hide();
        _fetch(currentLatest, -10, function(group) {
            $('.events .block .status').prepend(group);
        }, animate);
    };
})();
var editOrSaveAnn = function() {
    var isEditing = false;
    return function() {
        if(isEditing) {
            $('#announcement').removeAttr('contenteditable').css('min-height', '');
            var val = $('#announcement').html();
            $.post('post/announcement', {content:val}, function(r){console.log(r)});
        } else {
            $('#announcement').attr('contenteditable', true).css('min-height', 100).focus();
        }
        isEditing = !isEditing;
    }
}();
var postStatus = function() {
    var isEditing = false;
    return function() {
        if(isEditing) {
            $('#postStatusArea').hide();
            var val = $('#postStatusArea').val();
            $('#postStatusArea').val('');
            $.post('post/status?action=post', {content:val}, function(r){console.log(r);fetchNew();});
        } else {
            $('#postStatusArea').show();
        }
        isEditing = !isEditing;
    }
}();
