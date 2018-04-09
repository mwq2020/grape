<?php
namespace app\manage\controller;
use \think\Db;
use think\Loader;

class Customer extends \think\Controller
{

    //用户列表（筛选）
    //详情
    //禁用
    //添加
    //修改


    /**
     * 用户列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if(!empty($_REQUEST['title'])){
            $where['title'] = ['like','%'.trim($_REQUEST['title']).'%'];
        }
        if(!empty($_REQUEST['cat_id'])){
            $where['cat_id'] = intval($_REQUEST['cat_id']);
        }

        $customer_list = Loader::model('Customer')->where($where)->order('customer_id','desc')->paginate(10);
        $this->assign('customer_list', $customer_list);

        $page = $customer_list->render();
        $this->assign('page', $page);

        $this->view->engine->layout('layout');
        return $this->fetch('customer/index');
    }

}