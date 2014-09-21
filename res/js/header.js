$(function() {
    new Headroom($('.header')[0], {
      offset: 40,
      tolerance: 5
    }).init();
    $('.header a').tooltip({
        toggle: 'tooltip',
        placement: 'bottom'
    });
});
