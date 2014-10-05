var upload;
var save;
var addAtt2Doc;
var rmAtt;
$(function() {
    var editor = KindEditor.create('textarea.editor', {
        allowFileUpload: false,
        allowImageUpload: false,
        width: '100%',
        height: $(window).height() * 0.4,
        resizeType: 1,
        items : [
            'fontsize', 'forecolor', 'hilitecolor', '|', 'bold', 'italic', 'underline', 'strikethrough',
            'subscript', 'superscript', 'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright',
            'insertorderedlist', 'insertunorderedlist', '|', 'image', 'link', 'code', 'baidumap'
        ]
    });
    var uploadQueue = function() {
        var queue = [];
        var uploadNext = function() {
            if(queue.length <= 0)
                return;
            var item = queue.shift();
            var xhr = new XMLHttpRequest();
            xhr.open('post', '?upload=' + item.id + '&p=' + wikiquery, true);
            var xdata = JSON.stringify({
                size: item.size,
                name: encodeURIComponent(item.file.name),
            });
            xhr.setRequestHeader("X_DATA", xdata);
            xhr.send(item.file);
            xhr.upload.addEventListener("progress", function (e) {
                if(e.lengthComputable) {
                    $('#' + item.id + ' .progress-bar').css('width', (e.loaded / e.total * 100) + '%');
                }
            }, false);
            xhr.upload.addEventListener("load", function (e) {
                setTimeout(function() {
                    $('#' + item.id + ' .progress').hide();
                    $('#' + item.id + ' .control').show();
                }, 500);
            }, false);
        };
        setInterval(function() {
        }, 1000);

        return {
            push: function(item) {
                queue.push(item);
                uploadNext();
            },
        };
    }();
    var attachmentTpl = _.template($('#attachmentItemTpl').html());
    function filetype(name) {
        if(name.match(/\.(jpe?g|png|gif|bmp)$/i)) {
            return 'image';
        } else if(name.match(/\.(zip|rar)$/i)) {
            return 'zip'
        }
        return 'unsupported';
    }
    for(var k in attachments) {
        try {
            var name = attachments[k].name;
            var item = {
                id: k,
                name: name,
                size: attachments[k].size,
                type: filetype(name),
            };
            var attItem = $(attachmentTpl(item));
            $('.progress', attItem).hide();
            if(item.type == 'image') {
                var img = document.createElement('img');
                img.src = $('a', attItem).attr('href');
                $('.thumb', attItem).append(img);
                $('.control', attItem).show();
            } else if(item.type == 'unsupported') {
                $('.unsupported', attItem).show();
                $('.thumb', attItem).append('<span class="glyphicon glyphicon-ban-circle"></span>');
            } else if(item.type == 'zip') {
                $('.thumb', attItem).append('<span class="glyphicon glyphicon-compressed"></span>');
                $('.control', attItem).show();
            }
            $('.attachments').append(attItem);
        } catch(EX){}
    }
    upload = function(element) {
        if(!element.files) {
            alert('您的浏览器版本太低，无法完成异步文件上传');
            return;
        }
        for(var k in element.files) {
            var f = element.files[k];
            if(!f.name || !f.size)
                continue;
            var item = {
                id: 'attach_' + new Date().getTime() + '_' + Math.ceil(Math.random() * 9999) + '_' + k,
                file: f,
                name: f.name,
                size: function(sz) {
                    var metric = ['B', 'KB', 'MB', 'GB'];
                    var m = 0;
                    while(m < 3 && sz > 1024) {
                        m++;
                        sz /= 1024
                    }
                    sz = Math.ceil(sz * 10) / 10;
                    return sz + metric[m];
                }(f.size),
                type: filetype(f.name)
            };
            var attItem = $(attachmentTpl(item));
            if(item.type == 'image') {
                uploadQueue.push(item);
                var reader = new FileReader();
                reader.onload = function(attItem) {
                    return function(e) {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        $('.thumb', attItem).append(img);
                    };
                }(attItem);
                reader.readAsDataURL(f);
            } else if(item.type == 'unsupported') {
                uploadQueue.push(item);
                $('.unsupported', attItem).show();
                $('.progress', attItem).hide();
                $('.thumb', attItem).append('<span class="glyphicon glyphicon-ban-circle"></span>');
            } else if(item.type == 'zip') {
                $('.thumb', attItem).append('<span class="glyphicon glyphicon-compressed"></span>');
            }
            $('.attachments').append(attItem);
        }
    };
    save = function(element) {
        $(element).addClass('disabled');
        $.post('?save&p=' + wikiquery, {content:editor.html()}, function() {
            $(element).removeClass('disabled');
        });
    };
    addAtt2Doc = function(element) {
        var link = $(element).closest('.item').find('a');
        if(filetype(link.html()) == 'image') {
            editor.insertHtml('<img src="' + link.attr('href') + '"/>');
        } else {
            editor.insertHtml('<a href="' + link.attr('href') + '" target="_blank">' + link.html() + '</a>');
        }
    };
    rmAtt = function(element) {
        var item = $(element).closest('.item');
        var lang = $('.delete-prompt').html();
        lang = lang.replace('%', item.find('a').html());
        if(confirm(lang)) {
            $.get('?p=' + wikiquery, {deleteatt:item.attr('id')}, function() {
            });
            item.fadeOut(400, function() {
                $(this).remove();
            });
        }
    }
});
