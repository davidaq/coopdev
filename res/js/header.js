$(function() {
    new Headroom($('.header')[0], {
      scroll: 70,
      tolerance: 5
    }).init();
    $('.header a').tooltip({
        toggle: 'tooltip',
        placement: 'bottom'
    });
});
