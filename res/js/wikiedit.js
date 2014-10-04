var upload;
$(function() {
    var editor = KindEditor.create('textarea.editor', {
        allowFileUpload: false,
        allowImageUpload: false,
        width: '100%',
        height: $(window).height() * 0.4,
        resizeType: 1,
        items : [
            'fontsize', 'forecolor', 'hilitecolor', '|', 'bold', 'italic', 'underline', 'strikethrough',
            'subscript', 'superscript',
            'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
            'insertunorderedlist', '|', 'image', 'link', 'code', 'baidumap'
        ]
    });
    upload = function(element) {
        console.log(element.files);
    };
});
