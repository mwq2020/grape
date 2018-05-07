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
        var customer_id = $('#schoolId').val();
        if(customer_id == '' || customer_id == 0 || customer_id == '0'){
            return  $('#login_error').html('<span>!</span>请选择图书馆！').show();
        }
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
            data: 'customer_id='+ customer_id+'&reader_no='+ reader_no +'&password=' + password + '&m='+ Math.random() ,
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

    //视频图片点击触发网页弹层
    $('#video_img').click(function(){
        console.log('login click')
        $('#login_pop').show();
    })

    //活动报名按钮
    $('#activity_signup_btn').click(function(){
        $('#activity_singup_channel_pop').show();
    });

    //点击读者证报名
    $('#use_readerno_signup').click(function(){
        $('#activity_singup_channel_pop').hide();
        $('#activity_singup_readerno_pop').show();
    });

    //点击手机号报名
    $('#use_mobile_signup').click(function(){
        $('#activity_singup_channel_pop').hide();
        $('#activity_singup_mobile_pop').show();
    });

    //发送验证码按钮
    $('#send_code_btn').click(function(){
        console.log('click here');
        var mobile = $('input[name="mobile"]').val();
        if(mobile == '' || mobile == undefined){
            return $('#mobile_signup_error_message').html('<span>!</span>请输入正确的手机号').show();
        }
        console.log('报名手机号',mobile);
        //return;

        if($.cookie("time_limit") && $.cookie("time_limit") > 0){
            return $('#mobile_signup_error_message').html('<span>!</span>一分钟内只能发送一次').show();
        }

        $.ajax({
            type: 'POST',
            async: false,
            url: '/index/activity/send_sms' ,
            data: 'mobile='+ mobile + '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    $.cookie("valid_id",data.data.valid_id,{ expires:7, path:"/" });
                    timeCounter(60);
                } else {
                    $('#mobile_signup_error_message').html('<span>!</span>'+'发送失败【'+data.msg+'】').show();
                    //alert('发送失败【'+data.msg+'】');
                }
            } ,
            error:function(){
                $('#mobile_signup_error_message').html('<span>!</span>网络错误').show();
                //alert('网络错误');
            },
        });
    });

    //手机号报名确认按钮
    $('#mobile_signup_confirm_btn').click(function(){
        var mobile = $('input[name="mobile"]').val();
        var code = $('input[name="valid_code"]').val();
        var valid_id = $.cookie("valid_id");
        var activity_id = $('#activity_id').val();

        if(mobile == ''){
            return $('#mobile_signup_error_message').html('<span>!</span>请输入手机号').show();
        }
        if(code == ''){
            return $('#mobile_signup_error_message').html('<span>!</span>请输入验证码').show();
        }
        if(!$.cookie("valid_id")){
            return $('#mobile_signup_error_message').html('<span>!</span>请先获取验证码').show();
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
                    location.reload();
                } else {
                    return $('#mobile_signup_error_message').html('<span>!</span>'+'报名失败【'+data.msg+'】').show();
                }
            } ,
            error:function(){
                $('#mobile_signup_error_message').html('<span>!</span>网络错误').show();
            },
        });
    });

    //点击读者证报名确认按钮
    $('#readno_signup_confirm_btn').click(function() {
        var mobile = $('input[name="readerno_mobile"]').val();
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
                    location.reload();
                } else {
                    alert('报名失败【'+data.msg+'】');
                }
            } ,
            error:function(){
                alert('网络错误');
            },
        });
    })

    //上传作品按钮点击
    $('#upload_product_btn').click(function(){
        window.location = '/wechat/activity/upload_product?activity_id='+$('#activity_id').val();
    });

    //点击文件上传操作
    $('#choose_upload_btn').click(function() {
        //console.log('fffffff');
        $('#product_img').click();
    });

    //点击文件上传操作
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
                if(res.code == 200){
                    $('input[name="product_img_src"]').val(res.url);
                    //window.location = '/index/activity/info?activity_id='+$('#activity_id').val()+'&step=upload_info&file_path='+res.url;
                } else {
                    alert('上传图片失败【'+res.msg+'】');
                }
            }
        });
    });

    //确定上传内容
    $('#product_upload_confirm_btn').click(function(){
        console.log('图片上传');
        var activity_id = $('#activity_id').val();
        var product_img = $('input[name="product_img_src"]').val();
        var title = $('input[name="product_title"]').val();
        var product_desc = $('#df').val();

        $.ajax({
            type: 'POST',
            async: false,
            url: '/index/activity/upload_product' ,
            data: 'activity_id='+ activity_id+'&product_img='+product_img+'&title='+title+'&product_desc='+product_desc + '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    //alert('提交作品成功');
                    $('#upload_file_success_pop').show();
                    times = 5;
                    var timer=setInterval(function(){
                        times--;
                        if(times<=0){
                            clearInterval(timer);
                            window.location = '/wechat/activity/info?activity_id='+$('#activity_id').val();
                        }
                    },1000);
                } else {
                    alert('报名失败【'+data.msg+'】');
                }
            } ,
            error:function(){
                alert('网络错误');
            },
        });
    })
    
    $('#collect_btn').click(function () {
        var video_id = $(this).attr('data-video_id');
        $.ajax({
            type: 'POST',
            async: false, //注意这里要求为Boolean类型的参数，false不能写成'false'不然会被解析成true
            url: '/wechat/video/add_collect' ,
            data: 'video_id='+ video_id + '&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    //$('#like_success_pop').show();
                    //var timer = setInterval(function(){
                    //    $('#like_success_pop').hide();
                    //    clearTimeout(timer);
                    //},2000);
                    alert('添加喜欢收藏');
                    //location.reload();
                } else if(data.code == 400){
                    $('#login_pop').show();
                    //$("#login_pop_div").parent().parent().css("opacity","1");
                    //$("#login_pop_div").parent().parent().css("z-index","9999");
                }else {
                    alert('添加收藏失败');
                }
            } ,
            error:function(){
                alert('网络错误');
            },
        });
    })

    $('#share_btn').click(function(){
        $('#video_share_pop').show();
    })

})


//定时器
function timeCounter(times){
    var timer=null;
    timer=setInterval(function(){
        $('#send_code_btn').html('剩余'+times+'S').addClass('syRime');
        $.cookie("time_limit",times,{ expires:7, path:"/" });
        times--;
        if(times<=0){
            clearInterval(timer);
            $('#send_code_btn').html('验证码').removeClass('syRime');
            $.cookie("time_limit",0,{ expires:7, path:"/" });
        }
    },1000);


}