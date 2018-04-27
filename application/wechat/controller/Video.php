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

        //参数错误或者视频不存在就跳转到首页
        if(empty($video_id) || empty($video_info)){
            return $this->redirect('/wechat/index/index?from=no_video');
        }

        //右侧视频推荐
        $recommand_list = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where('a.status',1)->field('a.*,b.cat_name')->limit(8)->order('a.view_num','desc')->select();
        $this->assign('recommand_list',$recommand_list);
        $this->assign('page_title','视频详情');

        if($video_info){
            $video_info->view_num += 1;
            $video_info->save();
        }

        //记录视频到浏览视频列表
        $user_id = session('user_id');
        if($user_id && $video_id){
            $view_info = Db::table('user_view_list')->where(['video_id'=>$video_id,'user_id'=>$user_id])->find();
            if(empty($view_info)){
                $insert_data = [];
                $insert_data['video_id'] = $video_id;
                $insert_data['user_id'] = $user_id;
                $insert_data['date_time'] = strtotime(date('Y-m-d'));
                $insert_data['add_time'] = time();
                $insert_data['update_time'] = time();
                Db::table('user_view_list')->insert($insert_data);
            }
        }

        $cat_list = Loader::model('Category')->getCategoryList();
        $current_location = $cat_list[$video_info->cat_id]['cat_name'] ."-".$cat_list[$video_info->second_cat_id]['cat_name'];
        $this->assign('current_location',$current_location);

        return $this->fetch('index');
    }

}