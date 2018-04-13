<?php
namespace app\manage\controller;
use \think\Db;
use think\Loader;

class Customer extends \think\Controller
{

    /**
     * 客户列表
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

        $customer_list = Loader::model('Customer')->where($where)->order('customer_id','desc')->paginate(10);
        $this->assign('customer_list', $customer_list);

        $page = $customer_list->render();
        $this->assign('page', $page);

        $this->view->engine->layout('layout');
        return $this->fetch('customer/index');
    }

    /**
     * 管理员添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/add');
        }

        try {
            if(empty($_REQUEST['customer_name'])){
                throw new \Exception('客户名称不能为空');
            }
            if(empty($_REQUEST['region_name'])){
                throw new \Exception('所属区域不能为空');
            }
            if(empty($_REQUEST['customer_link_person'])){
                throw new \Exception('客户联系人不能为空');
            }
            if(empty($_REQUEST['link_person_mobile'])){
                throw new \Exception('联系人电话不能为空');
            }
            if(empty($_REQUEST['sale_person'])){
                throw new \Exception('销售对接人不能为空');
            }
            if(empty($_REQUEST['account_no'])){
                throw new \Exception('客户账号不能为空');
            }
            if(empty($_REQUEST['password'])){
                throw new \Exception('密码不能为空');
            }
            if(empty($_REQUEST['start_time'])){
                throw new \Exception('注册时间不能为空');
            }
            if(empty($_REQUEST['end_time'])){
                throw new \Exception('结束时间不能为空');
            }

            //检查账户名是否已经存在
            $account_exist = Loader::model('Customer')->where('customer_name',$_REQUEST['customer_name'])->find();
            if(!empty($account_exist)){
                throw new \Exception('账户名已存在，请更换');
            }

            $data = [];
            $data['customer_name']  = $_REQUEST['customer_name'];
            $data['region_name']    = $_REQUEST['region_name'];
            $data['customer_link_person']   = $_REQUEST['customer_link_person'];
            $data['link_person_mobile']     = $_REQUEST['link_person_mobile'];
            $data['sale_person']    = $_REQUEST['sale_person'];
            $data['account_no']     = $_REQUEST['account_no'];
            $data['password']       = md5($_REQUEST['password']);
            $data['start_time']     = strtotime($_REQUEST['start_time']);
            $data['end_time']       = strtotime($_REQUEST['end_time']);
            $data['add_time']       = time();
            $flag = Loader::model('Customer')->insert($data);
            if(empty($flag)){
                throw new \Exception('客户添加失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/add');
        }
        return $this->redirect('/manage/customer/index');
    }

    /**
     * 管理员修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $customer_info = Loader::model('Customer')->find($_REQUEST['customer_id']);
        $this->assign('customer_info',$customer_info);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/edit');
        }

        try {
            if(empty($_REQUEST['customer_name'])){
                throw new \Exception('客户名称不能为空');
            }
            if(empty($_REQUEST['region_name'])){
                throw new \Exception('所属区域不能为空');
            }
            if(empty($_REQUEST['customer_link_person'])){
                throw new \Exception('客户联系人不能为空');
            }
            if(empty($_REQUEST['link_person_mobile'])){
                throw new \Exception('联系人电话不能为空');
            }
            if(empty($_REQUEST['sale_person'])){
                throw new \Exception('销售对接人不能为空');
            }
            if(empty($_REQUEST['account_no'])){
                throw new \Exception('客户账号不能为空');
            }
            if(empty($_REQUEST['password'])){
                throw new \Exception('密码不能为空');
            }
            if(empty($_REQUEST['start_time'])){
                throw new \Exception('注册时间不能为空');
            }
            if(empty($_REQUEST['end_time'])){
                throw new \Exception('结束时间不能为空');
            }
            if(empty($customer_info)){
                throw new \Exception('客户不存在，刷新试试');
            }

            //检查账户名是否已经存在
            $map = [];
            $map['customer_id'] = ['<>',$_REQUEST['customer_id']];
            $map['customer_name'] = $_REQUEST['customer_name'];
            $account_exist = Loader::model('Customer')->where($map)->find();
            if(!empty($account_exist)){
                throw new \Exception('客户名称已存在，请更换');
            }


            $data = [];
            $data['customer_name']  = $_REQUEST['customer_name'];
            $data['region_name']    = $_REQUEST['region_name'];
            $data['customer_link_person']   = $_REQUEST['customer_link_person'];
            $data['link_person_mobile']     = $_REQUEST['link_person_mobile'];
            $data['sale_person']    = $_REQUEST['sale_person'];
            $data['account_no']     = $_REQUEST['account_no'];
            $data['password']       = md5($_REQUEST['password']);
            $data['start_time']     = strtotime($_REQUEST['start_time']);
            $data['end_time']       = strtotime($_REQUEST['end_time']);
            $data['add_time']       = time();

            $customer_info->customer_name   = $_REQUEST['customer_name'];
            $customer_info->region_name     = $_REQUEST['region_name'];
            $customer_info->customer_link_person    = $_REQUEST['customer_link_person'];
            $customer_info->link_person_mobile      = $_REQUEST['link_person_mobile'];

            $customer_info->sale_person = $_REQUEST['sale_person'];
            $customer_info->account_no  = $_REQUEST['account_no'];
            $customer_info->password    = md5($_REQUEST['password']);
            $customer_info->start_time  = strtotime($_REQUEST['start_time']);
            $customer_info->end_time    = strtotime($_REQUEST['end_time']);
            $flag = $customer_info->save();
            if(empty($flag)){
                throw new \Exception('客户修改失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/edit');
        }
        return $this->redirect('/manage/customer/index');
    }


    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['admin_id'])){
                throw new \Exception('入参错误');
            }
            $admin_info = Loader::model('Admin')->where('admin_id',$_REQUEST['admin_id'])->find();
            if(empty($admin_info)){
                throw new \Exception('管理员不存在');
            }
            $admin_info->status = intval($_REQUEST['status']);
            $admin_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('admin/admin_add');
        }
        return $this->redirect('/manage/admin/index');
    }


}