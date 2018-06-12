<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

//统计管理
class Statistics extends Base
{

    public function index()
    {
        $customer_list = Loader::model('Customer')->getCustomerSelectList();
        $this->assign('customer_list', $customer_list);

        $this->view->engine->layout('layout');
        return $this->fetch('statistics/index');
    }


    public function report_data()
    {
        $return_data = [
            'labels'=>[1,2,3,4,5,6,7,8,9,10],
            'data'=>[
                [
                    'data'=>[2,3,3,9,1,3,5,8,3,2],
                    'markPoint' => ['data'=> null],
                    'name'=>'独立ip',
                    'type'=>'line'
                ],
                [
                'data'=>[10,28,30,77,10,30,50,70,15,8],
                'markPoint' => ['data'=> null],
                'name'=>'访问量',
                'type'=>'line'
            ]
            ]
        ];

        exit(json_encode($return_data));
    }

}