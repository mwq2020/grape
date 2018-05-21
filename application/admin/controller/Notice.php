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
                $reader_no_list = trim(str_replace('，',',',$_REQUEST['reader_no_list']),',');
                if(empty($reader_no_list)){
                    throw new \Exception('发送对象为指定用户时，用户列表不能为空');
                }
                $reader_no_list = explode(',',$reader_no_list);
                $user_not_exist_list = [];
                foreach($reader_no_list as $row) {
                    $user_info = Loader::model('User')->where(['reader_no' => $row])->find();
                    if(empty($user_info)){
                        $user_not_exist_list[] = $row;
                    }
                }
                if(!empty($user_not_exist_list)){
                    throw new \Exception('以下读者证号【'.implode(',',$user_not_exist_list).'】不存在，请检查');
                }
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
            $notice_id= Loader::model('Notice')->getLastInsID();

            if($_REQUEST['send_type'] == 2) {
                $reader_no_list = trim(str_replace('，', ',', $_REQUEST['reader_no_list']), ',');
                $reader_no_list = explode(',', $reader_no_list);
                foreach($reader_no_list as $row) {
                    $user_info = Loader::model('User')->where(['reader_no' => $row])->find();
                    if(empty($user_info)){
                        continue;
                    }
                    $is_exist = Db::table('notice_user')->where(['notice_id' => $notice_id,'user_id'=>$user_info['user_id']])->find();
                    if(empty($is_exist)){
                        Db::table('notice_user')->insert(['notice_id' => $notice_id,'user_id'=>$user_info['user_id'],'add_time'=>time()]);
                    }
                }
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
                $reader_no_list = trim(str_replace('，',',',$_REQUEST['reader_no_list']),',');
                if(empty($reader_no_list)){
                    throw new \Exception('发送对象为指定用户时，用户列表不能为空');
                }
                $reader_no_list = explode(',',$reader_no_list);
                $user_not_exist_list = [];
                foreach($reader_no_list as $row) {
                    $user_info = Loader::model('User')->where(['reader_no' => $row])->find();
                    //print_r($user_info);
                    if(empty($user_info)){
                        $user_not_exist_list[] = $row;
                    }
                }
                if(!empty($user_not_exist_list)){
                    throw new \Exception('以下读者证号【'.implode(',',$user_not_exist_list).'】不存在，请检查');
                }
            }

            $notice_info->title         = $_REQUEST['title'];
            $notice_info->content       = $_REQUEST['content'];
            $notice_info->send_type     = $_REQUEST['send_type'];
            $notice_info->send_time     = empty($_REQUEST['send_time']) ? 0 : strtotime($_REQUEST['send_time']);
            $notice_info->add_time = time();
            $flag = $notice_info->save();
            if(empty($flag)){
                throw new \Exception('公告修改失败');
            }

            //发送用户列表处理
            $notice_id = $_REQUEST['notice_id'];
            if($_REQUEST['send_type'] == 2) {
                $reader_no_list = trim(str_replace('，', ',', $_REQUEST['reader_no_list']), ',');
                $reader_no_list = explode(',', $reader_no_list);
                foreach($reader_no_list as $row) {
                    $user_info = Loader::model('User')->where(['reader_no' => $row])->find();
                    if(empty($user_info)){
                        continue;
                    }
                    $is_exist = Db::table('notice_user')->where(['notice_id' => $notice_id,'user_id'=>$user_info['user_id']])->find();
                    if(empty($is_exist)){
                        Db::table('notice_user')->insert(['notice_id' => $notice_id,'user_id'=>$user_info['user_id'],'add_time'=>time()]);
                    }
                }
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            return $this->fetch('notice/edit');
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


    /**
     * 消息列表
     */
    public function send_list()
    {
        $where = [];
        $where['a.notice_id'] = $_REQUEST['notice_id'];
        if(!empty($_REQUEST['user_id'])){
            $where['a.user_id'] = $_REQUEST['user_id'];
        }
        if(!empty($_REQUEST['reader_no'])){
            $where['b.reader_no'] = ['like','%'.trim($_REQUEST['reader_no']).'%'];
        }
        if(!empty($_REQUEST['real_name'])){
            $where['b.real_name'] = ['like','%'.trim($_REQUEST['real_name']).'%'];
        }

        $notice_user_list = Db::table('notice_user')->alias('a')
                       ->join('user b','a.user_id=b.user_id')
                       ->where($where)
                        ->field('a.*,b.reader_no,b.real_name')
                       ->order('a.id','desc')
                       ->paginate(10, false, ['query' => $_GET]);
        $this->assign('notice_user_list',$notice_user_list);

        $this->view->engine->layout('layout');
        return $this->fetch('notice/send_list');
    }


    public function delete_user()
    {
        try {
            if(empty($_REQUEST['id'])){
                throw new \Exception('入参错误');
            }
            $notice_user_info = Db::table('notice_user')->where('id',$_REQUEST['id'])->find();
            if(empty($notice_user_info)){
                throw new \Exception('公告发送用户不存在');
            }
            Db::table('notice_user')->where('id',$_REQUEST['id'])->delete();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            //echo $error_msg;exit;
        }
        return $this->redirect('/admin/notice/send_list?notice_id='.$_REQUEST['notice_id']);
    }



}