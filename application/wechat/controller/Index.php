<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;

class Index extends \think\Controller
{
    public function index()
    {
        //首页banner数据获取
        $banner_list = Loader::model('Banner')->where('status',1)->order('banner_id','desc')->select();
        $this->assign('banner_list',$banner_list);

        //首页推荐展示
        $recommand_list = Loader::model('Video')->where('status',1)->order('view_num','desc')->limit(4)->select();
        $this->assign('recommand_list',$recommand_list);


        //首页精彩推荐
        $video_list= [];
        $video_list[1] = Loader::model('Video')->where(['status'=>1,'cat_id'=>'1'])->order('view_num','desc')->limit(4)->select();
        $video_list[2] = Loader::model('Video')->where(['status'=>1,'cat_id'=>'2'])->order('view_num','desc')->limit(4)->select();
        $video_list[3] = Loader::model('Video')->where(['status'=>1,'cat_id'=>'3'])->order('view_num','desc')->limit(4)->select();
        $video_list[4] = Loader::model('Video')->where(['status'=>1,'cat_id'=>'4'])->order('view_num','desc')->limit(4)->select();
        $video_list[5] = Loader::model('Video')->where(['status'=>1,'cat_id'=>'5'])->order('view_num','desc')->limit(4)->select();
        $video_list[6] = Loader::model('Video')->where(['status'=>1,'cat_id'=>'6'])->order('view_num','desc')->limit(4)->select();
        $video_list[7] = Loader::model('Video')->where(['status'=>1,'cat_id'=>'7'])->order('view_num','desc')->limit(4)->select();
        $this->assign('video_list',$video_list);

        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('index');
    }

    public function test()
    {
        $view_data = [];
        return $this->fetch('test',$view_data);
    }
}