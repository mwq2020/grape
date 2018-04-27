<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;
use Endroid\QrCode\QrCode;

class Video extends \think\Controller
{
    /**
     * 视频详情
     * @return mixed
     */
    public function info()
    {
        //获取当前视频的详情
        $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
        $video_info = Loader::model('Video')->find($video_id);
        $this->assign('video_info',$video_info);

        //参数错误或者视频不存在就跳转到首页
        if(empty($video_id) || empty($video_info)){
            return $this->redirect('/index/index/index?from=no_video');
        }

        //右侧视频推荐
        $recommand_list = Loader::model('Video')->limit(5)->select();
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
                $insert_data['date_time'] = strtotime(date('Y-m-d'));
                $insert_data['add_time'] = time();
                $insert_data['update_time'] = time();
                Db::table('user_view_list')->insert($insert_data);
            }
        }


        $cat_list = Loader::model('Category')->getCategoryList();
        $current_location = $cat_list[$video_info->cat_id]['cat_name'] ."-".$cat_list[$video_info->second_cat_id]['cat_name'];
        $this->assign('current_location',$current_location);

        return $this->fetch('video/info');
    }


    /**
     * 视频详情
     * @return mixed
     */
    public function video_info()
    {
        //获取当前视频的详情
        $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
        $video_info = Loader::model('Video')->find($video_id);
        $this->assign('video_info',$video_info);

        //右侧视频推荐
        $recommand_list = Loader::model('Video')->limit(5)->select();
        $this->assign('recommand_list',$recommand_list);
        $this->assign('page_title','视频详情');

        if($video_info){
            $video_info->view_num += 1;
            $video_info->save();
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
                throw new \Exception('您还没登录，请登录完再过来重试！');
            }
            $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
            if(empty($video_id)){
                throw new \Exception('入参错误');
            }
            $video_info = Loader::model('Video')->find($video_id);
            if(empty($video_id)){
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
        $url = 'http://www.zptys.net/index/video/info?video_id=3';//加http://这样扫码可以直接跳转url
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


}