<?php
namespace app\index\controller;
use think\Db;
use think\Loader;
use Endroid\QrCode\QrCode;
use think\Request;

class Video extends Base
{
    /**
     * 视频详情
     * @return mixed
     */
    public function info()
    {
        if(empty($user_id)) {
            $ip = Request::instance()->ip();
            Loader::model('User')->customerFreeLogin($ip);
        }

        //获取当前视频的详情
        $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
        $video_info = Loader::model('Video')->find($video_id);
        $this->assign('video_info',$video_info);
        //参数错误或者视频不存在就跳转到首页
        if(empty($video_id) || empty($video_info)){
            return $this->redirect('/index/index/index?from=no_video');
        }

        //右侧视频推荐
        $recommand_list = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')->where('a.status',1)->field('a.*,b.cat_name')->limit(5)->select();
        $this->assign('recommand_list',$recommand_list);
        $this->assign('page_title','视频详情');

        if($video_info){
            $video_info->view_num += 1;
            $video_info->save();
        }

        //记录视频到浏览视频列表
        $user_id = session('user_id');
        if($user_id && $video_id){
            $view_info = Db::table('user_view_list')->where(['video_id'=>$video_id,'user_id'=>$user_id])->find();
            if(empty($view_info)){
                $insert_data = [];
                $insert_data['video_id'] = $video_id;
                $insert_data['user_id'] = $user_id;
                $insert_data['add_time'] = time();
                $insert_data['update_time'] = time();
                Db::table('user_view_list')->insert($insert_data);
            }
        }


        $cat_list = Loader::model('Category')->getCategoryList();
        $current_location = $cat_list[$video_info->cat_id]['cat_name'] ." - ".$cat_list[$video_info->second_cat_id]['cat_name'];
        $this->assign('current_location',$current_location);

        return $this->fetch('video/info');
    }


    /**
     * 视频详情
     * @return mixed
     */
    public function video_info()
    {

        if(empty($user_id)) {
            $ip = Request::instance()->ip();
            //$ip = '27.151.120.90';
            Loader::model('User')->customerFreeLogin($ip);
        }

        //获取当前视频的详情
        $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
        $video_info = Loader::model('Video')->find($video_id);
        $this->assign('video_info',$video_info);

        //右侧视频推荐
        $recommand_list = Db::table('video')->alias('a')->join('category b','a.second_cat_id=b.cat_id')
            ->where(['a.status'=>1,'a.second_cat_id'=>$video_info['second_cat_id']])
            ->field('a.*,b.cat_name')
            ->order('rand()')
            ->limit(5)
            ->select();
        $this->assign('recommand_list',$recommand_list);
        $this->assign('page_title','视频详情');

        //当前视频播放次数
        if($video_info){
            $video_info->view_num += 1;
            $video_info->save();
        }

        //统计次数
        $page_view_num_info = Db::table('statistics')->where(['statistics_key'=>'video_play_num'])->find();
        if(!empty($page_view_num_info)) {
            $data = [];
            $data['id']                 = $page_view_num_info['id'];
            $data['statistics_value']   = $page_view_num_info['statistics_value']+1;
            $data['update_time']        = time();
            Db::table('statistics')->update($data);
        } else {
            $data = [];
            $data['statistics_key']     = 'video_play_num';
            $data['statistics_value']   = 1;
            $data['add_time']           = time();
            $data['update_time']        = time();
            Db::table('statistics')->insert($data);
        }


        //记录视频到浏览视频列表
        $user_id = session('user_id');
        if($user_id && $video_id){
            $view_info = Db::table('user_view_list')->where(['video_id'=>$video_id,'user_id'=>$user_id])->find();
            if(empty($view_info)){
                $insert_data = [];
                $insert_data['video_id'] = $video_id;
                $insert_data['user_id'] = $user_id;
                $insert_data['add_time'] = time();
                $insert_data['update_time'] = time();
                Db::table('user_view_list')->insert($insert_data);
            }
        }
        $cat_list = Loader::model('Category')->getCategoryList();
        $current_location = $cat_list[$video_info->cat_id]['cat_name'] ."-".$cat_list[$video_info->second_cat_id]['cat_name'];
        $this->assign('current_location',$current_location);

        return $this->fetch('video/video_info');
    }


    /**
     * 视频添加喜欢
     */
    public function add_like()
    {
        $return_data = ['code'=>200,'data'=>['status' => 1],'msg'=>''];
        try {
            $user_id = session('user_id');
            if(empty($user_id)){
                $return_data['code'] = 400;
                exit(json_encode($return_data));
                //throw new \Exception('您还没登录，请登录完再过来重试！');
            }
            $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
            if(empty($video_id)){
                throw new \Exception('入参错误');
            }
            $video_info = Loader::model('Video')->find($video_id);
            if(empty($video_info)){
                throw new \Exception('视频不存在');
            }

            $video_info->like_num += 1;
            $video_info->save();



        } catch (\Exception $e) {
            $return_data['code'] = 500;
            $return_data['msg'] = $e->getMessage();
        }
        exit(json_encode($return_data));
    }

    public function make_qrcode()
    {
        $qrCode=new QrCode();
        $url = 'http://www.zptys.net/wechat/video/index?video_id='.intval($_REQUEST['video_id']);//加http://这样扫码可以直接跳转url
        $qrCode->setText($url)
            ->setSize(300)//大小
            ->setLabelFontPath(VENDOR_PATH.'endroid/qrcode/assets/noto_sans.otf')
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabelFontSize(16);
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit;
    }

    public function test_ip()
    {
        $ip = Request::instance()->ip();
        echo "您当前的ip为:[".$ip."]";
    }


}