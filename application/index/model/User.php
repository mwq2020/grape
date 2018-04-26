<?php

namespace app\index\model;

use think\Model;

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
            var_dump($ret);
            exit;
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

}
