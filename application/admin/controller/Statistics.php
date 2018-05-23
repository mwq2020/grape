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

}