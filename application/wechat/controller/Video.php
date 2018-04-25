<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;

class Video extends \think\Controller
{
    public function index()
    {
        //获取当前视频的详情
        $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
        $video_info = Loader::model('Video')->find($video_id);
        $this->assign('video_info',$video_info);

        //右侧视频推荐
        $recommand_list = Loader::model('Video')->limit(8)->select();
        $this->assign('recommand_list',$recommand_list);
        $this->assign('page_title','视频详情');

        if($video_info){
            $video_info->view_num += 1;
            $video_info->save();
        }
        $cat_list = Loader::model('Category')->getCategoryList();
        $current_location = $cat_list[$video_info->cat_id]['cat_name'] ."-".$cat_list[$video_info->second_cat_id]['cat_name'];
        $this->assign('current_location',$current_location);

        return $this->fetch('index');
    }

}