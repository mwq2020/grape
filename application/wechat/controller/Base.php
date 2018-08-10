<?php
namespace app\wechat\controller;
use \think\Db;
use think\Exception;
use think\Loader;
use think\Request;
use think\Cookie;

class Base extends \think\Controller
{

    protected $beforeActionList = [
        'all_run'
    ];

    public function all_run()
    {
        $customer_list = Db::table('customer')->select();
        $this->assign('customer_list',$customer_list);

//        $page_view_num_info = Db::table('statistics')->where(['statistics_key'=>'page_view_num'])->find();
//        $page_view_num = !empty($page_view_num_info) ? $page_view_num_info['statistics_value'] : 1;
//        $video_play_num_info = Db::table('statistics')->where(['statistics_key'=>'video_play_num'])->find();
//        $video_play_num = !empty($video_play_num_info) ? $video_play_num_info['statistics_value'] : 1;
//        $this->assign('page_view_num',$page_view_num);
//        $this->assign('video_play_num',$video_play_num);

        if(!Request::instance()->isAjax()){
            $ip =  Request::instance()->ip();
            $data = [];
            $data['ip']             = ip2long($ip);
            $data['customer_id']    = intval( Cookie::get('customer_id'));
            $data['device_info']    = $_SERVER['HTTP_USER_AGENT'];
            $data['add_time']       = time();
            Db::table('view_log')->insert($data);

            /*
            if(!empty($page_view_num_info)) {
                $data = [];
                $data['id']                 = $page_view_num_info['id'];
                $data['statistics_value']   = $page_view_num_info['statistics_value']+1;
                $data['update_time']        = time();
                Db::table('statistics')->update($data);
            } else {
                $data = [];
                $data['statistics_key']     = 'page_view_num';
                $data['statistics_value']   = 1;
                $data['add_time']           = time();
                $data['update_time']        = time();
                Db::table('statistics')->insert($data);
            }
            */
        }


    }

}