<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

class User extends Base
{

    /**
     * 用户列表
     * @return mixed
     */
    public function index()
    {
        $customer_list = Loader::model('Customer')->select();
        $this->assign('customer_list',$customer_list);


        $where = [];
        if(!empty($_REQUEST['reader_no'])){
            $where['reader_no'] = ['like','%'.trim($_REQUEST['reader_no']).'%'];
        }
        if(!empty($_REQUEST['customer_id'])){
            $where['customer_id'] = intval($_REQUEST['customer_id']);
        }
        if(!empty($_REQUEST['real_name'])){
            $where['real_name'] = ['like','%'.trim($_REQUEST['real_name']).'%'];
        }
        if(!empty($_REQUEST['start_time'])){
            $where['register_time'] = ['>',strtotime($_REQUEST['start_time'])];
        }
        if(!empty($_REQUEST['end_time'])){
            $where[' register_time'] = ['<',strtotime($_REQUEST['end_time'])];
        }

        $user_list = Loader::model('User')->where($where)->order('user_id','desc')->paginate(10,false,['query' => $_GET]);
        $this->assign('user_list', $user_list);

        $this->view->engine->layout('layout');
        return $this->fetch('user/index');
    }

    /**
     * 用户添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        $customer_list = Loader::model('Customer')->select();
        $this->assign('customer_list',$customer_list);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('user/add');
        }

        try {
            if(empty($_REQUEST['real_name'])){
                throw new \Exception('姓名不能为空');
            }
            if(empty($_REQUEST['reader_no'])){
                throw new \Exception('读者证号不能为空');
            }
            if(empty($_REQUEST['telphone'])){
                throw new \Exception('联系电话不能为空');
            }
            if(empty($_REQUEST['school_name'])){
                throw new \Exception('所属学校不能为空');
            }
            if(empty($_REQUEST['password'])){
                throw new \Exception('密码不能为空');
            }
            if(empty($_REQUEST['customer_id'])){
                throw new \Exception('所属客户不能为空');
            }

            //检查账户名是否已经存在
            $account_exist = Loader::model('User')->where(['reader_no'=>$_REQUEST['reader_no']])->find();
            if(!empty($account_exist)){
                throw new \Exception('读者证号已经已存在，请更换');
            }

            $data = [];
            $data['real_name']  = $_REQUEST['real_name'];
            $data['customer_id']= intval($_REQUEST['customer_id']);
            $data['password']   = md5($_REQUEST['password']);
            $data['reader_no']  = $_REQUEST['reader_no'];
            $data['telphone']   = $_REQUEST['telphone'];
            $data['school_name'] = $_REQUEST['school_name'];
            //$data['avatar']     = $avatar;
            $data['status']     = intval($_REQUEST['status']);
            $data['register_time'] = time();
            $flag = Loader::model('User')->insert($data);
            if(empty($flag)){
                throw new \Exception('账户添加失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('user/add');
        }
        return $this->redirect('/admin/user/index');
    }

    /**
     * 管理员修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $user_info = Loader::model('User')->find($_REQUEST['user_id']);
        $this->assign('user_info',$user_info);

        $customer_list = Loader::model('Customer')->select();
        $this->assign('customer_list',$customer_list);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('user/edit');
        }

        try {
            if(empty($_REQUEST['real_name'])){
                throw new \Exception('姓名不能为空');
            }
            if(empty($_REQUEST['reader_no'])){
                throw new \Exception('读者证号不能为空');
            }
            if(empty($_REQUEST['telphone'])){
                throw new \Exception('联系电话不能为空');
            }
            if(empty($_REQUEST['school_name'])){
                throw new \Exception('所属学校不能为空');
            }
            if(empty($_REQUEST['password'])){
                //throw new \Exception('密码不能为空');
            }
            if(empty($_REQUEST['customer_id'])){
                throw new \Exception('所属客户不能为空');
            }

            //检查账户名是否已经存在
            $map = [];
            $map['user_id'] = ['<>',$_REQUEST['user_id']];
            $map['reader_no'] = $_REQUEST['reader_no'];
            $user_exist = Loader::model('User')->where($map)->find();
            if(!empty($user_exist)){
                throw new \Exception('读者证号已经已存在，请更换');
            }

            $user_info->customer_id  = $_REQUEST['customer_id'];
            if(!empty($_REQUEST['password'])) {
                $user_info->password = md5($_REQUEST['password']);
            }
            $user_info->real_name  = $_REQUEST['real_name'];
            $user_info->reader_no  = $_REQUEST['reader_no'];
            $user_info->telphone   = $_REQUEST['telphone'];
            $user_info->school_name = $_REQUEST['school_name'];
            $user_info->status     = intval($_REQUEST['status']);
            $flag = $user_info->save();
            if(empty($flag)){
                throw new \Exception('账户修改失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('user/edit');
        }
        return $this->redirect('/admin/user/index');
    }


    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['user_id'])){
                throw new \Exception('入参错误');
            }
            $user_info = Loader::model('User')->where('user_id',$_REQUEST['user_id'])->find();
            if(empty($user_info)){
                throw new \Exception('用户不存在');
            }
            $user_info->status = intval($_REQUEST['status']);
            $user_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
        }
        return $this->redirect('/admin/user/index');
    }

}