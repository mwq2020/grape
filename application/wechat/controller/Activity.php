<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;

class Activity extends \think\Controller
{
    public function index()
    {

        $activity_list = Loader::model('Activity')->where('status',1)->order('activity_id','desc')->paginate(6,false,['query' => $_GET]);
        $page = $activity_list->render();
        $this->assign('page', $page);

        if($activity_list){
            $activity_list = $activity_list->toArray();
            $activity_list = empty($activity_list['data']) ? [] : $activity_list['data'];
            foreach($activity_list as &$row){
                $row['activity_gallery'] = json_decode($row['activity_gallery'],true);
                if($row['start_time'] > time()){
                    $row['status_txt'] = '未开始';
                } elseif($row['start_time'] <= time() && $row['end_time'] >= time()){
                    $row['status_txt'] = '进行中';
                }else {
                    $row['status_txt'] = '已结束';
                }
            }
        }

        $this->assign('activity_list',$activity_list);
        $this->assign('page_title','活动列表');
        return $this->fetch('index');
    }

    public function activity_ajax()
    {
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $activity_list = Loader::model('Activity')->where('status',1)->order('activity_id','desc')->paginate(6,false,['query' => $_GET,'page'=>$page]);

        if($activity_list){
            $activity_list = $activity_list->toArray();
            $activity_list = empty($activity_list['data']) ? [] : $activity_list['data'];
            foreach($activity_list as &$row){
                $row['activity_gallery'] = json_decode($row['activity_gallery'],true);
                if($row['start_time'] > time()){
                    $row['status_txt'] = '未开始';
                } elseif($row['start_time'] <= time() && $row['end_time'] >= time()){
                    $row['status_txt'] = '进行中';
                }else {
                    $row['status_txt'] = '已结束';
                }
            }
        }

        $this->assign('activity_list',$activity_list);
        exit(json_encode(['code'=>200,'html' => $this->fetch('activity_ajax')]));
    }



    public function info()
    {
        $activity_id = isset($_REQUEST['activity_id']) ? intval($_REQUEST['activity_id']) : 0;
        $activity_info = Loader::model('Activity')->find($activity_id);
        if($activity_info){
            $activity_info = $activity_info->toArray();
            $activity_info['activity_gallery'] = json_decode($activity_info['activity_gallery'],true);
            if($activity_info['start_time'] > time()){
                $activity_info['status_txt'] = '未开始';
            } elseif($activity_info['start_time'] <= time() && $activity_info['end_time'] >= time()){
                $activity_info['status_txt'] = '进行中';
            }else {
                $activity_info['status_txt'] = '已结束';
            }
        }

        //活动状态
        $activity_status = 'starting';
        if($activity_info['end_time'] <= time()){
            $activity_status = 'ending';
        } elseif($activity_info['start_time'] >= time()) {
            $activity_status = 'not_beginning';
        }
        $this->assign('activity_status',$activity_status);

        //判断是否输出报名按钮、上传作品按钮
        $user_id = session('user_id');
        $is_signup = 0;
        $is_upload_product = 0;
        if(!empty($user_id)) {
            $map = ['activity_id' => $activity_id,'user_id'=>$user_id];
            $signup_info = Loader::model('ActivitySignup')->where($map)->find();
            $is_signup = !empty($signup_info) ? 1 :0;

            $map = ['activity_id' => $activity_id,'user_id'=>$user_id];
            $product_info = Loader::model('Product')->where($map)->find();
            $is_upload_product = !empty($product_info) ? 1 :0;
        }
        $this->assign('is_signup',$is_signup);
        $this->assign('is_upload_product',$is_upload_product);
//        echo "<pre>";
//        print_r($activity_info);
//        exit;

        //作品页面
        $product_image_src = isset($_REQUEST['file_path']) ? $_REQUEST['file_path'] : '';
        $this->assign('product_image_src',$product_image_src);
        $product_file_size = Loader::model('Activity')->getsize($product_image_src);
        $this->assign('product_file_size',$product_file_size);

        $this->assign('activity_info',$activity_info);
        $this->assign('page_title','活动详情');
        $step = isset($_REQUEST['step']) ? $_REQUEST['step'] : '';
        $this->assign('step',$step);
        return $this->fetch('info');
    }

