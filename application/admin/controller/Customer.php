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
            $where['add_time'] = ['>',strtotime($_REQUEST['start_time'])];
        }
        if(!empty($_REQUEST['end_time'])){
            $where[' add_time'] = ['<',strtotime($_REQUEST['end_time'])];
        }

        //失效时间
        if(!empty($_REQUEST['invalid_start_time'])){
            $where['end_time'] = ['>',strtotime($_REQUEST['invalid_start_time'])];
        }
        if(!empty($_REQUEST['invalid_end_time'])){
            $where[' end_time'] = ['<',strtotime($_REQUEST['invalid_end_time'])];
        }

        $customer_list = Loader::model('Customer')->where($where)->order('customer_id','desc')->paginate(10,false,['query' => $_GET]);
        $this->assign('customer_list', $customer_list);

        $this->view->engine->layout('layout');
        return $this->fetch('customer/index');
    }

    /**
     * 客户添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        $province_list = Db::table('region')->where('region_type=1')->select();
        $this->assign('province_list',$province_list);
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
                //throw new \Exception('所属区域不能为空');
            }
            if(empty($_REQUEST['customer_link_person'])){
                //throw new \Exception('客户联系人不能为空');
            }
            if(empty($_REQUEST['link_person_mobile'])){
                //throw new \Exception('联系人电话不能为空');
            }
            if(empty($_REQUEST['sale_person'])){
                //throw new \Exception('销售对接人不能为空');
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

            /*
            $is_user_exist = Loader::model('User')->where('reader_no',$_REQUEST['account_no'])->find();
            if(!empty($is_user_exist)){
                throw new \Exception('客户的账号已经存在，请修改账户名');
            }
            */

            $data = [];
            $data['customer_name']  = $_REQUEST['customer_name'];
            //$data['region_name']    = $_REQUEST['region_name'];
            $data['province_id']    = intval($_REQUEST['province_id']);
            $data['city_id']        = intval($_REQUEST['city_id']);
            $data['district_id']    = intval($_REQUEST['district_id']);

            $data['customer_link_person']   = empty($_REQUEST['customer_link_person']) ? '' : $_REQUEST['customer_link_person'];
            $data['link_person_mobile']     = empty($_REQUEST['link_person_mobile']) ? '' : $_REQUEST['link_person_mobile'];
            $data['sale_person']    = empty($_REQUEST['sale_person']) ? '' : $_REQUEST['sale_person'];

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
            $customer_id = Loader::model('Customer')->getLastInsID();


            //查询后台账号
            $user_info =  Loader::model('User')->where(['reader_no'=>$_REQUEST['account_no']])->find();
            if($user_info){
                $user_info->password = md5($_REQUEST['password']);
                $user_info->save();
            } else {
                $user_data = [];
                $user_data['customer_id']  = $customer_id;
                $user_data['reader_no']    = $_REQUEST['account_no'];
                $user_data['password']     =  md5($_REQUEST['password']);
                $user_data['real_name']    = $_REQUEST['customer_name'];
                $user_data['status']       = 1;
                $user_data['register_time']     = time();
                $flag = Loader::model('User')->insert($user_data);
                if(empty($flag)){
                    throw new \Exception('客户后台账号添加失败');
                }
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
     * 客户修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $customer_info = Loader::model('Customer')->find($_REQUEST['customer_id']);
        $this->assign('customer_info',$customer_info);

        $province_list = Db::table('region')->where('region_type=1')->select();
        $this->assign('province_list',$province_list);

        $city_list = [];
        if(!empty($customer_info->province_id)){
            $city_list = Db::table('region')->where('parent_id='.$customer_info->province_id)->select();
            $this->assign('city_list',$city_list);
        }

        $district_list = [];
        if(!empty($customer_info->city_id)){
            $district_list = Db::table('region')->where('parent_id='.$customer_info->city_id)->select();
            $this->assign('district_list',$district_list);
        }

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
                //throw new \Exception('所属区域不能为空');
            }
            if(empty($_REQUEST['customer_link_person'])){
                //throw new \Exception('客户联系人不能为空');
            }
            if(empty($_REQUEST['link_person_mobile'])){
                //throw new \Exception('联系人电话不能为空');
            }
            if(empty($_REQUEST['sale_person'])){
                //throw new \Exception('销售对接人不能为空');
            }
            if(empty($_REQUEST['account_no'])){
                throw new \Exception('客户账号不能为空');
            }
            if(empty($_REQUEST['password'])){
                //throw new \Exception('密码不能为空');
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
            //$customer_info->region_name     = empty($_REQUEST['region_name']) ? '' : $_REQUEST['region_name'];
            $customer_info->customer_link_person    = empty($_REQUEST['customer_link_person']) ? '' : $_REQUEST['customer_link_person'];
            $customer_info->link_person_mobile      = empty($_REQUEST['link_person_mobile']) ? '' : $_REQUEST['link_person_mobile'];

            $customer_info->province_id    = intval($_REQUEST['province_id']);
            $customer_info->city_id        = intval($_REQUEST['city_id']);
            $customer_info->district_id    = intval($_REQUEST['district_id']);

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

            //查询后台账号
            $user_info =  Loader::model('User')->where(['reader_no'=>$_REQUEST['account_no']])->find();
            if($user_info){
                if(!empty($_REQUEST['password'])){
                    $user_info->password = md5($_REQUEST['password']);
                    $user_info->save();
                }
            } else {
                $user_data = [];
                $user_data['customer_id']  = $_REQUEST['customer_id'];
                $user_data['reader_no']    = $_REQUEST['account_no'];
                $user_data['password']     =  md5($_REQUEST['password']);
                $user_data['real_name']    = $_REQUEST['customer_name'];
                $user_data['status']       = 1;
                $user_data['register_time']     = time();
                $flag = Loader::model('User')->insert($user_data);
                if(empty($flag)){
                    throw new \Exception('客户后台账号添加失败');
                }
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/edit');
        }
        return $this->redirect('/admin/customer/index');
    }


    /**
     * 删除客户操作
     */
    public function delete()
    {
        try {
            if(empty($_REQUEST['customer_id'])){
                throw new \Exception('入参错误');
            }
            $customer_info = Loader::model('Customer')->where('customer_id',$_REQUEST['customer_id'])->find();
            if(empty($customer_info)){
                throw new \Exception('客户不存在');
            }
            $customer_info->delete();

            Db::table('customer_ip_list')->where(['customer_id'=>$_REQUEST['customer_id']])->delete();

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/index');
        }
        return $this->redirect('/admin/customer/index');
    }


    /**
     * 客户列表
     * @return mixed
     */
    public function ip_list()
    {
        $where = [];
        $where['customer_id'] = $_REQUEST['customer_id'];

        $ip_list = Db::table('customer_ip_list')->where($where)->order('id','desc')->paginate(10,false,['query' => $_GET]);
        $this->assign('ip_list', $ip_list);

        $this->view->engine->layout('layout');
        return $this->fetch('customer/ip_list');
    }

    /**
     * 客户添加
     * @return mixed
     */
    public function ip_add()
    {
        $error_msg = '';
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/ip_add');
        }

        try {
            if(empty($_REQUEST['customer_id'])){
                throw new \Exception('customer_id不能为空');
            }
            if(empty($_REQUEST['start_ip'])){
                throw new \Exception('起始ip不能为空');
            }
            if(empty($_REQUEST['end_ip'])){
                throw new \Exception('结束ip不能为空');
            }
            if(empty($_REQUEST['media_url'])){
                throw new \Exception('流媒体地址不能为空');
            }

            $data = [];
            $data['customer_id']= $_REQUEST['customer_id'];
            $data['start_ip']   = ip2long($_REQUEST['start_ip']);
            $data['end_ip']     = ip2long($_REQUEST['end_ip']);
            $data['media_url']  = $_REQUEST['media_url'];
            $data['is_free_login'] = intval($_REQUEST['is_free_login']);
            $data['status']    = 1;
            $data['add_time']       = time();
            $data['update_time']       = time();
            $flag = Db::table('customer_ip_list')->insert($data);
            if(empty($flag)){
                throw new \Exception('ip限制添加失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/ip_add');
        }
        return $this->redirect('/admin/customer/ip_list?customer_id='.$_REQUEST['customer_id']);
    }

    /**
     * 客户修改
     * @return mixed
     */
    public function ip_edit()
    {
        $error_msg = '';
        $ip_info = Db::table('customer_ip_list')->find($_REQUEST['id']);
        $this->assign('ip_info',$ip_info);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/ip_edit');
        }

        try {
            if(empty($_REQUEST['customer_id'])){
                throw new \Exception('customer_id不能为空');
            }
            if(empty($_REQUEST['start_ip'])){
                throw new \Exception('起始ip不能为空');
            }
            if(empty($_REQUEST['end_ip'])){
                throw new \Exception('结束ip不能为空');
            }
            if(empty($_REQUEST['media_url'])){
                throw new \Exception('流媒体地址不能为空');
            }

            $data = [];
            $data['start_ip']      = ip2long($_REQUEST['start_ip']);
            $data['end_ip']        = ip2long($_REQUEST['end_ip']);
            $data['media_url']     = $_REQUEST['media_url'];
            $data['is_free_login'] = intval($_REQUEST['is_free_login']);
            $data['update_time']   = time();
            $flag = Db::table('customer_ip_list')->where(['id'=>$_REQUEST['id']])->update($data);
            if(empty($flag)){
                throw new \Exception('ip限制修改失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('customer/ip_edit');
        }
        return $this->redirect('/admin/customer/ip_list?customer_id='.$_REQUEST['customer_id']);
    }


    public function ip_change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['id'])){
                throw new \Exception('入参错误');
            }
            $ip_info = Db::table('customer_ip_list')->where('id',$_REQUEST['id'])->find();
            if(empty($ip_info)){
                throw new \Exception('ip限制不存在');
            }
            Db::table('customer_ip_list')->where(['id'=>$_REQUEST['id']])->update(['status' => intval($_REQUEST['status'])]);
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
        }
        return $this->redirect('/admin/customer/ip_list?customer_id='.$ip_info['customer_id']);
    }

}