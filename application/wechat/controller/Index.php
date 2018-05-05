<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;

class Index extends \think\Controller
{
    public function index()
    {
        //首页banner数据获取
        $banner_list = Loader::model('Banner')->where('status',1)->order('banner_id','desc')->select();
        $this->assign('banner_list',$banner_list);

        //首页推荐展示
        $recommand_list = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')
            ->where(['a.status'=>1])
            ->field('a.*,b.cat_name')
            ->order('rand()')
            ->limit(4)
            ->select();
        $this->assign('recommand_list',$recommand_list);

        //首页精彩推荐
        $video_list= [];
        $video_list[1] = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where(['a.status'=>1,'a.cat_id'=>'1'])->field('a.*,b.cat_name')->order('rand()')->limit(4)->select();
        $video_list[2] = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where(['a.status'=>1,'a.cat_id'=>'2'])->field('a.*,b.cat_name')->order('rand()')->limit(4)->select();
        $video_list[3] = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where(['a.status'=>1,'a.cat_id'=>'3'])->field('a.*,b.cat_name')->order('rand()')->limit(4)->select();
        $video_list[4] = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where(['a.status'=>1,'a.cat_id'=>'4'])->field('a.*,b.cat_name')->order('rand()')->limit(4)->select();
        $video_list[5] = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where(['a.status'=>1,'a.cat_id'=>'4'])->field('a.*,b.cat_name')->order('rand()')->limit(4)->select();
        $video_list[6] = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where(['a.status'=>1,'a.cat_id'=>'6'])->field('a.*,b.cat_name')->order('rand()')->limit(4)->select();
        $video_list[7] = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where(['a.status'=>1,'a.cat_id'=>'7'])->field('a.*,b.cat_name')->order('rand()')->limit(4)->select();
        $this->assign('video_list',$video_list);

        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('index');
    }

    public function test()
    {
        $view_data = [];
        return $this->fetch('test',$view_data);
    }
}