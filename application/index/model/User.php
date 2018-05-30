<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Loader;

class User extends Model
{
    protected $pk = 'user_id';
    protected $table = 'user';

    /**
     * 用户登录
     * @param $UserName
     * @param $UserPin
     * @param string $AutherizeKey
     */
    public function Login($UserName,$UserPin,$AutherizeKey='')
    {
        try {
            $wsdl = 'http://58.60.1.135:8085/service.asmx?WSDL';
            $client = new \SoapClient($wsdl);

            //$params = ['strUserName'=>'080812300021','strUserPin'=>'751008000','strAutherizeKey'=>''];
            $params = ['strUserName'=>$UserName,'strUserPin'=>$UserPin,'strAutherizeKey'=>$AutherizeKey];
            $ret = $client->Login($params);
            if(empty($ret)){
                return false;
            }
            $res = simplexml_load_string($ret->LoginResult);
            if($res->Return == 1){
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * 通过客户ip来判断是否免登陆【自动登录】
     * @param $ip
     * @return bool
     */
    public function customerFreeLogin($ip)
    {
        $user_id = session('user_id');
        if(!empty($user_id) || empty($ip)) {
            return false;
        }

        $ip2lang = ip2long($ip);
        $where = [];
        $where['start_ip'] = ['<=',$ip2lang];
        $where['end_ip'] = ['>=',$ip2lang];
        $where['is_free_login'] = 1;
        $where['status'] = 1;
        $ip_info = Db::table('customer_ip_list')->where($where)->find();
        if(empty($ip_info)){
            return false;
        }
        $customer_info = Db::table('customer')->find($ip_info['customer_id']);
        if(empty($customer_info)){
            return false;
        }
        $user_info = Db::table('user')->where(['reader_no'=>$customer_info['account_no'],'customer_id'=>$customer_info['customer_id']])->find();
        if(empty($user_info)){
            return false;
        }

        session('user_id',$user_info['user_id']);
        session('reader_no',$user_info['reader_no']);
        if(!empty($user_info['reader_no'])){
            session('real_name',$user_info['reader_no']);
        } else {
            session('real_name',$user_info['telphone']);
        }
        session('avatar','/static/image/user/default_user.jpg');
        session('user_info',$user_info);
        return true;
    }

}
