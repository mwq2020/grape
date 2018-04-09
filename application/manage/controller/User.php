<?php
namespace app\manage\controller;
use \think\Db;
use think\Loader;

class User extends \think\Controller
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

        $user_list = Loader::model('User')->where($where)->order('user_id','desc')->paginate(10);
        $this->assign('user_list', $user_list);

        $page = $user_list->render();
        $this->assign('page', $page);

        $this->view->engine->layout('layout');
        return $this->fetch('user/index');
    }

}