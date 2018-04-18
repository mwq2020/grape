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
        if($activity_info['end_time'] <= time()){
            $this->assign('is_end',1);
        } else {
            $this->assign('is_end',0);
        }

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
        $this->assign('activity_info',$activity_info);
        $this->assign('page_title','活动规则');
        return $this->fetch('rule');
    }

    /**
     * 活动结果页面
     */
    public function res()
    {
        $query = Db::table('product')->alias('a')->join('activity b','a.activity_id=b.activity_id')->join('user c','a.user_id=c.user_id');
        $product_list = $query->select();

        $this->assign('product_list',$product_list);
        $this->assign('page_title','活动结果');
        return $this->fetch('res');
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
                throw new Exception('上传文件为空');
            }

            $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/product');
            if($info){
                $return_data['url'] =  '/static/image/product/'.$info->getSaveName();
            }else{
                throw new \Exception('图片保存失败【'.$file->getError().'】');
            }

        } catch (Exception $e){
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
     * 获活动报名
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
                $flag = Loader::model('User')->insert($user_data);
                if(empty($flag)){
                    throw new \Exception('用户生成失败');
                }
                $user_id = Loader::model('User')->getLastInsID();
            } else {
                $user_id = $user_info['user_id'];
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

}
