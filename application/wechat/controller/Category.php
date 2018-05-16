<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;
use think\Request;

class Category extends Base
{
    public function index()
    {
        $sort = isset($_REQUEST['sort']) ? intval($_REQUEST['sort']) : 1;
        $sort = in_array($sort,[1,2,3,4]) ? $sort : 1;
        $cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 1;
        $second_cat_id = isset($_REQUEST['second_cat_id']) ? intval($_REQUEST['second_cat_id']) : 0;
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        $second_cat_list = Loader::model('Category')->where('parent_id',$cat_id)->select();
        $this->assign('second_cat_list',$second_cat_list);

        //查询语句构造

        $query = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where('a.status',1)->field('a.*,b.cat_name');
        if(!empty($cat_id)){
            $query = $query->where('a.cat_id',$cat_id);
        }
        if(!empty($second_cat_id)){
            $query = $query->where('a.second_cat_id',$second_cat_id);
        }

        if($sort == 1){
            $query =$query->order('video_sn asc');
        } elseif($sort == 2){
            $query =$query->order('a.add_time','desc');
        } elseif($sort == 3){
            $query =$query->order('a.like_num','desc');
        } elseif($sort == 4){
            $query =$query->order('a.view_num','desc');
        }

        $video_list = $query->paginate(8,false,['query' => $_GET,'page'=>$page]);
        $this->assign('page', $page);
        $video_list = !empty($video_list) ? $video_list->toArray() : ['data'=>[]];

        $cat_list = Loader::model('Category')->getCategoryList();
        foreach($video_list['data'] as &$row){
            $row['cat_name'] = isset($cat_list[$row['second_cat_id']]) ? $cat_list[$row['second_cat_id']]['cat_name'] : '';
        }

        $this->assign('video_list',$video_list['data']);

        $this->assign('sort',$sort);
        $this->assign('cat_id',$cat_id);
        $this->assign('second_cat_id',$second_cat_id);
        $this->assign('page_title','艺术库');
        $this->assign('is_ajax',0);
        if (!Request::instance()->isAjax()){
            return $this->fetch('category/index');
        } else {
            $this->assign('is_ajax',1);
            echo json_encode(['code'=>200,'html'=>$this->fetch('category/index')]);
            exit;
        }
    }

    public function ajax_page()
    {
        $sort = isset($_REQUEST['sort']) ? intval($_REQUEST['sort']) : 1;
        $sort = in_array($sort,[1,2,3,4]) ? $sort : 1;
        $cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 1;
        $second_cat_id = isset($_REQUEST['second_cat_id']) ? intval($_REQUEST['second_cat_id']) : 0;
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

        //查询语句构造
        $query = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->field('a.*,b.cat_name');
        if(!empty($cat_id)){
            $query = $query->where('a.cat_id',$cat_id);
        }
        if(!empty($second_cat_id)){
            $query = $query->where('a.second_cat_id',$second_cat_id);
        }

        if($sort == 1){
            $query =$query->order('a.view_num desc,a.add_time desc');
        } elseif($sort == 2){
            $query =$query->order('a.add_time','desc');
        } elseif($sort == 3){
            $query =$query->order('a.like_num','desc');
        } elseif($sort == 4){
            $query =$query->order('a.view_num','desc');
        }

        $video_list = $query->paginate(8,false,['query' => $_GET,'page'=>$page]);
        $this->assign('video_list',$video_list);
        echo json_encode(['code'=>200,'html'=>$this->fetch('category/ajax_page')]);
        exit;
    }

}