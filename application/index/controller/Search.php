<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;

class Search extends \think\Controller
{
    public function index()
    {
        $keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';

        $hot_keyword_list = Loader::model('SearchKeyword')->order('search_num','desc')->limit(6)->select();
        $this->assign('hot_keyword_list',$hot_keyword_list);

        $query = Loader::model('Video')->where('status',1);
        if(!empty($keyword)){
            $query = $query->where('title|title','like','%'.$keyword.'%');

            //更新搜索关键字信息
            $keyword_info = Loader::model('SearchKeyword')->where('keyword',$keyword)->find();
            if($keyword_info){
                $keyword_info->search_num += 1;
                $keyword_info->update_time = time();
                $keyword_info->save();
            } else {
                Loader::model('SearchKeyword')->insert(['keyword'=>$keyword,'search_num'=>1,'add_time'=>time(),'update_time'=>time()]);
            }

        }
        //获取视频列表
        $search_list = $query->order('view_num','desc')->limit(12)->select();
        $this->assign('search_list',$search_list);

        //热门推荐
        $hot_recommand = Loader::model('Video')->where('status',1)->order('view_num','desc')->limit(5)->select();
        $this->assign('hot_recommand',$hot_recommand);

        $this->assign('page_title','视频搜索');
        return $this->fetch('search/index');
    }

}
