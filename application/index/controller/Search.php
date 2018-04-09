<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;

class Search extends \think\Controller
{
    public function index()
    {

        $video_list = Loader::model('Video')->where('status',1)->order('video_id','desc')->limit(3)->select();
        $this->assign('video_list',$video_list);
        $this->assign('page_title','视频搜索');
        return $this->fetch('search/index');
    }

}