    /**
     * 活动规则页面
     * @return mixed
     */
    public function rule()
    {
        $activity_id = isset($_REQUEST['activity_id']) ? intval($_REQUEST['activity_id']) : 0;
        $activity_info = Loader::model('Activity')->find($activity_id);
        if($activity_info){
            $activity_info = $activity_info->toArray();

            $activity_info['activity_gallery'] = json_decode($activity_info['activity_gallery'],true);
            if($activity_info['start_time'] > time()){
                $activity_info['status_txt'] = '未开始';
            } elseif($activity_info['start_time'] <= time() && $activity_info['end_time'] >= time()){
                $activity_info['status_txt'] = '进行中';
            }else {
                $activity_info['status_txt'] = '已结束';
            }
        }

        //输出活动结束标志
        //活动状态
        $activity_status = 'starting';
        if($activity_info['end_time'] <= time()){
            $activity_status = 'ending';
        } elseif($activity_info['start_time'] >= time()) {
            $activity_status = 'not_beginning';
        }
        $this->assign('activity_status',$activity_status);

        //判断是否输出报名按钮、上传作品按钮
        $user_id = session('user_id');
        $is_signup = 0;
        $is_upload_product = 0;
        if(!empty($user_id)) {
            $map = ['activity_id' => $activity_id,'user_id'=>$user_id];
            $signup_info = Loader::model('ActivitySignup')->where($map)->find();
            $is_signup = !empty($signup_info) ? 1 :0;

            $map = ['activity_id' => $activity_id,'user_id'=>$user_id];
            $product_info = Loader::model('Product')->where($map)->find();
            $is_upload_product = !empty($product_info) ? 1 :0;
        }
        $this->assign('is_signup',$is_signup);
        $this->assign('is_upload_product',$is_upload_product);


        $this->assign('activity_info',$activity_info);
        $this->assign('page_title','活动规则');
        $step = isset($_REQUEST['step']) ? $_REQUEST['step'] : '';
        $this->assign('step',$step);
        return $this->fetch('rule');
    }


    /**
     * 活动结果页面
     */
    public function res()
    {
        $activity_id = isset($_REQUEST['activity_id']) ? intval($_REQUEST['activity_id']) : 0;
        $activity_info = Loader::model('Activity')->find($activity_id);
        if($activity_info){
            $activity_info = $activity_info->toArray();

            $activity_info['activity_gallery'] = json_decode($activity_info['activity_gallery'],true);
            if($activity_info['start_time'] > time()){
                $activity_info['status_txt'] = '未开始';
            } elseif($activity_info['start_time'] <= time() && $activity_info['end_time'] >= time()){
                $activity_info['status_txt'] = '进行中';
            }else {
                $activity_info['status_txt'] = '已结束';
            }
        }
        //活动状态
        $activity_status = 'starting';
        if($activity_info['end_time'] <= time()){
            $activity_status = 'ending';
        } elseif($activity_info['start_time'] >= time()) {
            $activity_status = 'not_beginning';
        }
        $this->assign('activity_status',$activity_status);
        $this->assign('activity_info',$activity_info);

        //获奖作品展示
        $query = Db::table('product')->alias('a')->join('user c','a.user_id=c.user_id');
        $map = [];
        $map['a.activity_id'] = $activity_id;
        $award_list = $query->where($map)->where('a.award_grade','>',0)->select();
        if(!empty($award_list)){
            foreach($award_list as &$row){
                $row['award_grade_txt'] = '';
                if($row['award_grade'] == 1){
                    $row['award_grade_txt'] = '一等奖';
                } elseif ($row['award_grade'] == 2){
                    $row['award_grade_txt'] = '二等奖';
                } elseif ($row['award_grade'] == 3){
                    $row['award_grade_txt'] = '三等奖';
                } elseif ($row['award_grade'] == 100){
                    $row['award_grade_txt'] = '优秀奖';
                }
            }
        }
        $this->assign('award_list',$award_list);

        //所有作品展示
        $query = Db::table('product')->alias('a')->join('activity b','a.activity_id=b.activity_id')->join('user c','a.user_id=c.user_id');
        $product_list = $query->select();
        $this->assign('product_list',$product_list);

        $this->assign('page_title','活动结果');
        return $this->fetch('res');
    }

    /**
     * 活动结果页面
     */
    public function product_list()
    {
        $activity_id = isset($_REQUEST['activity_id']) ? intval($_REQUEST['activity_id']) : 0;
        $activity_info = Loader::model('Activity')->find($activity_id);
        if($activity_info){
            $activity_info = $activity_info->toArray();

            $activity_info['activity_gallery'] = json_decode($activity_info['activity_gallery'],true);
            if($activity_info['start_time'] > time()){
                $activity_info['status_txt'] = '未开始';
            } elseif($activity_info['start_time'] <= time() && $activity_info['end_time'] >= time()){
                $activity_info['status_txt'] = '进行中';
            }else {
                $activity_info['status_txt'] = '已结束';
            }
        }
        $this->assign('activity_info',$activity_info);

        //活动状态
        $activity_status = 'starting';
        if($activity_info['end_time'] <= time()){
            $activity_status = 'ending';
        } elseif($activity_info['start_time'] >= time()) {
            $activity_status = 'not_beginning';
        }
        $this->assign('activity_status',$activity_status);

        $query = Db::table('product')->alias('a')->join('activity b','a.activity_id=b.activity_id')->join('user c','a.user_id=c.user_id');
        $product_list = $query->paginate(6,false,['query' => $_GET]);

        $page = $product_list->render();
        $this->assign('page', $page);

        $this->assign('product_list',$product_list);
        $this->assign('page_title','活动作品列表');
        return $this->fetch('product_list');
    }

}