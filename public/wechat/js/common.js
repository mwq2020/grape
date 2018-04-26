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

    $('#login_btn').click(function(){
        console.log('login click')
        $('#login_pop').show();
    });

    $('.closed').click(function(){
        console.log('login close click')
        $(this).parent().parent().hide();
    });


    // 登录弹层提交时间
    $('#login_confirm_btn').click(function(){
        var reader_no = $('input[name=reader_no]').val();
        var password = $('input[name=password]').val();
        if(reader_no == ''){
            return  $('#login_error').html('<span>!</span>读者证号不能为空！').show();
        }
        if(password == ''){
            return  $('#login_error').html('<span>!</span>密码不能为空！').show();
        }
        $('#login_error').html('').hide();

        console.log('登录账号',reader_no);
        console.log('登录账号',password);

        $.ajax({
            type: 'POST',
            async: false, //注意这里要求为Boolean类型的参数，false不能写成'false'不然会被解析成true
            url: '/wechat/user/login' ,
            data: 'reader_no='+ reader_no +'&password=' + password + '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    //window.location = '/wechat/user/index';
                    location.reload();
                } else {
                    return $('#login_error').html('<span>!</span>'+data.msg).show();
                }
            } ,
            error:function(){
                return $('#login_error').html('<span>!</span>登录失败，请刷新重试').show();
            },
        });
    })






})