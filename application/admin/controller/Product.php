<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

//作品管理
class Product extends Base
{

    //用户列表（筛选）
    //详情
    //禁用
    //添加
    //修改

    /**
     * 作品列表
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

        //$product_list = Loader::model('Product')->where($where)->order('product_id','desc')->paginate(10);
        //$this->assign('product_list', $product_list);


        $product_list = Db::table('product')->alias('a')
            ->join('user b','a.user_id=b.user_id')
            ->join('activity c','a.activity_id=c.activity_id')
            ->where($where)->order('a.product_id','desc')
            ->field('a.*,b.real_name,b.reader_no,c.activity_name')
            ->paginate(10,false,['query' => $_GET]);
        $this->assign('product_list', $product_list);


        $this->view->engine->layout('layout');
        return $this->fetch('product/index');
    }

}