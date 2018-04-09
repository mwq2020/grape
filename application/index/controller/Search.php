<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;
use think\Cookie;

class Search extends \think\Controller
{
    public function index()
    {
        $keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
        $cat_id = isset($_REQUEST['cat_id']) ? trim($_REQUEST['cat_id']) : 0;
        $last_keyword = Cookie::get('keyword');

        $hot_keyword_list = Loader::model('SearchKeyword')->order('search_num','desc')->limit(6)->select();
        $this->assign('hot_keyword_list',$hot_keyword_list);

        $query = Loader::model('Video')->where('status',1);
        if(!empty($keyword)){ /* 客户输入关键字搜索页面 */
            Cookie::set('keyword',$keyword,30*24*3600);

            if($cat_id){
                $query = $query->where('cat_id',$cat_id);
            }
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

            //获取视频列表
            $search_list = $query->order('view_num','desc')->limit(12)->select();
            $this->assign('search_list',$search_list);

            //搜索结果统计
            $search_video_num = 0;
            $cat_list = Db::table('category')->where('parent_id',0)->select();
            $video_count_res = Db::table('video')
                ->where('status',1)
                ->where('title|title','like','%'.$keyword.'%')
                ->field('cat_id,count(video_id) as cnt')
                ->group('cat_id')
                ->select();
            $statistics_list = [];
            if(!empty($video_count_res)){
                foreach($video_count_res as $row){
                    $statistics_list[$row['cat_id']] = $row['cnt'];
                }
            }
            foreach($cat_list as &$cat){
                $cat['count_num'] = 0;
                if(isset($statistics_list[$cat['cat_id']])){
                    $cat['count_num'] = $statistics_list[$cat['cat_id']];
                    $search_video_num += $statistics_list[$cat['cat_id']];
                }
            }
            $this->assign('cat_list',$cat_list);
            $this->assign('search_video_num',$search_video_num);
            $this->assign('cat_id',$cat_id);

            /* 搜索无结果是跳转到无搜索结果页面 */
            if(empty($search_list)){
                return $this->fetch('search/no_result');
            }

            //热门推荐
            $hot_recommand = Loader::model('Video')->where('status',1)->order('view_num','desc')->limit(5)->select();
            $this->assign('hot_recommand',$hot_recommand);

            $this->assign('page_title','视频搜索');
            return $this->fetch('search/search_list');


        } else { /* 初始进搜索页面显示 */

            if(!empty($last_keyword)){ /* 有上次搜索结果页面 */
                $query = $query->where('title|title','like','%'.$last_keyword.'%');
                //获取视频列表
                $search_list = $query->order('view_num','desc')->limit(10)->select();
                $this->assign('search_list',$search_list);

                //热门推荐
                $hot_recommand = Loader::model('Video')->where('status',1)->order('view_num','desc')->limit(5)->select();
                $this->assign('hot_recommand',$hot_recommand);
                $this->assign('page_title','视频搜索');
                return $this->fetch('search/index');
            } else { /* 无上次搜索页面 */

                //热门推荐
                $hot_recommand = Loader::model('Video')->where('status',1)->order('view_num','desc')->limit(10)->select();
                $this->assign('hot_recommand',$hot_recommand);

                $this->assign('page_title','视频搜索');
                return $this->fetch('search/no_search_history');
            }
        }
    }

}
