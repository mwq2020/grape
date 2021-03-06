(function($, window, document, undefined) {
	
	/**
	 * 点击下拉显示
	 */
	$(document).on("click", ".selectBox", function() {
		var ul = $(this).find("ul"),
			display = ul.css("display");
			
		display = display == "block" ? 0 : 1;
		
		$(".selectBox ul").css("display", "none");
		
		if (display) {
			ul.css("display", "block");
			display = 0;
			ul.find("li").each(function() {
				display += $(this).height();
			});
			ul.css("display", "none");
			
			if (display > 300) {
				ul.css("height",300);
				ul.css("overflow", "auto");
			}
			
			ul.slideDown(100);
		} else {
			ul.slideUp();
		}
		
		return false;
	});
	   
	/**
	 * 点击列表 文字和 value 上去
	 */
	$(document).on("click", ".selectBox ul li", function() {
		var p = $(this).parent().parent().find("p");
		p.text($(this).find("a").text());
	});
	
	$(document).on("click", function() {
		$(".selectBox ul").slideUp();
	});
})(jQuery, window, document);
