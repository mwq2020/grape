<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

class Role extends Base
{
    /**
     * 角色列表
     */
    public function index()
    {
        $role_list = Loader::model('Role')->order('role_id','desc')->paginate(10);
        $this->assign('role_list',$role_list);

        $this->view->engine->layout('layout');
        return $this->fetch('role/index');
    }

    /**
     * 管理员添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        $privilege_lsit = $this->getPrivilegeList();
        $this->assign('privilege_lsit',$privilege_lsit);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('role/add');
        }

        try {
            if(empty($_REQUEST['role_name'])){
                throw new \Exception('角色名称不能为空');
            }

            //检查账户名是否已经存在
            $role_exist = Loader::model('Role')->where('role_name',$_REQUEST['role_name'])->find();
            if(!empty($role_exist)){
                throw new \Exception('账户名已存在，请更换');
            }

            $privilege_data = isset($_REQUEST['privilege']) ? $_REQUEST['privilege'] : [];
            $data = [];
            $data['role_name']      = $_REQUEST['role_name'];
            $data['privilege_data'] = json_encode($privilege_data);
            $data['status']     = 1;
            $data['remark']     = $_REQUEST['remark'];
            $data['add_time']    = time();
            $flag = Loader::model('Role')->insert($data);
            if(empty($flag)){
                throw new \Exception('角色添加失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('role/add');
        }
        return $this->redirect('/admin/role/index');
    }

    /**
     * 管理员修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $privilege_lsit = $this->getPrivilegeList();
        $this->assign('privilege_lsit',$privilege_lsit);

        $role_info = Loader::model('Role')->find($_REQUEST['role_id']);
        $this->assign('role_info',$role_info);

        $privilege_data = json_decode($role_info->privilege_data,true);
        $this->assign('privilege_data',$privilege_data);
//        echo "<pre>";
//        print_r($privilege_data);
//        print_r($role_info);
//        exit;

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('role/edit');
        }

        try {
            if(empty($_REQUEST['role_name'])){
                throw new \Exception('角色名称不能为空');
            }

            //检查角色名是否已经存在
            $map = [];
            $map['role_id'] = ['<>',$_REQUEST['role_id']];
            $map['role_name'] = $_REQUEST['role_name'];
            $account_exist = Loader::model('Role')->where($map)->find();
            if(!empty($account_exist)){
                throw new \Exception('角色名已存在，请更换');
            }

            $privilege_data = isset($_REQUEST['privilege']) ? $_REQUEST['privilege'] : [];
            $role_info->role_name       = $_REQUEST['role_name'];
            $role_info->privilege_data  = json_encode($privilege_data);
            $role_info->remark          = $_REQUEST['remark'];
            $flag = $role_info->save();
            if(empty($flag)){
                throw new \Exception('角色修改失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('role/edit');
        }
        return $this->redirect('/admin/role/index');
    }


    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['role_id'])){
                throw new \Exception('入参错误');
            }
            $role_info = Loader::model('Role')->where('role_id',$_REQUEST['role_id'])->find();
            if(empty($role_info)){
                throw new \Exception('角色不存在');
            }
            $role_info->status = intval($_REQUEST['status']);
            $role_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('admin/add');
        }
        return $this->redirect('/admin/role/index');
    }


    private function getPrivilegeList()
    {
        return [
            'customer'=>[
                'name'=>'用户管理',
                'child'=>[
                    'customer'=>['customer'=>'客户管理','select'=>'查询','add'=>'新增'],
                    'user'=>['user'=>'用户管理','select'=>'查询','add'=>'新增']
                ]
            ],
            'video'=>[
                'name'=>'资源管理',
                'child'=>[
                    'contract'=>['video'=>'资源管理','select'=>'查询','add'=>'新增']
                ]
            ],
            'activity'=>[
                'name'=>'活动管理',
                'child'=>[
                    'activity'=>['activity'=>'活动管理','select'=>'查询','add'=>'新增']
                ]
            ],
            'product'=>[
                'name'=>'作品管理',
                'child'=>[
                    'product'=>['product'=>'作品管理','select'=>'查询','add'=>'新增']
                ]
            ],
            'contract'=>[
                'name'=>'合同管理',
                'child'=>[
                    'contract'=>['contract'=>'合同管理','select'=>'查询','add'=>'新增']
                ]
            ],
            'banner'=>[
                'name'=>'个性化设置',
                'child'=>[
                    'banner'=>['banner'=>'banner管理','select'=>'查询','add'=>'新增'],
                    'url'=>['url'=>'域名设置','select'=>'查询','add'=>'新增']
                ]
            ],
            'setting'=>[
                'name'=>'系统管理',
                'child'=>[
                    'admin'=>['admin'=>'账户管理','select'=>'查询','add'=>'新增'],
                    'role'=>['role'=>'角色管理','select'=>'查询','add'=>'新增'],
                    'message'=>['message'=>'公告管理','select'=>'查询','add'=>'新增']
                ]
            ],
        ];
    }
}