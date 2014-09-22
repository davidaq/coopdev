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
    return function(year, month) {
        $.get('calendar', {year:year,month:month}, function(cdata) {
            $('.events #calendar').html(tpl(cdata));
        }, 'JSON');
    };
}();
$(function() {
    var now = new Date();
    setCalendar(now.getFullYear(), now.getMonth() + 1);
    var fadeTimeout;
    $('#calendar').on('click', '.plus', function(e) {
    });
    $('#calendar').on('click', '.trash', function(e) {
        $(this).closest('.item').fadeOut(200, function() {
            $(this).remove();
        });
    });
    $('#calendar').on('click', '.wrap', function(e) {
        e.stopPropagation();
    });
    $('#calendar').on('click', '.done, .markitem', function() {
        $('.markitem').fadeOut(300);
    });
    $('#calendar').on('click', '.marks', function() {
        $('.markitem.mark-' + $(this).attr('date')).fadeIn(300);
    });
});
