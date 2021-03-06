<?php
namespace app\admin\controller;
use \think\Db;
use think\Exception;
use think\Loader;

class Admin extends Base
{

    /**
     * 作品列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if(!empty($_REQUEST['account'])){
            $where['a.account'] = ['like','%'.trim($_REQUEST['account']).'%'];
        }
        if(!empty($_REQUEST['user_name'])){
            $where['a.user_name'] = ['like','%'.trim($_REQUEST['user_name']).'%'];
        }
        if(!empty($_REQUEST['role_id'])){
            $where['a.role_id'] = intval($_REQUEST['role_id']);
        }

        $role_list = Loader::model('Role')->where('status',1)->select();
        $this->assign('role_list',$role_list);

        $admin_list = Db::table('admin')->alias('a')
                    ->join('role b','a.role_id=b.role_id')
                    ->where($where)->order('a.admin_id','desc')
                    ->field('a.*,b.role_name')
                    ->paginate(10,false,['query' => $_GET]);
        $this->assign('admin_list', $admin_list);

        $this->view->engine->layout('layout');
        return $this->fetch('admin/index');
    }

    /**
     * 管理员添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        $role_list = Loader::model('Role')->where('status',1)->select();
        $this->assign('role_list',$role_list);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('admin/add');
        }

        try {
            if(empty($_REQUEST['account'])){
                throw new \Exception('登录账号不能为空');
            }
            if(empty($_REQUEST['user_name'])){
                throw new \Exception('用户名不能为空');
            }
            if(empty($_REQUEST['email'])){
                throw new \Exception('邮箱不能为空');
            }
            if(empty($_REQUEST['mobile'])){
                throw new \Exception('手机号不能为空');
            }
            if(empty($_REQUEST['password'])){
                throw new \Exception('密码不能为空');
            }
            if($_REQUEST['password'] != $_REQUEST['password_repeat']){
                throw new \Exception('密码和确认密码不一致');
            }
            if(empty($_REQUEST['role_id'])){
                throw new \Exception('角色不能为空');
            }

            //检查账户名是否已经存在
            $account_exist = Loader::model('Admin')->where('account',$_REQUEST['account'])->find();
            if(!empty($account_exist)){
                throw new \Exception('账户名已存在，请更换');
            }

            $data = [];
            $data['account']    = $_REQUEST['account'];
            $data['user_name']  = $_REQUEST['user_name'];
            $data['email']      = $_REQUEST['email'];
            $data['mobile']     = $_REQUEST['mobile'];
            $data['salt']       = rand(1000,9999);
            $data['password']   = md5($_REQUEST['password'].$data['salt']);
            $data['role_id']    = intval($_REQUEST['role_id']);
            $data['status']     = 1;
            $data['add_time']   = time();
            $flag = Loader::model('Admin')->insert($data);
            if(empty($flag)){
                throw new \Exception('账户添加失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('admin/add');
        }
        return $this->redirect('/admin/admin/index');
    }

    /**
     * 管理员修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $role_list = Loader::model('Role')->where('status',1)->select();
        $this->assign('role_list',$role_list);

        $admin_info = Loader::model('Admin')->find($_REQUEST['admin_id']);
        $this->assign('admin_info',$admin_info);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('admin/edit');
        }

        try {
            if(empty($_REQUEST['account'])){
                throw new \Exception('登录账号不能为空');
            }
            if(empty($_REQUEST['user_name'])){
                throw new \Exception('用户名不能为空');
            }
            if(empty($_REQUEST['email'])){
                throw new \Exception('邮箱不能为空');
            }
            if(empty($_REQUEST['mobile'])){
                throw new \Exception('手机号不能为空');
            }
            if(!empty($_REQUEST['password']) || !empty($_REQUEST['password_repeat'])){
                if($_REQUEST['password'] != $_REQUEST['password_repeat']){
                    throw new \Exception('密码和确认密码不一致');
                }
            }
            if(empty($_REQUEST['role_id'])){
                throw new \Exception('角色不能为空');
            }
            if(empty($_REQUEST['admin_id'])){
                throw new \Exception('参数错误');
            }
            if(empty($admin_info)){
                throw new \Exception('管理员不存在，刷新试试');
            }

            //检查账户名是否已经存在
            $map = [];
            $map['admin_id'] = ['<>',$_REQUEST['admin_id']];
            $map['account'] = $_REQUEST['account'];
            $account_exist = Loader::model('Admin')->where($map)->find();
            if(!empty($account_exist)){
                throw new \Exception('账户名已存在，请更换');
            }

            $admin_info->account    = $_REQUEST['account'];
            $admin_info->user_name  = $_REQUEST['user_name'];
            $admin_info->email      = $_REQUEST['email'];
            $admin_info->mobile     = $_REQUEST['mobile'];
            $admin_info->role_id    = intval($_REQUEST['role_id']);
            if(!empty($_REQUEST['password'])){
                $admin_info->salt = rand(1000,9999);
                $admin_info->password = md5($_REQUEST['password'].$admin_info->salt);
            }
            $flag = $admin_info->save();
            if(empty($flag)){
                throw new \Exception('账户修改失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('admin/admin_edit');
        }

        return $this->redirect('/admin/admin/index');
    }


    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['admin_id'])){
                throw new \Exception('入参错误');
            }
            $admin_info = Loader::model('Admin')->where('admin_id',$_REQUEST['admin_id'])->find();
            if(empty($admin_info)){
                throw new \Exception('管理员不存在');
            }
            $admin_info->status = intval($_REQUEST['status']);
            $admin_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
        }
        return $this->redirect('/admin/admin/index',302);
    }

    /**
     * 用户修改密码
     */
    public function modify_password()
    {
        if(!empty($_POST)){
            try {
                $admin_id = session('admin_id');
                if(empty($_POST['admin_id']) || empty($admin_id) || $admin_id != $_POST['admin_id']) {
                    throw new \Exception('参数错误');
                }

                if(empty($_POST['old_password'])){
                    throw new \Exception('原密码不能为空');
                }
                if(empty($_POST['password'])){
                    throw new \Exception('新密码不能为空');
                }
                if(empty($_POST['comfirm_password'])){
                    throw new \Exception('确认密码不能为空');
                }
                if($_POST['password'] != $_POST['comfirm_password']){
                    throw new \Exception('新密码和确认密码不一致');
                }
                if(strlen($_POST['password']) < 6){
                    throw new \Exception('新密码长度太短');
                }

                $admin_info = Loader::model('Admin')->find($admin_id);
                if(empty($admin_info)){
                    throw new \Exception('管理账号不存在');
                }
                if(md5($_POST['old_password'].$admin_info['salt']) !=  $admin_info['password']){
                    throw new \Exception('原密码错误');
                }

                $salt = rand(9999,100);
                $md5_password = md5($_POST['password'].$salt);
                $admin_info->salt = $salt;
                $admin_info->password = $md5_password;
                $flag = $admin_info->save();
                if(empty($flag)){
                    throw new \Exception('密码修改失败');
                }
                $this->redirect('/admin/index/right',302);
            } catch (\Exception $e) {
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
