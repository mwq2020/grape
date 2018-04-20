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

        //首页视频展示获取
        $video_list = [];
        $video_list[1] = Loader::model('Video')->where('status',1)->order('video_id','desc')->find();
        $video_list[2] = Loader::model('Video')->where('status',1)->order('video_id','desc')->find();
        $video_list[3] = Loader::model('Video')->where('status',1)->order('video_id','desc')->find();
        $video_list[4] = Loader::model('Video')->where('status',1)->order('video_id','desc')->find();
        $this->assign('video_list',$video_list);

        //首页右侧推荐展示
        $recommand_list = Loader::model('Video')->where('status',1)->order('view_num','desc')->limit(4)->select();
        $this->assign('recommand_list',$recommand_list);

        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('index');
    }

    public function test()
    {
        $view_data = [];
        return $this->fetch('test',$view_data);
    }
}