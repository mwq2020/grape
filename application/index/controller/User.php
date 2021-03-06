<?php
namespace app\index\controller;
use \think\Db;
use think\Exception;
use think\Loader;
use think\Session;

class User extends Base
{
    /**
     * 登录操作
     * @return mixed
     */
    public function login()
    {
        $return_data = ['code' => 200,'data'=>[],'msg'=>''];
        try {
            //调用接口判断登录是否正确、
            $reader_no = isset($_REQUEST['reader_no']) ? $_REQUEST['reader_no'] : '';
            $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
            $customer_id = isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : 0;
            if(empty($reader_no)){
                throw new \Exception('读者证号不能为空！');
            }
            if(empty($password)){
                throw new \Exception('密码不能为空！');
            }
            if(empty($customer_id)){
                throw new \Exception('图书馆不能为空！');
            }
            $customer_info = Db::table('customer')->where(['customer_id' => $customer_id])->find();
            if(empty($customer_info)){
                throw new \Exception('图书馆不存在！');
            }

            //用户本地表
            $user_info = Loader::model('User')->where(['reader_no'=>$reader_no,'customer_id'=>$customer_id])->find();
            if($user_info['status'] == 0){
                throw new \Exception('用户已禁用！');
            }

            $password = md5($password);
            if($customer_info['login_type'] == 1){ //本地用户表校验
                if(empty($user_info)){
                    throw new \Exception('登录失败，读者证号不存在');
                }
                if($user_info['password'] != $password){
                    throw new \Exception('登录失败，密码错误');
                }
                $update_data = [];
                $update_data['user_id'] = $user_info['user_id'];
                $update_data['last_login_time'] = time();
                Loader::model('User')->update($update_data);
            } else { //图书馆接口校验
                $flag = Loader::model('User')->Login($reader_no,$password);
                if(empty($flag)){
                    //throw new \Exception('登录失败，请检查用户名及密码');
                }

                if(empty($user_info)){
                    $user_info = [];
                    $user_info['customer_id']   = $customer_id;
                    $user_info['reader_no']     = $reader_no;
                    $user_info['password']      = $password;
                    $user_info['status']        = 1;
                    $user_info['register_time'] = time();
                    $user_info['last_login_time'] = time();
                    $flag = Loader::model('User')->insert($user_info);
                    if(empty($flag)){
                        throw new \Exception('登录失败，请检查用户名及密码');
                    }
                    $user_info['user_id'] = Loader::model('User')->getLastInsID();
                } else {
                    $update_data = [];
                    $update_data['user_id'] = $user_info['user_id'];
                    $update_data['last_login_time'] = time();
                    Loader::model('User')->update($update_data);
                }
            }

            $return_data['data'] = ['user_id' => $user_info['user_id'],'reader_no' => $user_info['reader_no']];
            session('user_id',$user_info['user_id']);
            session('reader_no',$user_info['reader_no']);
            if(!empty($user_info['reader_no'])){
                session('real_name',$user_info['reader_no']);
            } else {
                session('real_name',$user_info['telphone']);
            }
            session('avatar','/static/image/user/default_user.jpg');
            session('user_info',$user_info);
        } catch (\Exception $e){
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
        Session::clear();
        $this->redirect('/');
    }

    /**
     * 用户的浏览页面 （全部，最近7天，30天 6个月）
     * @return mixed
     */
    public function viewlist()
    {
        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/?error=no_login');
        }

        $query = Db::table('user_view_list')->alias('a')->join('video b','a.video_id=b.video_id')->join('category c','b.second_cat_id=c.cat_id')->field('a.add_time,b.*,c.cat_name');

        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        $type = in_array($type,[1,2,3]) ? $type : 0;
        if($type == 1){
            //$end_time = time();
            $start_time = strtotime(date('y-m-d',strtotime('-7 days')));
            $query = $query->where('a.add_time','>=',$start_time);
        } elseif($type == 2) {
            //$end_time = time();
            $start_time = strtotime(date('y-m-d',strtotime('-30 days')));
            $query = $query->where('a.add_time','>=',$start_time);
        } elseif($type == 3) {
            //$end_time = time();
            $start_time = strtotime(date('y-m-d',strtotime('-180 days')));
            $query = $query->where('a.add_time','>=',$start_time);
        }
        $view_list = $query->where('a.user_id',$user_id)->paginate(8,false,['query' => $_GET]);    //->select(); //todo 用户id 替换成正确的
        $page = $view_list->render();
        $this->assign('page', $page);

        $this->assign('view_list',$view_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-历史浏览记录');
        $this->assign('no_content_txt','暂无浏览记录');
        return $this->fetch('viewlist');
    }

    /**
     * 用户中心消息列表
     * @return mixed
     */
    public function message()
    {
        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/?error=no_login');
        }

        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        $type = in_array($type,[1,2,3]) ? $type : 1;

        $message_list = Db::table('message')->where('type',$type)->where('user_id',$user_id)->paginate(5,false,['query' => $_GET]);
        $page = $message_list->render();
        $this->assign('page', $page);

        $this->assign('message_list',$message_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-我的消息');
        $this->assign('no_content_txt','暂无消息');
        return $this->fetch('message');
    }

    public function productionlist()
    {
        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/?error=no_login');
        }

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
        $product_list = $query->where('a.user_id',$user_id)->paginate(4,false,['query' => $_GET]);
        $page = $product_list->render();
        $this->assign('page', $page);

        $this->assign('product_list',$product_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-我的作品集');
        $this->assign('no_content_txt','暂无作品');
        return $this->fetch('productionlist');
    }

    public function product_info()
    {
        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/?error=no_login');
        }

        $product_id = isset($_REQUEST['product_id']) ? intval($_REQUEST['product_id']) : 0;
        $product_info = Loader::model('Product')->find($product_id);
        if(empty($product_info)){
            return $this->redirect('/?error=no_product');
        }
        $this->assign('product_info',$product_info);

        $product_info->view_num += 1;
        $product_info->save();

        //我的其他作品
        $product_list = Db::table('product')->where('user_id',$user_id)->limit(3)->select();
        $this->assign('product_list',$product_list);

        $this->assign('page_title','用户中心-我的作品详情');
        return $this->fetch('productioninfo');
    }


    /**
     * 更改message状态
     */
    public function change_message_status()
    {
        $return_data = ['code' => 200,'msg' => '','data'=>['product_id'=>0]];
        try {
            $message_id = isset($_REQUEST['message_id']) ? $_REQUEST['message_id'] : '';
            if(empty($message_id)){
                throw new \Exception('参数错误');
            }
            $message_info = Loader::model('Message')->find($message_id);
            if(empty($message_info)){
                throw new \Exception('消息不存在');
            }

            if($message_info['status'] != 1){
                $message_info->status = 1;
                $message_info->save();
            }
        } catch (\Exception $e){
            $return_data['code']    = 500;
            $return_data['msg']     = $e->getMessage();
        }
        exit(json_encode($return_data));

    }



}
