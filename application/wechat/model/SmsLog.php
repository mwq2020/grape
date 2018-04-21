<?php

namespace app\wechat\model;

use think\Model;
use Aliyun\DySDKLite\SignatureHelper;

class SmsLog extends Model
{
    protected $pk = 'sms_id';
    protected $table = 'sms_log';

    /**
     * 短信验证码发送
     * @param $mobile
     * @param $content
     */
    public function sendValidSms($mobile,$data,$template_code,$signature="紫葡萄艺术库")
    {
        $params = array ();

        // *** 需用户填写部分 ***
        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        //$accessKeyId = "LTAIznq4Y0K";
        //$accessKeySecret = "7GwqOBZN0p9d777wyVdlmJ7OV";

        $accessKeyId = config('sms.key');
        $accessKeySecret = config('sms.secret');

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $mobile;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $signature;

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $template_code;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项

//        $params['TemplateParam'] = Array (
//            "code" => "123456",
//            "product" => "阿里通信"
//        );

        $params['TemplateParam'] = $data;

        // fixme 可选: 设置发送短信流水号
        //$params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );

        if($content && $content->Code == "OK"){
            return true;
        }
        return false;
    }


}
