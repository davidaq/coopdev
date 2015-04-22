function postTicket(form) {
    var content = $('textarea', form).val().replace(/^\s+|\s+$/g, '');
    if(!content) {
        $('textarea', form).val('');
        $('textarea', form).attr('placeholder', '请填写工单内容');
        $('textarea', form).focus();
        return false;
    }
    if(!$('input:checked', form)[0]) {
        alert('请选择工单类型');
        return false;
    }
}

statusText = {
    pending    : '等待中',
    working    : '处理中',
    confirming : '待确认',
    closed     : '已关闭',
    suggest    : '建议',
    bug        : 'Bug',
    defect     : '故障'
};
statusStyle = {
    pending    : 'danger',
    working    : 'info',
    confirming : 'primary',
    closed     : 'success',
    suggest    : 'info',
    bug        : 'warning',
    defect     : 'danger'
};

var loadMore;
$(function() {
    var loading = false;
    var nomore = false;
    var lastID = 0;
    var body = $('body')[0];
    var listDom = $('#ticketlist');
    var loadingIcon = $('.ticket .list .loading');
    var ticketItemTpl = _.template($('#ticketItemTpl').html());
    loadMore = function() {
        if(loading || nomore || body.scrollTop > loadingIcon.offset().top + listDom.offset().top - 0) {
            return;
        }
        loading = true;
        $.ajax({
            url: 'ticket',
            method: 'get',
            data: {list:lastID},
            dataType: 'json',
            success: function(data) {
                console.log(data);
                loading = false;
                if(data && data.length) {
                    for(var k in data) {
                        listDom.append(ticketItemTpl(data[k]));
                    }
                    lastID = data[data.length - 1].id;
                    if(data.length < 10) {
                        nomore = true;
                        loadingIcon.hide();
                    }
                } else {
                    nomore = true;
                    loadingIcon.hide();
                }
            },
            error: function() {
                console.log('error');
                loading = false;
            }
        });
    };
    loadMore();
    $(window).scroll(_.debounce(loadMore, 300));
});

function removeTag(dom) {
    var tag = $('input', dom).val().substr(3);
    if(confirm('是否删除标签: ' + tag)) {
        $(dom).remove();
        $.post('ticket', {rmtag:tag});
    }
    return false;
}

function filter() {
    var list = [];
    var inverse = false;
    $('.filter-item:checked').each(function() {
        var tn = $(this).val();
        if(tn == 'INVERSE') {
            inverse = true;
        } else if(tn.substr(0, 3) == 'tag') {
            tn = ctname(tn);
        } else {
            tn = 'label-' + tn;
        }
        list.push(tn);
    });
    var stext = '';
    var all = inverse ? 'block' : 'none';
    var single = !inverse ? 'block' : 'none';
    if(list.length > 0) {
        stext = '#ticketlist .item {display:'+all+'} #ticketlist .NEVER_USED_CLASS';
        for(var k in list) {
            stext += ',#ticketlist .item.' + list[k]
        }
        stext += '{display:'+single+'}';
    }
    $('#filterStyle').html(stext);
    loadMore();
}
function ctname(str) {
    return 'CT_' + Base64.encode(str)
            .replace(/A/g, 'AA')
            .replace(/_/g, 'A1')
            .replace(/&/g, 'A2')
            .replace(/#/g, 'A3');
}

var Base64 = {
    _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_&#",
    encode : function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output +
            this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
            this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode : function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}
