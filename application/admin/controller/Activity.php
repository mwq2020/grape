<?php
namespace app\admin\controller;
use \think\Db;
use think\Exception;
use think\Loader;

class Activity extends Base
{


    /**
     * 活动列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if(!empty($_REQUEST['activity_name'])){
            $where['a.activity_name'] = ['like','%'.trim($_REQUEST['activity_name']).'%'];
        }
        if(!empty($_REQUEST['customer_id'])){
            $where['a.customer_id'] = intval($_REQUEST['customer_id']);
        }

        if(!empty($_REQUEST['start_time'])){
            $where['a.add_time'] = ['>',strtotime($_REQUEST['start_time'])];
        }
        if(!empty($_REQUEST['end_time'])){
            $where[' a.add_time'] = ['<',strtotime($_REQUEST['end_time'])];
        }

        if(!empty($_REQUEST['status'])){
            if($_REQUEST['status'] == 1){
                $where['a.start_time'] = ['>',time()];
            } elseif($_REQUEST['status'] == 2){
                $where['a.start_time'] = ['<',time()];
                $where['a.end_time'] = ['>',time()];
            } elseif($_REQUEST['status'] == 3){
                $where['a.end_time'] = ['<',time()];
            }
        }

        $activity_list = Db::table('activity')->alias('a')
                    ->join('customer b','a.customer_id=b.customer_id')
                    ->where($where)->order('a.activity_id','desc')
                    ->field('a.*,b.customer_name')
                    ->paginate(10 ,false,['query' => $_GET]);
        $this->assign('activity_list', $activity_list);

        $customer_list = Loader::model('Customer')->getCustomerSelectList();
        $this->assign('customer_list', $customer_list);

        $this->view->engine->layout('layout');
        return $this->fetch('activity/index');
    }

    /**
     * 添加活动页面
     * @return mixed
     */
    public function add()
    {
        if(empty($_POST)){
            $customer_list = Loader::model('Customer')->getCustomerSelectList();
            $this->assign('customer_list', $customer_list);
            $this->assign('error_msg', '');
            $this->view->engine->layout('layout');
            return $this->fetch('activity/add');
        }

        try{
            $activity_img = '';
            $activity_gallery = [];
            $file = request()->file('activity_img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/activity');
                if($info){
                    $activity_img =  '/static/image/activity/'.$info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }

            $file = request()->file('activity_gallery_1');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/activity');
                if($info){
                    $image_name =  $info->getSaveName();
                    array_push($activity_gallery,'/static/image/activity/'.$image_name);
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }
            $file = request()->file('activity_gallery_2');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/activity');
                if($info){
                    $image_name =  $info->getSaveName();
                    array_push($activity_gallery,'/static/image/activity/'.$image_name);
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }
            $file = request()->file('activity_gallery_3');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/activity');
                if($info){
                    $image_name =  $info->getSaveName();
                    array_push($activity_gallery,'/static/image/activity/'.$image_name);
                }else{
                    throw new \Exception('图片保存失败【'.'/static/image/activity/'.$file->getError().'】');
                }
            }

            $data = [];
            $data['activity_name']  = $_REQUEST['activity_name'];
            $data['customer_id']    = $_REQUEST['customer_id'];
            $data['start_time']     = strtotime($_REQUEST['start_time']);
            $data['end_time']       = strtotime($_REQUEST['end_time']);
            $data['address']        = $_REQUEST['address'];
            $data['is_signup']      = isset($_REQUEST['is_signup']) ? intval($_REQUEST['is_signup']) : 0;
            $data['signup_start_time']  = strtotime($_REQUEST['signup_start_time']);
            $data['signup_end_time']    = strtotime($_REQUEST['signup_end_time']);
            $data['max_number']         = $_REQUEST['max_number'];
            $data['activity_img']       = $activity_img;//活动图片
            $data['activity_gallery']   = json_encode($activity_gallery);//活动图集
            $data['activity_desc']      = $_REQUEST['activity_desc'];
            $data['join_rule']          = $_REQUEST['join_rule'];
            $data['activity_reward']    = $_REQUEST['activity_reward'];
            $data['special_attention']  = $_REQUEST['special_attention'];
            $data['status']             = 1;
            $data['add_time']   = time();
            $flag = Loader::model('Activity')->insert($data);
            if(empty($flag)){
                throw new \Exception('活动添加失败');
            }
        } catch (\Exception $e) {
            $this->assign('error_msg', $e->getMessage());
            $customer_list = Loader::model('Customer')->getCustomerSelectList();
            $this->assign('customer_list', $customer_list);
            $this->view->engine->layout('layout');
            return $this->fetch('activity/add');
        }
        return $this->redirect('activity/index');
    }

    /**
     * 修改活动
     * @return mixed
     */
    public function edit()
    {

        $activity_id = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : 0;
        $activity_info = Loader::model('Activity')->find($_REQUEST['activity_id']);
        $activity_info->activity_gallery = empty($activity_info->activity_gallery) ? [] : json_decode($activity_info->activity_gallery,true);
        $this->assign('activity_info', $activity_info);
        $customer_list = Loader::model('Customer')->getCustomerSelectList();
        $this->assign('customer_list', $customer_list);

        if(empty($_POST)){
            $this->assign('error_msg', '');
            $this->view->engine->layout('layout');
            return $this->fetch('activity/edit');
        }

        try{
            $activity_img = '';
            $activity_gallery = [];
            $file = request()->file('activity_img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/activity');
                if($info){
                    $activity_img =  '/static/image/activity/'.$info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
                unset($file);
            }

            $file = request()->file('activity_gallery_1');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/activity');
                if($info){
                    $image_name1 =  $info->getSaveName();
                    //echo '第一张图片'.$image_name1."<br>";
                    array_push($activity_gallery,'/static/image/activity/'.$image_name1);
                }else{
                    throw new \Exception('图片1保存失败【'.$file->getError().'】');
                }
                unset($file);
            }
            $file = request()->file('activity_gallery_2');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/activity');
                if($info){
                    $image_name2 =  $info->getSaveName();
                    //echo '第二张图片'.$image_name2."<br>";
                    array_push($activity_gallery,'/static/image/activity/'.$image_name2);
                }else{
                    throw new \Exception('图片2保存失败【'.$file->getError().'】');
                }
                unset($file);
            }
            $file = request()->file('activity_gallery_3');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/activity');
                if($info){
                    $image_name3 =  $info->getSaveName();
                    //echo '第三张图片'.$image_name3."<br>";
                    array_push($activity_gallery,'/static/image/activity/'.$image_name3);
                }else{
                    throw new \Exception('图片3保存失败【'.'/static/image/activity/'.$file->getError().'】');
                }
                unset($file);
            }

