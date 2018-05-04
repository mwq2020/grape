<?php
namespace app\index\controller;
use \think\Db;
use think\Exception;
use think\Loader;

class Base extends \think\Controller
{

    protected $beforeActionList = [
        'all_run'
    ];


    public function all_run()
    {
        $customer_list = Db::table('customer')->select();
        $this->assign('customer_list',$customer_list);
    }

}