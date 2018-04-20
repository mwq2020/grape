<?php
namespace app\manage\controller;
use \think\Db;
use think\Exception;
use think\Loader;

class Activity extends \think\Controller
{

    //用户列表（筛选）
    //详情
    //禁用
    //添加
    //修改

    /**
     * 用户列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if(!empty($_REQUEST['title'])){
            $where['title'] = ['like','%'.trim($_REQUEST['title']).'%'];
        }
        if(!empty($_REQUEST['cat_id'])){
            $where['cat_id'] = intval($_REQUEST['cat_id']);
        }

        $activity_list = Loader::model('Activity')->where($where)->order('activity_id','desc')->paginate(10);
        $this->assign('activity_list', $activity_list);

        $page = $activity_list->render();
        $this->assign('page', $page);

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
            $data['start_time']     = strtotime($_REQUEST['start_date']);
            $data['end_time']       = strtotime($_REQUEST['end_date']);
            $data['address']        = $_REQUEST['address'];
            $data['is_signup']      = isset($_REQUEST['is_signup']) ? intval($_REQUEST['is_signup']) : 0;
            $data['signup_start_time']  = strtotime($_REQUEST['signup_start_date']);
            $data['signup_end_time']    = strtotime($_REQUEST['signup_end_date']);
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
        if(empty($_POST)){
            $this->assign('error_msg', '');
            $this->view->engine->layout('layout');
            return $this->fetch('activity/edit');
        }

    }


}