var setCalendar = function() {
    _.templateSettings = {
        evaluate: /\[\[(.*?)\]\]/g,
        interpolate: /\[\[=(.*?)\]\]/g,
        excape: /\[\[\-(.*?)\]\]/g,
    };
    var tpl;
    $(function() {
        tpl = _.template($('#caltpl').html());
    });
    var cy, cm;
    return function(year, month) {
        if(!year)
            year = cy;
        if(!month)
            month = cm;
        cy = year;
        cm = month;
        $.get('calendar', {year:year,month:month}, function(cdata) {
            $('.events #calendar').html(tpl(cdata));
        }, 'JSON');
    };
}();
$(function() {
    var now = new Date();
    setCalendar(now.getFullYear(), now.getMonth() + 1);
    $('#calendar').on('click', '.plus', function(e) {
        var val = $(this).prev('textarea').val();
        var date = $(this).closest('.wrap').attr('date');
        if(val.trim() == '')
            return;
        $.post('post/calendar?action=add', {content:val,date:date}, function() {
            $(this).prev('textarea').val('');
            $('.markitem').fadeOut(300);
            setCalendar();
        });
    });
    $('#calendar').on('click', '.trash', function(e) {
        var date = $(this).closest('.wrap').attr('date');
        var key = $(this).closest('.item').attr('key');
        $.post('post/calendar?action=delete', {date:date,key:key});
        $(this).closest('.item').fadeOut(200, function() {
            $(this).remove();
        });
    });
    $('#calendar').on('click', '.wrap', function(e) {
        e.stopPropagation();
    });
    $('#calendar').on('click', '.done, .markitem', function() {
        $('.markitem').fadeOut(300);
        setCalendar();
    });
    $('#calendar').on('click', '.marks', function() {
        $('.markitem.mark-' + $(this).attr('date')).fadeIn(300);
    });
});
