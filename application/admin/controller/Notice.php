<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

class Notice extends Base
{
    /**
     * 消息列表
     */
    public function index()
    {
        $notice_list = Db::table('notice')->order('notice_id','desc')->paginate(10, false, ['query' => $_GET]);
        $this->assign('notice_list',$notice_list);

        $this->view->engine->layout('layout');
        return $this->fetch('notice/index');
    }

    /**
     * 消息添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('notice/add');
        }

        try {
            if(empty($_REQUEST['title'])){
                throw new \Exception('标题不能为空');
            }
            if(empty($_REQUEST['content'])){
                throw new \Exception('内容不能为空');
            }
            if(empty($_REQUEST['send_type'])){
                throw new \Exception('发送对象不能为空');
            }

            //发送对象的列表
            if($_REQUEST['send_type'] == 2){
                $_REQUEST['reader_no_list'];
            }

            $data = [];
            $data['title']          = $_REQUEST['title'];
            $data['content']        = $_REQUEST['content'];
            $data['admin_id']       = session('admin_id');
            $data['create_name']    = session('account');
            $data['send_type']      = $_REQUEST['send_type'];
            $data['status']         = 0;
            $data['send_time']      = empty($_REQUEST['send_time']) ? 0 : strtotime($_REQUEST['send_time']);
            $data['add_time']       = time();
            $flag = Loader::model('Notice')->insert($data);
            if(empty($flag)){
                throw new \Exception('公告添加失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('notice/add');
        }
        return $this->redirect('/admin/notice/index');
    }

    /**
     * 管理员修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $notice_info = Loader::model('Notice')->find($_REQUEST['notice_id']);
        $this->assign('notice_info',$notice_info);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('notice/edit');
        }

        try {
            if(empty($_REQUEST['title'])){
                throw new \Exception('标题不能为空');
            }
            if(empty($_REQUEST['content'])){
                throw new \Exception('内容不能为空');
            }
            if(empty($_REQUEST['send_type'])){
                throw new \Exception('发送对象不能为空');
            }

            //发送对象的列表
            if($_REQUEST['send_type'] == 2){
                $_REQUEST['reader_no_list'];
            }

            $notice_info->title         = $_REQUEST['title'];
            $notice_info->content       = $_REQUEST['content'];
            $notice_info->send_type     = $_REQUEST['send_type'];
            $notice_info->send_time     = empty($_REQUEST['send_time']) ? 0 : strtotime($_REQUEST['send_time']);
            $flag = $notice_info->save();
            if(empty($flag)){
                throw new \Exception('公告修改失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
        }
        return $this->redirect('/admin/notice/index');
    }


    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['role_id'])){
                throw new \Exception('入参错误');
            }
            $role_info = Loader::model('Role')->where('role_id',$_REQUEST['role_id'])->find();
            if(empty($role_info)){
                throw new \Exception('公告不存在');
            }
            $role_info->status = intval($_REQUEST['status']);
            $role_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
        }
        return $this->redirect('/admin/notice/index');
    }

}