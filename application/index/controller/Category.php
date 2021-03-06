<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;

class Category extends Base
{
    /**
     * 视频列表
     * @return mixed
     */
    public function index()
    {
        $sort = isset($_REQUEST['sort']) ? intval($_REQUEST['sort']) : 1;
        $sort = in_array($sort,[1,2,3,4]) ? $sort : 1;
        $cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
        $second_cat_id = isset($_REQUEST['second_cat_id']) ? intval($_REQUEST['second_cat_id']) : 0;

        $second_cat_list = Loader::model('Category')->where('parent_id',$cat_id)->select();
        $this->assign('second_cat_list',$second_cat_list);

        //查询语句构造
        $query = Loader::model('Video')->where('status',1);
        if(!empty($cat_id)){
            $query = $query->where('cat_id',$cat_id);
        }
        if(!empty($second_cat_id)){
            $query = $query->where('second_cat_id',$second_cat_id);
        }

        if($sort == 1){
            $query =$query->order('video_sn asc');
        } elseif($sort == 2){
            $query =$query->order('add_time','desc');
        } elseif($sort == 3){
            $query =$query->order('like_num','desc');
        } elseif($sort == 4){
            $query =$query->order('view_num','desc');
        }

        $video_list = $query->paginate(6,false,['query' => $_GET]);
        $page = $video_list->render();
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

        $this->assign('page_title','视频分类');
        $this->assign('no_content_txt','暂无内容');
        return $this->fetch('category/index');
    }

}
