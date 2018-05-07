<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;
use think\Session;

class User extends Base
{
    public function index()
    {
        $customer_list = Db::table('customer')->select();
        $this->assign('customer_list',$customer_list);

        $user_id = session('user_id');
        if(empty($user_id)){
            $this->assign('page_title','紫葡萄少儿艺术库');
            return $this->fetch('no_login_user_index');
        }

        $statistics_data = [];
        $statistics_data['view_num'] = Db::table('user_view_list')->where(['user_id'=>$user_id])->count();
        $statistics_data['collect_num'] = Db::table('user_collect_list')->where(['user_id'=>$user_id])->count();
        $statistics_data['product_num'] = Db::table('product')->where(['user_id'=>$user_id])->count();
        $statistics_data['message_num'] = Db::table('message')->where(['user_id'=>$user_id])->count();
        $this->assign('statistics_data',$statistics_data);

        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('index');
    }

    /**
     * 用户登录
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

            $user_info = Loader::model('User')->where(['reader_no'=>$reader_no,'customer_id'=>$customer_id])->find();
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
     * 用户退出登录
     */
    public function logout()
    {
        Session::clear();
        $this->redirect('/wechat/index/index');
    }

    /**
     * 用户的观看视频记录
     * @return mixed|void
     */
    public function view_list()
    {
        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/wechat/index/index?error=no_login');
        }
        $query = Db::table('user_view_list')->alias('a')->join('video b','a.video_id=b.video_id')->join('category c','b.second_cat_id=c.cat_id')->field('a.add_time,b.*,c.cat_name');
        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 1;
        $type = in_array($type,[1,2,3]) ? $type : 1;
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
        $view_list = $query->where('a.user_id',$user_id)->paginate(8,false,['query' => $_GET]);    //->select(); //todo 用户id 替换成正确的

        $this->assign('view_list',$view_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-历史浏览记录');
        return $this->fetch('view_list');
    }

    /**
     * 用户的消息列表
     * @return mixed|void
     */
    public function message_list()
    {

        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/wechat/index/index?error=no_login');
        }

        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        //$type = in_array($type,[1,2,3]) ? $type : 1;

        $message_list = Db::table('message')->where('user_id',$user_id)->paginate(5,false,['query' => $_GET]);
        $page = $message_list->render();
        $this->assign('page', $page);
//        echo "<pre>";
//        print_r($message_list);
//        exit;
        $this->assign('message_list',$message_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-我的消息');
        return $this->fetch('message_list');
    }

    /**
     * 用户的作品列表
     * @return mixed|void
     */
    public function product_list()
    {
        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/wechat/index/index?error=no_login');
        }

        $query = Db::table('product')->alias('a')->join('activity b','a.activity_id=b.activity_id');
        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 1;
        $type = in_array($type,[1,2,3]) ? $type : 1;
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
        $product_list = $query->where('a.user_id',$user_id)->paginate(8,false,['query' => $_GET]);
        $this->assign('product_list',$product_list);
        $this->assign('type',$type);
        $this->assign('page_title','用户中心-我的作品集');
        return $this->fetch('product_list');
    }

    /**
     * 用户的收藏列表
     * @return mixed
     */
    public function collect_list()
    {
        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/wechat/index/index?error=no_login');
        }
        $query = Db::table('user_collect_list')->alias('a')->join('video b','a.video_id=b.video_id')->join('category c','b.second_cat_id=c.cat_id')->field('a.collect_id,b.*,c.cat_name');
        $collect_list = $query->where('a.user_id',$user_id)->order('a.add_time','desc')->paginate(8,false,['query' => $_GET]);    //->select(); //todo 用户id 替换成正确的
        $this->assign('collect_list',$collect_list);

        $this->assign('page_title','紫葡萄少儿艺术库-我的收藏');
        return $this->fetch('collect_list');
    }


    public function product_info()
    {
        $user_id = session('user_id');
        if(empty($user_id)){
            return $this->redirect('/wechat/index/index?error=no_login');
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
        return $this->fetch('product_info');//视频作品的详情页面
        return $this->fetch('product_info_audio');//音频作品的详情页面
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