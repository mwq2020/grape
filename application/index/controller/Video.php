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

        //右侧视频推荐
        $recommand_list = Loader::model('Video')->limit(5)->select();
        $this->assign('recommand_list',$recommand_list);
        $this->assign('page_title','视频详情');

        if($video_info){
            $video_info->view_num += 1;
            $video_info->save();
        }

        return $this->fetch('video/info');
    }

    /**
     * 视频添加喜欢
     */
    public function add_like()
    {
        $return_data = ['code'=>200,'data'=>['status' => 1],'msg'=>''];
        try {
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