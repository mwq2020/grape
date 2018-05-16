$(function () {
    $('.close_message_btn').click(function(){
        $(this).parent().parent().hide();
    })

    //设置作品弹层的右上关闭按钮
    $('.btnClose').click(function(){
        $(this).parent().parent().parent().parent().hide();
    })

    //作品列表点击设置作品按钮
    $('.set_award_grade_btn').click(function(){
        $('#set_award_grade_pop').show();
        $('#product_id').val($(this).attr('data-product_id'));
        $('#product_name').html($(this).attr('data-product_name'));
    })

    //弹层取消按钮点击
    $('.btnDark').click(function(){
        $(this).parent().parent().parent().hide();
    })

    //设置作品等级确认按钮点击
    $('.product_award_confirm_btn').click(function () {
        $.ajax({
            type: 'POST',
            async: false, //注意这里要求为Boolean类型的参数，false不能写成'false'不然会被解析成true
            url: '/admin/product/set_award_grade' ,
            data: 'product_id='+ $('input[name="product_id"]').val() +'&award_grade='+$('input[name="award_grade"]:checked').val()+'&m='+ Math.random() ,
            dataType:'json',
            cache:false, //同理
            success: function(data){
                if(data.code == 200){
                    $('#set_award_grade_pop').hide();
                    location.reload();
                } else {
                    alert('设置作品等级失败');
                }
            } ,
            error:function(){
                alert('设置作品等级失败，请刷新重试');
            },
        });
    })
})