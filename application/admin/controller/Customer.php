<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

class Customer extends Base
{

    /**
     * 客户列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if(!empty($_REQUEST['customer_name'])){
            $where['customer_name'] = ['like','%'.trim($_REQUEST['customer_name']).'%'];
        }
        if(!empty($_REQUEST['account_no'])){
            $where['account_no'] = ['like','%'.trim($_REQUEST['account_no']).'%'];
        }
        if(!empty($_REQUEST['region_name'])){
            $where['region_name'] = $_REQUEST['region_name'];
        }
        if(!empty($_REQUEST['sale_person'])){
            $where['sale_person'] = ['like','%'.trim($_REQUEST['sale_person']).'%'];
        }
        if(!empty($_REQUEST['type'])){
            $where['type'] = $_REQUEST['type'];
        }

        //注册时间
        if(!empty($_REQUEST['start_time'])){
            $where['a.add_time'] = ['>',strtotime($_REQUEST['start_time'])];
        }
        if(!empty($_REQUEST['end_time'])){
            $where[' a.add_time'] = ['<',strtotime($_REQUEST['end_time'])];
        }

        //失效时间
        if(!empty($_REQUEST['invalid_start_time'])){
            $where['a.end_time'] = ['>',strtotime($_REQUEST['invalid_start_time'])];
        }
        if(!empty($_REQUEST['invalid_end_time'])){
            $where[' a.end_time'] = ['<',strtotime($_REQUEST['invalid_end_time'])];
        }

        $customer_list = Loader::model('Customer')->where($where)->order('customer_id','desc')->paginate(10,false,['query' => $_GET]);
        $this->assign('customer_list', $customer_list);

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
            if(empty($_REQUEST['login_type'])){
                throw new \Exception('用户登录校验方式不能为空');
            }

            //检查账户名是否已经存在
            $account_exist = Loader::model('Customer')->where('customer_name',$_REQUEST['customer_name'])->find();
            if(!empty($account_exist)){
                throw new \Exception('账户名已存在，请更换');
            }


            $is_admin_exist = Loader::model('Admin')->where('account',$_REQUEST['account_no'])->find();
            if(!empty($is_admin_exist)){
                throw new \Exception('客户的账号已经存在，请修改账户名');
            }

            $data = [];
            $data['customer_name']  = $_REQUEST['customer_name'];
            $data['region_name']    = $_REQUEST['region_name'];
            $data['customer_link_person']   = $_REQUEST['customer_link_person'];
            $data['link_person_mobile']     = $_REQUEST['link_person_mobile'];
            $data['sale_person']    = $_REQUEST['sale_person'];

            $data['account_no']     = $_REQUEST['account_no'];
            $data['password']       = md5($_REQUEST['password']);

            $data['type']           = intval($_REQUEST['type']);
            $data['login_type']     = intval($_REQUEST['login_type']);

            $data['start_time']     = strtotime($_REQUEST['start_time']);
            $data['end_time']       = strtotime($_REQUEST['end_time']);
            $data['add_time']       = time();
            $flag = Loader::model('Customer')->insert($data);
            if(empty($flag)){
                throw new \Exception('客户添加失败');
            }

            $admin_data = [];
            $admin_data['account']      = $_REQUEST['account_no'];
            $admin_data['password']     =  md5($_REQUEST['password']);
            $admin_data['user_name']    = $_REQUEST['customer_name'];
            $admin_data['status']       = 1;
            $admin_data['add_time']     = time();
            $flag = Loader::model('Admin')->insert($admin_data);
            if(empty($flag)){
                throw new \Exception('客户后台账号添加失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/add');
        }
        return $this->redirect('/admin/customer/index');
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
            if(empty($_REQUEST['login_type'])){
                throw new \Exception('用户登录校验方式不能为空');
            }

            //检查账户名是否已经存在
            $map = [];
            $map['customer_id'] = ['<>',$_REQUEST['customer_id']];
            $map['customer_name'] = $_REQUEST['customer_name'];
            $account_exist = Loader::model('Customer')->where($map)->find();
            if(!empty($account_exist)){
                throw new \Exception('客户名称已存在，请更换');
            }

            $customer_info->customer_name   = $_REQUEST['customer_name'];
            $customer_info->region_name     = $_REQUEST['region_name'];
            $customer_info->customer_link_person    = $_REQUEST['customer_link_person'];
            $customer_info->link_person_mobile      = $_REQUEST['link_person_mobile'];

            $customer_info->type           = intval($_REQUEST['type']);
            $customer_info->login_type     = intval($_REQUEST['login_type']);

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
        return $this->redirect('/admin/customer/index');
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