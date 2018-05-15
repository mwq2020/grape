<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

class Video extends Base
{

    /**
     * 默认页面
     * @return mixed|\think\response\Redirect
     */
    public function index()
    {

        $where = [];
        if(!empty($_REQUEST['title'])){
            $where['title'] = ['like','%'.trim($_REQUEST['title']).'%'];
        }
        if(!empty($_REQUEST['cat_id'])){
            $where['cat_id'] = intval($_REQUEST['cat_id']);
        }

        $cat_list = Loader::model('Category')->where('parent_id',0)->order('cat_id','asc')->select();
        $this->assign('cat_list', $cat_list);

        $video_list = Loader::model('Video')->where($where)->order('video_id','desc')->paginate(10,false,['query' => $_GET]);
        $this->assign('video_list', $video_list);

        $this->view->engine->layout('layout');
        return $this->fetch('video/index');
    }


    public function add()
    {
        $error_msg = '';
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('video/add');
        }

        try {
            if(empty($_REQUEST['title'])){
                throw new \Exception('视频分类id不能为空');
            }
            if(empty($_REQUEST['cat_id'])){
                throw new \Exception('视频分类id不能为空');
            }
            if(empty($_REQUEST['second_cat_id'])){
                throw new \Exception('视频二级分类id不能为空');
            }

            $image_name = '';
            $file = request()->file('video_img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/video');
                if($info){
                    $image_name =  $info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }

            $video_name = '';
            $file = request()->file('video');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'video');
                if($info){
                    $video_name =  $info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }

            $data = [];
            $data['cat_id']         = $_REQUEST['cat_id'];
            $data['second_cat_id']  = $_REQUEST['second_cat_id'];
            $data['title']          = $_REQUEST['title'];
            if($image_name){
                $data['video_img']      = '/static/image/video/'.$image_name;
            }
            if($video_name){
                $data['video_url']      = '/static/video/'.$video_name;
            }
            $data['status']         = isset($_REQUEST['status']) ? $_REQUEST['status'] : 0;
            $data['position']       = isset($_REQUEST['position']) ? $_REQUEST['position'] : 0;
            $data['sort']           = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 0;
            $data['mark']           = isset($_REQUEST['mark']) ? $_REQUEST['mark'] : '';
            $data['add_time']       = time();
            $data['update_time']    = time();
            $flag = Db::table('video')->insert($data);
            if(empty($flag)){
                throw new \Exception('保存数据错误，请重试');
            }

        } catch (\Exception $e){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('video/add');
        }
        $this->redirect('/admin/video/index');
    }


    /**
     * 视频的编辑
     */
    public function edit()
    {
        $error_msg = '';
        $video_id = isset($_REQUEST['video_id']) ? $_REQUEST['video_id'] : 0;
        $video_info = Db::table('video')->find($video_id);
        $this->assign('video_info',$video_info);

        $second_cat_list = Db::table('Category')->where('parent_id',$video_info['cat_id'])->select();
        $this->assign('second_cat_list',$second_cat_list);
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('video/edit');
        }

        try {
            if(empty($video_id)){
                throw new \Exception('视频id不能为空');
            }
            if(empty($_REQUEST['title'])){
                throw new \Exception('视频分类id不能为空');
            }
            if(empty($_REQUEST['cat_id'])){
                throw new \Exception('视频分类id不能为空');
            }
            if(empty($_REQUEST['second_cat_id'])){
                throw new \Exception('视频二级分类id不能为空');
            }

            $image_name = '';
            $file = request()->file('video_img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/video');
                if($info){
                    $image_name =  '/static/image/video/'.$info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }

            $video_name = '';
            $file = request()->file('video');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'video');
                if($info){
                    $video_name =  '/static/image/video/'.$info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }

            $data = [];
            $data['cat_id']         = $_REQUEST['cat_id'];
            $data['second_cat_id']  = $_REQUEST['second_cat_id'];
            $data['title']          = $_REQUEST['title'];
            if($image_name){
                $data['video_img']      = $image_name;
            }
            if($video_name){
                $data['video_url']      = $video_name;
            }
            $data['status']         = isset($_REQUEST['status']) ? $_REQUEST['status'] : 2;
            $data['position']       = isset($_REQUEST['position']) ? $_REQUEST['position'] : 0;
            $data['sort']           = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 0;
            $data['mark']         = isset($_REQUEST['mark']) ? $_REQUEST['mark'] : '';
            $data['update_time']    = time();
            $flag = Db::table('video')->where('video_id', $video_id)->update($data);
            if(empty($flag)){
                throw new \Exception('保存数据错误，请重试');
            }

        } catch (\Exception $e){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('video/edit');
        }
        $this->redirect('/admin/video/index');
    }

    /**
     * 更改视频的状态
     * @return mixed|void
     */
    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['video_id'])){
                throw new \Exception('入参错误');
            }
            $video_info = Loader::model('video')->where('video_id',$_REQUEST['video_id'])->find();
            if(empty($video_info)){
                throw new \Exception('视频不存在');
            }
            $video_info->status = intval($_REQUEST['status']);
            $video_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('video/edit');
        }
        return $this->redirect('/admin/video/index');
    }

    /**
     * tp 上传文件代码
     */
    public function upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            //$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            $info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                echo $info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                echo $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                echo $info->getFilename();
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }

    /**
     * 获取子分类
     */
    public function get_child_category()
    {

        $cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
        if(empty($cat_id)){
            echo json_encode(['code' => 200,'data' => []]);exit;
        }
        $cat_list = Db::table('Category')->where('parent_id',$cat_id)->select();
        echo json_encode(['code' => 200,'data' => $cat_list]);exit;
    }

}
