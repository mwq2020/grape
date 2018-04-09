<?php
namespace app\index\controller;
use \think\Db;
use think\Exception;
use think\Loader;

class Activity extends \think\Controller
{
    /**
     * 活动列表页面
     * @return mixed
     */
    public function index()
    {
        $activity_list = Loader::model('Activity')->where('status',1)->order('activity_id','desc')->limit(12)->select();
        if($activity_list){
            $activity_list = collection($activity_list)->toArray();
            foreach($activity_list as &$row){
                $row['activity_gallery'] = json_decode($row['activity_gallery'],true);
                if($row['start_time'] > time()){
                    $row['status_txt'] = '未开始';
                } elseif($row['start_time'] <= time() && $row['end_time'] >= time()){
                    $row['status_txt'] = '进行中';
                }else {
                    $row['status_txt'] = '已结束';
                }
            }
        }

//        echo "<pre>";
//        print_r($activity_list);
//        exit;

        $this->assign('activity_list',$activity_list);
        $this->assign('page_title','活动列表');
        return $this->fetch('activity/index');
    }

    /**
     * 活动详情页面
     * @return mixed
     */
    public function info()
    {
        $activity_id = isset($_REQUEST['activity_id']) ? intval($_REQUEST['activity_id']) : 0;
        $activity_info = Loader::model('Activity')->find($activity_id);
        if($activity_info){
            $activity_info = $activity_info->toArray();
            $activity_info['activity_gallery'] = json_decode($activity_info['activity_gallery'],true);
            if($activity_info['start_time'] > time()){
                $activity_info['status_txt'] = '未开始';
            } elseif($activity_info['start_time'] <= time() && $activity_info['end_time'] >= time()){
                $activity_info['status_txt'] = '进行中';
            }else {
                $activity_info['status_txt'] = '已结束';
            }
        }

        //输出活动结束标志
        if($activity_info['end_time'] <= time()){
            $this->assign('is_end',1);
        } else {
            $this->assign('is_end',0);
        }

//        echo "<pre>";
//        print_r($activity_info);
//        exit;

        $this->assign('activity_info',$activity_info);
        $this->assign('page_title','活动详情');
        return $this->fetch('info');
    }

    /**
     * 活动规则页面
     * @return mixed
     */
    public function rule()
    {
        $activity_id = isset($_REQUEST['activity_id']) ? intval($_REQUEST['activity_id']) : 0;
        $activity_info = Loader::model('Activity')->find($activity_id);
        if($activity_info){
            $activity_info = $activity_info->toArray();

            $activity_info['activity_gallery'] = json_decode($activity_info['activity_gallery'],true);
            if($activity_info['start_time'] > time()){
                $activity_info['status_txt'] = '未开始';
            } elseif($activity_info['start_time'] <= time() && $activity_info['end_time'] >= time()){
                $activity_info['status_txt'] = '进行中';
            }else {
                $activity_info['status_txt'] = '已结束';
            }
        }
        $this->assign('activity_info',$activity_info);
        $this->assign('page_title','活动规则');
        return $this->fetch('rule');
    }

    /**
     * 活动结果页面
     */
    public function res()
    {
        $query = Db::table('product')->alias('a')->join('activity b','a.activity_id=b.activity_id')->join('user c','a.user_id=c.user_id');
        $product_list = $query->select();

        $this->assign('product_list',$product_list);
        $this->assign('page_title','活动结果');
        return $this->fetch('res');
    }

    /**
     * 上传视频文件
     */
    public function upload_file()
    {
        $return_data = ['code' => 200,'msg' => '','data'=>[]];
        try {
            $file = request()->file('product_img');
            if(empty($file)){
                throw new Exception('上传文件为空');
            }

            $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/product');
            if($info){
                $return_data['url'] =  '/static/image/product/'.$info->getSaveName();
            }else{
                throw new \Exception('图片保存失败【'.$file->getError().'】');
            }

        } catch (Exception $e){
            $return_data['code']    = 500;
            $return_data['msg']     = $e->getMessage();
        }

        exit(json_encode($return_data));
    }

}
