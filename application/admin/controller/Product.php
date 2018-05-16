<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

//作品管理
class Product extends Base
{

    /**
     * 作品列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if(!empty($_REQUEST['product_name'])){
            $where['a.product_name'] = ['like','%'.trim($_REQUEST['product_name']).'%'];
        }
        if(!empty($_REQUEST['activity_id'])){
            $where['a.activity_id'] = intval($_REQUEST['activity_id']);
        }
        if(!empty($_REQUEST['user_name'])){
            $where['b.reader_no'] = ['like','%'.trim($_REQUEST['user_name']).'%'];
        }
        if(!empty($_REQUEST['type'])){
            $where['a.type'] = intval($_REQUEST['type']);
        }

        $product_list = Db::table('product')->alias('a')
            ->join('user b','a.user_id=b.user_id')
            ->join('activity c','a.activity_id=c.activity_id')
            ->where($where)->order('a.product_id','desc')
            ->field('a.*,b.real_name,b.reader_no,c.activity_name')
            ->paginate(10,false,['query' => $_GET]);
        $this->assign('product_list', $product_list);

        $activity_list = Db::table('activity')->select();
        $this->assign('activity_list', $activity_list);

        $this->view->engine->layout('layout');
        return $this->fetch('product/index');
    }

    //设置作品的等级
    public function set_award_grade()
    {
        $return_data = ['flag'=>false];
        try {
            $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 0;
            $product_info = Loader::model('Product')->find($product_id);
            $product_info->award_grade = intval($_REQUEST['award_grade']);
            $product_info->save();
            $return_data['flag'] = true;
            $this->ajax_return($return_data,200);
        } catch (\Exception $e) {
            $this->ajax_return($return_data,500);
        }
    }

}