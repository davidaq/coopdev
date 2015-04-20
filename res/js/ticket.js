function postTicket(form) {
    var content = $('textarea', form).val().replace(/^\s+|\s+$/g, '');
    if(!content) {
        alert('请填写工单内容');
        return false;
    }
    if(!$('input:checked', form)[0]) {
        alert('请选择工单类型');
        return false;
    }
}
