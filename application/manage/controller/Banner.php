<?php
namespace app\manage\controller;
use \think\Db;
use think\Loader;

class Banner extends \think\Controller
{

    /**
     * banner列表
     * @return mixed
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

        $banner_list = Loader::model('Banner')->where($where)->order('banner_id','desc')->paginate(10);
        $this->assign('banner_list', $banner_list);

        $page = $banner_list->render();
        $this->assign('page', $page);

        $this->view->engine->layout('layout');
        return $this->fetch('banner/index');
    }

    /**
     * 添加banner页面
     * @return mixed
     */
    public function add()
    {
        if(empty($_POST)){
            $this->assign('error_msg', '');
            $this->view->engine->layout('layout');
            return $this->fetch('banner/add');
        }

        try{
            $image_name = '';
            $file = request()->file('banner_img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/banner');
                if($info){
                    $image_name =  $info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }
            if(empty($image_name)){
                throw new \Exception('banenr图片不能为空');
            }
            if(empty($_REQUEST['title'])){
                throw new \Exception('banenr标题不能为空');
            }

            $data = [];
            $data['title']          = $_REQUEST['title'];
            $data['url']            = $_REQUEST['url'];
            $data['banner_img']     = $image_name ? '/static/image/banner/'.$image_name : '';
            $data['status']         = intval($_REQUEST['status']);
            $data['banner_type']    = $_REQUEST['banner_type'];
            $data['remark']         = $_REQUEST['remark'];
            $data['add_time']       = time();
            $data['update_time']    = time();
            $flag = Loader::model('Banner')->insert($data);
            if(empty($flag)){
                throw new Exception('banenr添加失败');
            }
        } catch (Exception $e) {
            $this->assign('error_msg', $e->getMessage());
            $this->view->engine->layout('layout');
            return $this->fetch('banner/add');
        }
        return $this->redirect('banner/index');
    }


    /**
     * 添加banner页面
     * @return mixed
     */
    public function edit()
    {
        $banner_info = Loader::model('Banner')->find($_REQUEST['banner_id']);
        $this->assign('banner_info', $banner_info);
        if(empty($_POST)){
            $this->assign('error_msg', '');
            $this->view->engine->layout('layout');
            return $this->fetch('banner/edit');
        }

        try{
            $image_name = '';
            $file = request()->file('banner_img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/banner');
                if($info){
                    $image_name =  $info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }

            if(empty($_REQUEST['title'])){
                throw new \Exception('banenr标题不能为空');
            }

            $banner_info->title = $_REQUEST['title'];
            $banner_info->url = $_REQUEST['url'];
            if($image_name){
                $banner_info->banner_img = $image_name ? '/static/image/banner/'.$image_name : '';
            }
            $banner_info->status = $_REQUEST['status'];
            $banner_info->banner_type = $_REQUEST['banner_type'];
            $banner_info->remark = $_REQUEST['remark'];
            $banner_info->update_time = time();
            $flag = $banner_info->save();

            if(empty($flag)){
                throw new Exception('banenr修改失败');
            }
        } catch (Exception $e) {
            $this->assign('error_msg', $e->getMessage());
            $this->view->engine->layout('layout');
            return $this->fetch('banner/edit');
        }
        return $this->redirect('banner/index');
    }

    /**
     * 修改banner的状态
     * @return mixed|void
     */
    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['banner_id'])){
                throw new \Exception('入参错误');
            }
            $banner_info = Loader::model('Banner')->where('banner_id',$_REQUEST['banner_id'])->find();
            if(empty($banner_info)){
                throw new \Exception('角色不存在');
            }
            $banner_info->status = intval($_REQUEST['status']);
            $banner_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('banner/add');
        }
        return $this->redirect('/manage/banner/index');
    }

}