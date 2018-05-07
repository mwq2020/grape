<?php
namespace app\manage\controller;
use \think\Db;
use think\Loader;

class Setting extends \think\Controller
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
    public function friend_url()
    {
        $where = [];
        if(!empty($_REQUEST['title'])){
            $where['title'] = ['like','%'.trim($_REQUEST['title']).'%'];
        }
        if(!empty($_REQUEST['cat_id'])){
            $where['cat_id'] = intval($_REQUEST['cat_id']);
        }

        $url_list = Loader::model('FriendUrl')->where($where)->order('id','desc')->paginate(10);
        $this->assign('url_list', $url_list);

        $page = $url_list->render();
        $this->assign('page', $page);

        $this->view->engine->layout('layout');
        return $this->fetch('setting/friend_url');
    }

}