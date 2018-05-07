<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;

class Video extends Base
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

        $customer_list = Db::table('customer')->select();
        $this->assign('customer_list',$customer_list);

        //右侧视频推荐
        $recommand_list = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')
            ->where(['a.status'=>1,'a.second_cat_id'=>$video_info['second_cat_id']])
            ->field('a.*,b.cat_name')
            ->order('rand()')
            ->limit(6)
            ->select();$this->assign('recommand_list',$recommand_list);
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
                $insert_data['video_id']    = $video_id;
                $insert_data['user_id']     = $user_id;
                $insert_data['add_time']    = time();
                $insert_data['update_time'] = time();
                Db::table('user_view_list')->insert($insert_data);
            }
        }

        $cat_list = Loader::model('Category')->getCategoryList();
        $current_location = $cat_list[$video_info->cat_id]['cat_name'] ."-".$cat_list[$video_info->second_cat_id]['cat_name'];
        $this->assign('current_location',$current_location);

        return $this->fetch('index');
    }

    /**
     * 视频添加收藏
     */
    public function add_collect()
    {
        $return_data = ['code'=>200,'data'=>['status' => 1],'msg'=>''];
        try {
            $user_id = session('user_id');
            if(empty($user_id)){
                $return_data['code'] = 400;
                exit(json_encode($return_data));
                //throw new \Exception('您还没登录，请登录完再过来重试！');
            }
            $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
            if(empty($video_id)){
                throw new \Exception('入参错误');
            }
            $video_info = Loader::model('Video')->find($video_id);
            if(empty($video_info)){
                throw new \Exception('视频不存在');
            }
            $video_info->like_num += 1;
            $video_info->save();

            $collect_info = Db::table('user_collect_list')->where(['video_id'=>$video_id,'user_id'=>$user_id])->find();
            if(empty($collect_info)){
                $data = [];
                $data['video_id']   = $video_id;
                $data['user_id']    = $user_id;
                $data['add_time']   = time();
                Db::table('user_collect_list')->insert($data);
            }

        } catch (\Exception $e) {
            $return_data['code'] = 500;
            $return_data['msg'] = $e->getMessage();
        }
        exit(json_encode($return_data));
    }

}