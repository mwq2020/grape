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

        if($video_info){
            $video_info->view_num += 1;
            $video_info->save();
        }

        return $this->fetch('video/info');
    }

    /**
     * 视频添加喜欢
     */
    public function add_like()
    {
        $return_data = ['code'=>200,'data'=>['status' => 1],'msg'=>''];
        try {
            $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
            if(empty($video_id)){
                throw new \Exception('入参错误');
            }
            $video_info = Loader::model('Video')->find($video_id);
            if(empty($video_id)){
                throw new \Exception('视频不存在');
            }

            $video_info->like_num += 1;
            $video_info->save();
        } catch (\Exception $e) {
            $return_data['code'] = 500;
            $return_data['msg'] = $e->getMessage();
        }
        exit(json_encode($return_data));
    }

}