//            echo "<pre>";
//            print_r($activity_gallery);
//            exit;

            $data = [];
            $data['activity_name']  = $_REQUEST['activity_name'];
            $data['customer_id']    = $_REQUEST['customer_id'];
            $data['start_time']     = strtotime($_REQUEST['start_time']);
            $data['end_time']       = strtotime($_REQUEST['end_time']);
            $data['address']        = $_REQUEST['address'];
            $data['is_signup']      = isset($_REQUEST['is_signup']) ? intval($_REQUEST['is_signup']) : 0;
            $data['signup_start_time']  = strtotime($_REQUEST['signup_start_time']);
            $data['signup_end_time']    = strtotime($_REQUEST['signup_end_time']);
            $data['max_number']         = $_REQUEST['max_number'];
            if($activity_img){
                $data['activity_img']       = $activity_img;//活动图片
            }
            if($activity_gallery){
                $data['activity_gallery']   = json_encode($activity_gallery);//活动图集
            }

            $data['activity_desc']      = $_REQUEST['activity_desc'];
            $data['join_rule']          = $_REQUEST['join_rule'];
            $data['activity_reward']    = $_REQUEST['activity_reward'];
            $data['special_attention']  = $_REQUEST['special_attention'];
            $data['status']             = 1;
            $flag = Loader::model('Activity')->where('activity_id', $activity_id)->update($data);
            if(empty($flag)){
                throw new \Exception('活动修改失败');
            }
        } catch (\Exception $e) {
            $this->assign('error_msg', $e->getMessage());
            echo $e->getMessage();exit;

            $customer_list = Loader::model('Customer')->getCustomerSelectList();
            $this->assign('customer_list', $customer_list);
            $this->view->engine->layout('layout');
            return $this->fetch('activity/edit');
        }
        return $this->redirect('activity/index');
    }


    /**
     * 修改活动状态
     */
    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['activity_id'])){
                throw new \Exception('入参错误');
            }
            $activity_info = Loader::model('Activity')->where('activity_id',$_REQUEST['activity_id'])->find();
            if(empty($activity_info)){
                throw new \Exception('活动不存在');
            }
            $activity_info->status = intval($_REQUEST['status']);
            $activity_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
        }
        return $this->redirect('/admin/activity/index');
    }


    /**
     * 活动列表
     * @return mixed
     */
    public function signup_list()
    {
        $where = [];
        $where['a.activity_id'] = $_REQUEST['activity_id'];
        if(!empty($_REQUEST['title'])){
            $where['a.title'] = ['like','%'.trim($_REQUEST['title']).'%'];
        }
        if(!empty($_REQUEST['cat_id'])){
            $where['a.cat_id'] = intval($_REQUEST['cat_id']);
        }

        //$activity_list = Loader::model('Activity')->where($where)->order('activity_id','desc')->paginate(10);

        $signup_list = Db::table('activity_signup')->alias('a')
            ->join('user b','a.user_id=b.user_id')
            ->where($where)->order('a.id','desc')
            ->field('b.*,a.id,a.add_time as signup_time,a.mobile as signup_mobile,a.activity_id,a.status')
            ->paginate(10,false,['query' => $_GET]);
        $this->assign('signup_list', $signup_list);

        $this->view->engine->layout('layout');
        return $this->fetch('activity/signup_list');
    }

    /**
     * 编辑用户的报名状态
     */
    public function change_signup_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['id'])){
                throw new \Exception('入参错误');
            }
            $signup_info = Db::table('activity_signup')->where('id',$_REQUEST['id'])->find();
            if(empty($signup_info)){
                throw new \Exception('报名信息不存在');
            }
            Db::table('activity_signup')->where('id',$_REQUEST['id'])->update(['status'=>intval($_REQUEST['status'])]);
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
        }
        return $this->redirect('/admin/activity/signup_list?activity_id='.$signup_info['activity_id']);
    }


}