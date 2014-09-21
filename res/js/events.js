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
            $('.events .calendar').html(tpl(cdata));
        }, 'JSON');
    };
}();
$(function() {
    var now = new Date();
    setCalendar(now.getFullYear(), now.getMonth() + 1);
});
