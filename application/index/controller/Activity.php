<?php
namespace app\index\controller;
use \think\Db;
use think\Exception;
use think\Loader;

class Activity extends \think\Controller
{

    /**
     * 活动列表页面
     * @return mixed
     */
    public function index()
    {
        $activity_list = Loader::model('Activity')->where('status',1)->order('activity_id','desc')->limit(12)->select();
        if($activity_list){
            $activity_list = collection($activity_list)->toArray();
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

//        echo "<pre>";
//        print_r($activity_list);
//        exit;

        $this->assign('activity_list',$activity_list);
        $this->assign('page_title','活动列表');
        return $this->fetch('activity/index');
    }


    /**
     * 活动详情页面
     * @return mixed
     */
    public function info()
    {
//        echo "<pre>";
//        print_r($_REQUEST);
//        print_r(session('reader_no'));
//        exit;
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

        //获奖作品展示
        $query = Db::table('product')->alias('a')->join('user c','a.user_id=c.user_id');
        $map = [];
        $map['a.activity_id'] = $activity_id;
        $award_list = $query->where($map)->where('a.award_grade','>',0)->select();
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


        //活动状态
        $activity_status = 'starting';
        if($activity_info['end_time'] <= time()){
            $activity_status = 'ending';
        } elseif($activity_info['start_time'] >= time()) {
            $activity_status = 'not_beginning';
        }
        $this->assign('activity_status',$activity_status);

        $query = Db::table('product')->alias('a')->join('activity b','a.activity_id=b.activity_id')->join('user c','a.user_id=c.user_id');
        $product_list = $query->select();

        $this->assign('product_list',$product_list);
        $this->assign('page_title','活动作品列表');
        return $this->fetch('product_list');
    }


    /**
     * 上传视频文件
     */
    public function upload_file()
    {
        $return_data = ['code' => 200,'msg' => '','data'=>[]];
        try {
            $file = request()->file('product_img');
            if(empty($file)){
                throw new \Exception('上传文件为空');
            }

            $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/product');
            if($info){
                $return_data['url'] =  '/static/image/product/'.$info->getSaveName();
            }else{
                throw new \Exception('图片保存失败【'.$file->getError().'】');
            }

        } catch (\Exception $e){
            $return_data['code']    = 500;
            $return_data['msg']     = $e->getMessage();
        }

        exit(json_encode($return_data));
    }


    /**
     * 短信发送
     */
    public function send_sms()
    {
        $return_data = ['code' => 200,'msg' => '','data'=>['valid_id'=>0]];
        try {
            $mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : '';
            if(empty($mobile)){
                throw new \Exception('手机号不能为空');
            }
            if(strlen($mobile) != 11){
                throw new \Exception('手机号格式错误');
            }
            $code = rand(100000,999999);
            $param_data = ['code' => $code];

            //todo  上线需要解开这个 否则报名短信发布出去
            //$flag = Loader::model('SmsLog')->sendValidSms($mobile,$param_data,'SMS_132400562');
            $flag = true;
            if(empty($flag)){
                throw new \Exception('短信发送失败，请重试');
            }

            $sms_content = "验证码{$code},有效期10分钟";
            $sms_data = [];
            $sms_data['user_id'] = 0;
            $sms_data['mobile'] = $mobile;
            $sms_data['content'] = $sms_content;
            $sms_data['code'] = $code;
            $sms_data['status'] = 1;
            $sms_data['add_time'] = time();
            $flag = Loader::model('SmsLog')->insert($sms_data);
            if(empty($flag)){
                throw new \Exception('短信入库失败，请重试');
            }
            $return_data['data']['valid_id'] = Loader::model('SmsLog')->getLastInsID();
        } catch (\Exception $e){
            $return_data['code']    = 500;
            $return_data['msg']     = $e->getMessage();
        }
        exit(json_encode($return_data));
    }

    /**
     * 活动报名
     */
    public function signup()
    {
        $return_data = ['code' => 200,'msg' => '','data'=>['valid_id'=>0]];
        try {
            $mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : '';
            $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
            $valid_id = isset($_REQUEST['valid_id']) ? $_REQUEST['valid_id'] : '';
            $activity_id = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '';
            if(empty($mobile)){
                throw new \Exception('手机号不能为空');
            }
            if(strlen($mobile) != 11){
                throw new \Exception('手机号格式错误');
            }
            if(empty($code)){
                throw new \Exception('验证码不能为空');
            }
            if(empty($valid_id)){
                throw new \Exception('参数错误，请重试');
            }
            if(empty($activity_id)){
                throw new \Exception('参数2错误，请重试');
            }

            $sms_info = Loader::model('SmsLog')->find($valid_id);
            if(empty($sms_info)){
                throw new \Exception('验证码错误，请重试');
            }
            if($sms_info['valid_status']== 1){
                throw new \Exception('验证码已过期');
            }
            if($sms_info['code'] != $code){
                throw new \Exception('验证码错误');
            }

            $user_info = Loader::model('User')->where(['telphone'=>$mobile])->find();
            if(empty($user_info)){
                $user_data = [];
                $user_data['telphone'] = $mobile;
                $user_data['register_time'] = time();
                $user_data['real_name'] = $mobile;
                $flag = Loader::model('User')->insert($user_data);
                if(empty($flag)){
                    throw new \Exception('用户生成失败');
                }
                $user_id = Loader::model('User')->getLastInsID();
                $user_info = Loader::model('User')->find($user_id);
                session('user_id',$user_id);
                session('reader_no','');
                session('real_name',$mobile);
                session('avatar','/static/image/user/default_user.jpg');
                session('user_info',$user_info);
            } else {
                $user_id = $user_info['user_id'];
                session('user_id',$user_id);
                session('reader_no','');
                session('real_name',$mobile);
                session('avatar','/static/image/user/default_user.jpg');
                session('user_info',$user_info);
            }

            $signup_data = [];
            $signup_data['user_id']     = $user_id;
            $signup_data['activity_id'] = $activity_id;
            $signup_data['mobile']      = $mobile;
            $signup_data['add_time']    = time();
            $flag = Loader::model('ActivitySignup')->insert($signup_data);
            if(empty($flag)){
                throw new \Exception('报名信息入库失败，请重试');
            }

            //验证码设置为已验证
            $sms_info->valid_status = 1;
            $sms_info->save();

        } catch (\Exception $e){
            $return_data['code']    = 500;
            $return_data['msg']     = $e->getMessage();
        }
        exit(json_encode($return_data));
    }


    /**
     * 活动报名
     */
    public function readno_signup()
    {
        $return_data = ['code' => 200,'msg' => '','data'=>[]];
        try {
            $mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : '';
            $activity_id = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '';
            if(empty($mobile)){
                throw new \Exception('手机号不能为空');
            }
            if(strlen($mobile) != 11){
                throw new \Exception('手机号格式错误');
            }
            if(empty($activity_id)){
                throw new \Exception('参数2错误，请重试');
            }

            $user_id = session('user_id');
            if(empty($user_id)){
                throw new \Exception('登录状态退出，请重新登录');
            }
            $user_info = Loader::model('User')->find($user_id);
            if(empty($user_info)){
                throw new \Exception('用户异常请退出重新登录');
            }

            $signup_data = [];
            $signup_data['user_id']     = $user_id;
            $signup_data['activity_id'] = $activity_id;
            $signup_data['mobile']      = $mobile;
            $signup_data['add_time']    = time();
            $flag = Loader::model('ActivitySignup')->insert($signup_data);
            if(empty($flag)){
                throw new \Exception('报名信息入库失败，请重试');
            }
        } catch (\Exception $e){
            $return_data['code']    = 500;
            $return_data['msg']     = $e->getMessage();
        }
        exit(json_encode($return_data));
    }


    /**
     * 作品上传保存
     */
    public function upload_product()
    {
        $return_data = ['code' => 200,'msg' => '','data'=>['product_id'=>0]];
        try {
            $title = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';
            $product_desc = isset($_REQUEST['product_desc']) ? $_REQUEST['product_desc'] : '';
            $activity_id = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '';
            $product_img = isset($_REQUEST['product_img']) ? $_REQUEST['product_img'] : '';
            if(empty($title)){
                throw new \Exception('作品标题不能为空');
            }
            if(empty($product_desc)){
                throw new \Exception('作品描述不能为空');
            }
            if(empty($activity_id)){
                throw new \Exception('参数错误');
            }

            $product_data = [];
            $product_data['user_id'] = session('user_id');
            $product_data['activity_id'] = $activity_id;
            $product_data['product_name'] = $title;
            $product_data['product_desc'] = $product_desc;
            $product_data['product_img'] = $product_img;
            $product_data['type'] = 1;
            $product_data['add_time'] = time();
            $flag = Loader::model('Product')->insert($product_data);
            if(empty($flag)){
                throw new \Exception('作品入库失败，请重试');
            }
            $return_data['data']['product_id'] = Loader::model('Product')->getLastInsID();
        } catch (\Exception $e){
            $return_data['code']    = 500;
            $return_data['msg']     = $e->getMessage();
        }
        exit(json_encode($return_data));
    }





}
