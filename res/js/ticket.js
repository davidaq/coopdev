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

$(function() {
    var loading = false;
    var nomore = false;
    var lastID = 0;
    var body = $('body')[0];
    var listDom = $('#ticketlist');
    var loadingIcon = $('.ticket .list .loading');
    var loadMore = _.debounce(function() {
        if(loading || nomore || body.scrollTop < loadingIcon.offset().top - listDom.offset().top - 100) {
            return;
        }
        loading = true;
        $.ajax({
            url: 'ticket',
            method: 'get',
            data: {list:lastID},
            dataType: 'json',
            success: function(data) {
                if(data && data.length) {
                    console.log(data);
                    for(var k in data) {
                        
                    }
                    lastID = data[data.length - 1].id;
                }
                loading = false;
                loadMore();
            },
            error: function() {
                console.log('error');
                loading = false;
                loadMore();
            }
        });
    }, 300);
    loadMore();
    $(window).scroll(loadMore);
});
