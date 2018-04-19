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

    // 点击为登录 显示登录弹层
    $('.unLogin').click(function(){
        console.log('login btn click')
        $('#login_pop_div').parent().parent().show();
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

    //活动详情页面关闭弹层按钮
    $('.colseActivity').click(function(){
        $(this).parent().parent().hide();
    })

    //点击获取验证码按钮
    $('.hqYzm').click(function(){
        console.log('获取验证码');
        var mobile = $('#activity_mobile').val();
        if(mobile == ''){
            return alert('手机号不能为空');
        }
        if($.cookie("time_limit") && $.cookie("time_limit") > 0){
            return alert('一分钟内只能发送一次');
        }

        $.ajax({
            type: 'POST',
            async: false, //注意这里要求为Boolean类型的参数，false不能写成'false'不然会被解析成true
            url: '/index/activity/send_sms' ,
            data: 'mobile='+ mobile + '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    $.cookie("valid_id",data.data.valid_id,{ expires:7, path:"/" });
                    timeCounter(60);
                } else {
                    alert('发送失败【'+data.msg+'】');
                }
            } ,
            error:function(){
                alert('网络错误');
            },
        });
    })

    //手机号报名页面确认按钮
    $('#mobile_avctivity_signup').click(function(){

        var mobile = $('#activity_mobile').val();
        var code = $('#activity_mobile_code').val();
        var valid_id = $.cookie("valid_id");
        var activity_id = $('#activity_id').val();

        if(!$.cookie("valid_id")){
            return alert('请先获取验证码');
        }

        $.ajax({
            type: 'POST',
            async: false,
            url: '/index/activity/signup' ,
            data: 'mobile='+ mobile + '&code='+ code  + '&valid_id='+ valid_id + '&activity_id='+ activity_id+ '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    alert('报名成功');
                    window.location = "/index/activity/info?activity_id="+activity_id;
                } else {
                    alert('报名失败【'+data.msg+'】');
                }
            } ,
            error:function(){
                alert('网络错误');
            },
        });
    })


    //读者证号报名页面确认按钮
    $('#readno_avctivity_signup').click(function(){
        var mobile = $('#readno_mobile').val();
        var activity_id = $('#activity_id').val();

        if(mobile == ''){
            return alert('手机号不能为空');
        }

        $.ajax({
            type: 'POST',
            async: false,
            url: '/index/activity/readno_signup' ,
            data: 'mobile='+ mobile+ '&activity_id='+ activity_id+ '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    alert('报名成功');
                    window.location = "/index/activity/info?activity_id="+activity_id;
                } else {
                    alert('报名失败【'+data.msg+'】');
                }
            } ,
            error:function(){
                alert('网络错误');
            },
        });
    })


    //文件上传
    $('#product_img').on('change', function() {
        var fd = new FormData();
        fd.append("product_img", $("#product_img").get(0).files[0]);
        $.ajax({
            url: "/index/activity/upload_file",
            type: "POST",
            processData: false,
            contentType: false,
            data: fd,
            dataType:'json',
            success: function(res) {
                console.log(res);
                console.log('code',res.code);
                console.log('url',res.url);
                if(res.code == 200){
                    window.location = '/index/activity/info?activity_id='+$('#activity_id').val()+'&step=upload_info&file_path='+res.url;
                } else {
                    alert('上传图片失败【'+res.msg+'】');
                }
            }
        });
    });

    //确定上传内容
    $('.uploadBtn').click(function(){
        console.log('图片上传');
        var activity_id = $('#activity_id').val();
        var product_img = $('input[name="product_img"]').val();
        var title = $('input[name="title"]').val();
        var product_desc = $('#product_desc').val();

        $.ajax({
            type: 'POST',
            async: false,
            url: '/index/activity/upload_product' ,
            data: 'activity_id='+ activity_id+'&product_img='+product_img+'&title='+title+'&product_desc='+product_desc + '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    alert('提交作品成功');
                    window.location = "/index/activity/info?activity_id="+activity_id;
                } else {
                    alert('报名失败【'+data.msg+'】');
                }
            } ,
            error:function(){
                alert('网络错误');
            },
        });


    })

})


//定时器
function timeCounter(times){
    var timer=null;
    timer=setInterval(function(){
        $('.hqYzm').html('剩余'+times+'S').addClass('syRime');
        $.cookie("time_limit",times,{ expires:7, path:"/" });
        times--;
        if(times<=0){
            clearInterval(timer);
            $('.hqYzm').html('').removeClass('syRime');
            $.cookie("time_limit",0,{ expires:7, path:"/" });
        }
    },1000);


}
