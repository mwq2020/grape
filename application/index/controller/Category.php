<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;

class Category extends \think\Controller
{
    public function index()
    {
        $sort = isset($_REQUEST['sort']) ? intval($_REQUEST['sort']) : 1;
        $sort = in_array($sort,[1,2,3,4]) ? $sort : 1;
        $cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

        //查询语句构造
        $query = Loader::model('Video')->where('status',1);
        if(!empty($cat_id)){
            $query = $query->where('cat_id',$cat_id);
        }

        if($sort == 1){
            $query =$query->order('view_num desc,add_time desc');
        } elseif($sort == 2){
            $query =$query->order('add_time','desc');
        } elseif($sort == 3){
            $query =$query->order('like_num','desc');
        } elseif($sort == 4){
            $query =$query->order('view_num','desc');
        }

        $video_list = $query->limit(8)->select();
        $this->assign('video_list',$video_list);
        $this->assign('sort',$sort);
        $this->assign('cat_id',$cat_id);
//        echo "<pre>";
//        print_r($video_list);
//        exit;
        $this->assign('page_title','视频分类');
        return $this->fetch('category/index');
    }

}
