<?php
namespace app\index\controller;
use \think\Db;
use think\Exception;
use think\Loader;

class User extends \think\Controller
{
    /**
     * 登录操作
     * @return mixed
     */
    public function login()
    {
        $return_data = ['code' => 200,'data'=>[],'msg'=>''];
        try {
            //调用接口判断登录是否正确
            $reader_no = isset($_REQUEST['reader_no']) ? $_REQUEST['reader_no'] : '';
            $user_info = Loader::model('User')->where('reader_no',$reader_no)->find();
            if(empty($user_info)){
                throw new Exception('用户不存在');
            }
            $return_data['data'] = ['user_id' => $user_info['user_id'],'reader_no' => $user_info['reader_no']];
            //session('[start]');
            session('user_id',$user_info['user_id']);
            session('reader_no',$user_info['reader_no']);
            session('avatar','/static/image/user/default_user.jpg');
            session('user_info',$user_info);
        } catch (Exception $e){
            $return_data['code'] = 500;
            $return_data['msg'] = $e->getMessage();
        }
        exit(json_encode($return_data));
    }

    /**
     * 退出操作
     */
    public function logout()
    {

    }

    /**
     * 用户的浏览页面 （全部，最近7天，30天 6个月）
     * @return mixed
     */
    public function viewlist()
    {
        $query = Db::table('user_view_list')->alias('a')->join('video b','a.video_id=b.video_id');

        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        $type = in_array($type,[1,2,3]) ? $type : 0;
        if($type == 1){
            //$end_time = time();
            $start_time = strtotime(date('y-m-d',strtotime('-7 days')));
            $query = $query->where('a.date_time','>=',$start_time);
        } elseif($type == 2) {
            //$end_time = time();
            $start_time = strtotime(date('y-m-d',strtotime('-30 days')));
            $query = $query->where('a.date_time','>=',$start_time);
        } elseif($type == 3) {
            //$end_time = time();
            $start_time = strtotime(date('y-m-d',strtotime('-180 days')));
            $query = $query->where('a.date_time','>=',$start_time);
        }
        $view_list = $query->where('a.user_id',1)->select(); //todo 用户id 替换成正确的

//        echo "<pre>";
//        print_r($view_list);
//        exit;
        $this->assign('view_list',$view_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-历史浏览记录');
        return $this->fetch('viewlist');
    }

    /**
     * 用户中心消息列表
     * @return mixed
     */
    public function message()
    {
        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        $type = in_array($type,[1,2,3]) ? $type : 1;

        $message_list = Db::table('message')->where('type',$type)->where('user_id',1)->select();
//        echo "<pre>";
//        print_r($message_list);
//        exit;
        $this->assign('message_list',$message_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-我的消息');
        return $this->fetch('message');
    }

    public function productionlist()
    {
        $query = Db::table('product')->alias('a')->join('activity b','a.activity_id=b.activity_id');

        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        $type = in_array($type,[1,2,3]) ? $type : 0;
        if($type == 1){
            $start_time = strtotime(date('y-m-d',strtotime('-7 days')));
            $query = $query->where('a.add_time','>=',$start_time);
        } elseif($type == 2) {
            $start_time = strtotime(date('y-m-d',strtotime('-30 days')));
            $query = $query->where('a.add_time','>=',$start_time);
        } elseif($type == 3) {
            $start_time = strtotime(date('y-m-d',strtotime('-180 days')));
            $query = $query->where('a.add_time','>=',$start_time);
        }
        $product_list = $query->where('a.user_id',1)->select(); //todo 用户id 替换成正确的

        $this->assign('product_list',$product_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-我的作品集');
        return $this->fetch('productionlist');
    }

    public function productioninfo()
    {

        $this->assign('page_title','用户中心-我的作品详情');
        return $this->fetch('productioninfo');
    }



}
