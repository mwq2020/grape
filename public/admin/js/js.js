$(document).ready(function(){
	
	//left
	$(".leftMenu h1").toggle(function () {
            $(".leftMenu ul").animate({ height: 'toggle', opacity: 'hide' }, "fast");
            $(this).next(".leftMenu ul").animate({ height: 'toggle', opacity: 'toggle' }, "fast");
        }, function () {
            $(".leftMenu ul").animate({ height: 'toggle', opacity: 'hide' }, "fast");
            $(this).next(".leftMenu ul").animate({ height: 'toggle', opacity: 'toggle' }, "fast");
        });


        $(".leftMenu li").click(function () {
            $(".leftMenu li").removeClass("On");
            $(this).addClass("On");
        });
		
	//tag
	$(".boxP .tag li").each(function(index) {
		$(this).mouseover(function(){
				$(".tagCon .tagS").removeClass("active");
				$(".boxP .tag li.active").removeClass("active");
				$(".tagCon div").eq(index).addClass("active");
				$(this).addClass("active");
		}).mouseout(function(){
		});
	});
});









