// JavaScript Document
window.onresize=function(){
	//wrap的宽度和浏览器的大小一致
	var wid =$(window).width();
	var heit = $(window).height();
	$(".wrap").css("width",wid)
	
		//$(".wrap").css("height",heit)	
		
	}
	
$(function(){
	//wrap的宽度和浏览器的大小一致
	var wid =$(window).width();
	var heit = $(window).height();
	$(".wrap").css("width",wid)

	
	//视频播放全屏相关视频	
	$(".relevant").click(function(){
		$(this).hide();
		$(".width").animate({"width":"12.7rem"},500);
		$(".relevantVideo").animate({"right":"0"},500);
	})
	$(".coloseRele").click(function(){
		$(".relevant").show()
		$(".width").animate({"width":"100%"},500);
		$(".relevantVideo").animate({"right":"-7.15rem"},500);
	})
})


