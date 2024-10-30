(function($) {
	$(function () {
	    if ($.cookie('killThisStupidCookie')) {
	        $('.cookie_monster_div').remove()
	    }
	});
	$(document).on('click','.killCookie',function(){
	    $('.cookie_monster_div').fadeTo(300,0,function(){
	        $(this).remove();
	    });
	    $.cookie('killThisStupidCookie', '1', { expires: 7, path: '/' })
	});
})(jQuery);