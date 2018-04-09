<?php
namespace app\manage\controller;
use \think\Db;
use think\Exception;
use think\Loader;

class Admin extends \think\Controller
{

    /**
     * 作品列表
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

        $admin_list = Loader::model('Admin')->where($where)->order('admin_id','desc')->paginate(10);
        $this->assign('admin_list', $admin_list);

        $page = $admin_list->render();
        $this->assign('page', $page);

        $this->view->engine->layout('layout');
        return $this->fetch('admin/index');
    }

    /**
     * 用户修改密码
     */
    public function modify_password()
    {
        if(!empty($_POST)){
//            echo "<pre>";
//            print_r($_POST);
//            exit;


            try {
                $admin_id = session('admin_id');
                if(empty($_POST['admin_id']) || empty($admin_id) || $admin_id != $_POST['admin_id']) {
                    throw new Exception('参数错误');
                }

                if(empty($_POST['old_password'])){
                    throw new Exception('原密码不能为空');
                }
                if(empty($_POST['password'])){
                    throw new Exception('新密码不能为空');
                }
                if(empty($_POST['comfirm_password'])){
                    throw new Exception('确认密码不能为空');
                }
                if($_POST['password'] != $_POST['comfirm_password']){
                    throw new Exception('新密码和确认密码不一致');
                }
                if(strlen($_POST['password']) < 6){
                    throw new Exception('新密码长度太短');
                }

                $admin_info = Loader::model('Admin')->find($admin_id);
                if(empty($admin_info)){
                    throw new Exception('管理账号不存在');
                }
                if(md5($_POST['old_password'].$admin_info['salt']) !=  $admin_info['password']){
                    throw new Exception('原密码错误');
                }

                $salt = rand(9999,100);
                $md5_password = md5($_POST['password'].$salt);
                $admin_info->salt = $salt;
                $admin_info->password = $md5_password;
                $flag = $admin_info->save();
                if(empty($flag)){
                    throw new Exception('密码修改失败');
                }
                return $this->redirect('/manage/index/index');
            } catch (\Exception $e){
                $this->assign('error_msg', $e->getMessage());
                $this->view->engine->layout('layout');
                return $this->fetch('admin/modify_password');
            }

        }

        $this->assign('error_msg', '');
        $this->view->engine->layout('layout');
        return $this->fetch('admin/modify_password');
    }

    /**
     * 默认页面
     * @return mixed|\think\response\Redirect
     */
    public function video_list()
    {

        $video_list = Db::table('video')->select();

        $view_data = [];
        $view_data['video_list'] = $video_list;



        $this->view->engine->layout('layout');
        return $this->fetch('video/video_list',$view_data);
    }


    /**
     * 视频的添加、修改页面
     * @return mixed
     */
    public function  info()
    {
        $view_data = [];
        $view_data['error_msg'] = '';


        $video_id = isset($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : 0;
        if($video_id){
            $video_info = Db::table('video')->find($video_id);
            $view_data['video_info'] = $video_info;
        }

        $this->view->engine->layout('layout');
        return $this->fetch('video/info',$view_data);
    }

    /**
     * 视频的添加和编辑
     */
    public function edit()
    {
        $image_name = '';
        $file = request()->file('video_img');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image');
            if($info){
                $image_name =  $info->getSaveName();
            }else{
                // 上传失败获取错误信息
                echo $file->getError();exit;
            }
        }

        $video_name = '';
        $file = request()->file('video');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'video');
            if($info){
                $video_name =  $info->getSaveName();
            }else{
                // 上传失败获取错误信息
                echo $file->getError(); exit;
            }
        }
        //exit;

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
        $data['add_time']       = time();
        $data['update_time']    = time();
        $flag = Db::table('video')->insert($data);
        if(empty($flag)){
            echo "添加失败，请重试";exit;
        }
        $this->redirect('/manage/video/video_list');
    }


    public function test()
    {
        echo "ffff";exit;
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

}
