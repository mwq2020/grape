// JavaScript Document
$(function(){
	//弹层上下居中
	var height =$(".popupBox").height();
	var heit = $(window).height();
	var top= (heit-height)/2
	$(".popupBox").css("top",top)
	
	
	var height =$(window).height();
	var heit =$(".wrapMain").height();
	if(heit< height){
		$(".wrapMain").css("height",height)
		} else {
			$(".wrapMain").css("height",'')
			}
})
window.onresize=function(){
	var height =$(window).height();
	var heit =$(".wrapMain").height();
	if(heit< height){
		$(".wrapMain").css("height",height)
		} else {
			$(".wrapMain").css("height",'')
			}
}