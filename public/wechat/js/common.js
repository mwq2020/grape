$(function(){

    //数据框焦点时自动清除搜索内容
    $('.Seachinput').focus(function(){
        if($(this).val() == '搜索书名'){
            $(this).val('');
        }
    })

    //数据框失去焦点并且没有输入任何内容回复提示
    $('.Seachinput').blur(function(){
        if($(this).val() == ''){
            $(this).val('搜索书名');
        }
    })

    //搜索页面点击搜索按钮
    $('#search_btn').click(function(){
        window.location = '/wechat/search/index?keyword='+$('#search_keywords').val();
    })

})