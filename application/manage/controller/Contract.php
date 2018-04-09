<?php
namespace app\manage\controller;
use \think\Db;
use think\Loader;

//合同管理
class Contract extends \think\Controller
{

    //用户列表（筛选）
    //详情
    //禁用
    //添加
    //修改

    /**
     * 合同列表
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

        $contract_list = Loader::model('Contract')->where($where)->order('contract_id','desc')->paginate(10);
        $this->assign('contract_list', $contract_list);

        $page = $contract_list->render();
        $this->assign('page', $page);

        $this->view->engine->layout('layout');
        return $this->fetch('contract/index');
    }

}