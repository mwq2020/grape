$(function(){

    // 登录弹层提交时间
    $('.lgSubmit').click(function(){
        var reader_no = $('#reader_no').val();
        var password = $('#password').val();
        if(reader_no == ''){
            return  $('#login_err_msg').html('读者证号不能为空！').parent().show();
        }
        if(password == ''){
            return  $('#login_err_msg').html('密码不能为空！').parent().show();
        }
        //console.log('登录账号',reader_no);
        //console.log('登录账号',password);
        $.ajax({
            type: 'POST',
            async: false, //注意这里要求为Boolean类型的参数，false不能写成'false'不然会被解析成true
            url: '/index/user/login' ,
            data: 'reader_no='+ reader_no +'&password=' + password + '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    //window.location = '/index/index/index';
                    location.reload();
                } else {
                    return  $('#login_err_msg').html('登录失败【'+data.msg+'】').parent().show();
                }
            } ,
            error:function(){
                return  $('#login_err_msg').html('登录失败，请刷新重试').parent().show();
            },
        });
    })

    //错误消息弹层关闭
    $('.coloseError').click(function(){
        $(this).parent().hide();
    })

    //登录弹层关闭
    $('#login_pop_div').click(function(){
        $(this).parent().parent().hide();
    })

    //搜索页面点击搜索按钮
    $('#search_btn').click(function(){
        window.location = '/index/search/index?keyword='+$('#search_keywords').val();
    })

    $('.add_like_btn').click(function(){
        var video_id = $(this).attr('data-video_id');
        $.ajax({
            type: 'POST',
            async: false, //注意这里要求为Boolean类型的参数，false不能写成'false'不然会被解析成true
            url: '/index/video/add_like' ,
            data: 'video_id='+ video_id + '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    alert('添加喜欢成功');
                    //location.reload();
                } else {
                    alert('添加喜欢失败');
                }
            } ,
            error:function(){
                alert('网络错误');
            },
        });
    })

    //查看简介按钮
    $('#view_video_desc_btn').click(function(){
        $('#video_info_desc').show();
    })

    //查看简介按钮
    $('.view_video_desc_btn').click(function(){
        $('#video_info_desc').show();
    })

    //关闭简介按钮
    $('#close_video_desc_btn').click(function(){
        $('#video_info_desc').hide();
    })


})