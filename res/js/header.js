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
(function() {
    var userinfo = {};
    var requests = {};
    function setUser(element, uid) {
        var info = userinfo[uid];
        if(info) {
            $(element).html('<img src="' + info['avatar'] + '"/> ' + info.name);
            $(element).attr('title', info.title);
        }
        $(element).show();
    }
    var getUsers = _.debounce(function() {
        var uids = [];
        for(var k in requests) {
            uids.push(k);
        }
        $.post('post/userinfo', {ids:uids}, function(result) {
            for(var k in result) {
                userinfo[k] = result[k];
            }
            for(var k in requests) {
                for(var j in requests[k]) {
                    setUser(requests[k][j], k);
                }
            }
        }, 'json');
    }, 10);
    var userSignArived = function() {
        $(this).removeClass('user-sign');
        $(this).addClass('user-info');
        var uid = $(this).html();
        if(userinfo[uid]) {
            setUser(this, uid);
        } else if (requests[uid]) {
            requests[uid].push(this);
        } else {
            requests[uid] = [this];
            getUsers();
        }
    }
    try {
        $(document).arrive('.user-sign', userSignArived);
    }catch(e) {
        // interval fall back for arrive.js
        setInterval(function() {
            $('.user-sign').each(userSignArived);
        }, 500);
    }
})();
