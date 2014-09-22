$(function() {
    
});
var editOrSaveAnn = function() {
    var isEditing = false;
    return function(btn) {
        if(isEditing) {
            $('#announcement').removeAttr('contenteditable').css('min-height', '');
            var val = $('#announcement').html();
            $.post('post/announcement', {content:val}, function(r){console.log(r)});
        } else {
            $('#announcement').attr('contenteditable', true).css('min-height', 100).focus();
        }
        isEditing = !isEditing;
        $('.text', btn).each(function() {
            $(this).toggle();
        });
    }
}();
