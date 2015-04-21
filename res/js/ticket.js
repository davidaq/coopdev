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

$(function() {
    var loading = false;
    var nomore = false;
    var lastID = 0;
    var body = $('body')[0];
    var listDom = $('#ticketlist');
    var loadingIcon = $('.ticket .list .loading');
    var ticketItemTpl = _.template($('#ticketItemTpl').html());
    var loadMore = function() {
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
