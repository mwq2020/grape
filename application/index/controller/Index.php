<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;

class Index extends \think\Controller
{
    public function index()
    {
        //首页banner数据获取
        $banner_list = Loader::model('Banner')->where('status',1)->order('banner_id','desc')->select();
        $this->assign('banner_list',$banner_list);

        //首页视频展示获取
        $video_list = [];
        $video_list[1] = Loader::model('Video')->where(['status'=>1,'position'=>1])->order('video_id','desc')->find();
        $video_list[2] = Loader::model('Video')->where(['status'=>1,'position'=>2])->order('video_id','desc')->find();
        $video_list[3] = Loader::model('Video')->where(['status'=>1,'position'=>3])->order('video_id','desc')->find();
        $video_list[4] = Loader::model('Video')->where(['status'=>1,'position'=>4])->order('video_id','desc')->find();
        $this->assign('video_list',$video_list);

        //首页右侧推荐展示
        $recommand_list = Loader::model('Video')->where('status',1)->order('view_num','desc')->limit(4)->select();
        $this->assign('recommand_list',$recommand_list);

//        echo "<pre>";
//        print_r($video_list);
//        exit;
        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('index');
    }

    public function test() 
    {
        $view_data = [];
       return $this->fetch('test',$view_data); 
    }
}

/*
THINK_PATH 框架系统目录
ROOT_PATH 框架应用根目录
APP_PATH 应用目录（默认为application）
CONF_PATH 配置目录（默认为APP_PATH）
LIB_PATH 系统类库目录（默认为 THINK_PATH.'library/'）
CORE_PATH 系统核心类库目录 （默认为 LIB_PATH.'think/'）
TRAIT_PATH 系统trait目录（默认为 LIB_PATH.'traits/'）
EXTEND_PATH 扩展类库目录（默认为 ROOT_PATH . 'extend/')
VENDOR_PATH 第三方类库目录（默认为 ROOT_PATH . 'vendor/'）
RUNTIME_PATH 应用运行时目录（默认为 ROOT_PATH.'runtime/'）
LOG_PATH 应用日志目录 （默认为 RUNTIME_PATH.'log/'）
CACHE_PATH 项目模板缓存目录（默认为 RUNTIME_PATH.'cache/'）
TEMP_PATH 应用缓存目录（默认为 RUNTIME_PATH.'temp/'）
*/
