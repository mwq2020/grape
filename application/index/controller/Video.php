<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;

class Video extends \think\Controller
{
    /**
     * 视频详情
     * @return mixed
     */
    public function info()
    {
        //获取当前视频的详情
        $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
        $video_info = Loader::model('Video')->find($video_id);
        $this->assign('video_info',$video_info);

        //右侧视频推荐
        $recommand_list = Loader::model('Video')->limit(5)->select();
        $this->assign('recommand_list',$recommand_list);
        $this->assign('page_title','视频详情');
        return $this->fetch('video/info');
    }

}