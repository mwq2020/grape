<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

class Resource extends Base
{
    /**
     * 角色列表
     */
    public function index()
    {
        $resource_list = Loader::model('Resource')->order('resource_id','desc')->paginate(10);
        $this->assign('resource_list',$resource_list);

        $this->view->engine->layout('layout');
        return $this->fetch('resource/index');
    }

    /**
     * 管理员添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        $parent_resource = Loader::model('Resource')->where('parent_id',0)->select();
        $this->assign('parent_resource',$parent_resource);

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('resource/add');
        }

        try {
            if(empty($_REQUEST['resource_name'])){
                throw new \Exception('权限资源名称不能为空');
            }

            //检查账户名是否已经存在
            $role_exist = Loader::model('Resource')->where('resource_name',$_REQUEST['resource_name'])->find();
            if(!empty($role_exist)){
                //throw new \Exception('权限资源已存在，请更换');
            }

            $data = [];
            $data['resource_name']  = $_REQUEST['resource_name'];
            $data['url']            = trim($_REQUEST['url']);
            $data['parent_id']      = $_REQUEST['parent_id'];
            $data['status']         = 1;
            $data['remark']         = $_REQUEST['remark'];
            $data['add_time']       = time();
            $data['update_time']    = time();
            $flag = Loader::model('Resource')->insert($data);
            if(empty($flag)){
                throw new \Exception('权限资源添加失败');
            }

        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('resource/add');
        }
        return $this->redirect('/admin/resource/index');
    }

    /**
     * 管理员修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $parent_resource = Loader::model('Resource')->where('parent_id',0)->select();
        $this->assign('parent_resource',$parent_resource);

        $resource_info = Loader::model('Resource')->find($_REQUEST['resource_id']);
        $this->assign('resource_info',$resource_info);

//        echo "<pre>";
//        print_r($privilege_data);
//        print_r($role_info);
//        exit;

        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('resource/edit');
        }

        try {
            if(empty($_REQUEST['resource_name'])){
                throw new \Exception('角色名称不能为空');
            }

            //检查角色名是否已经存在
            $map = [];
            $map['resource_id'] = ['<>',$_REQUEST['resource_id']];
            $map['resource_name'] = $_REQUEST['resource_name'];
            $account_exist = Loader::model('Resource')->where($map)->find();
            if(!empty($account_exist)){
                //throw new \Exception('角色名已存在，请更换');
            }

            $privilege_data = isset($_REQUEST['privilege']) ? $_REQUEST['privilege'] : [];
            $resource_info->resource_name       = $_REQUEST['resource_name'];
            $resource_info->url                 = $_REQUEST['url'];
            $resource_info->parent_id           = $_REQUEST['parent_id'];
            $resource_info->status              = $_REQUEST['status'];
            $resource_info->update_time         = time();
            $resource_info->remark          = $_REQUEST['remark'];
            $flag = $resource_info->save();
            if(empty($flag)){
                throw new \Exception('权限资源修改失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('resource/edit');
        }
        return $this->redirect('/admin/resource/index');
    }


    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['resource_id'])){
                throw new \Exception('入参错误');
            }
            $resource_info = Loader::model('Resource')->where('resource_id',$_REQUEST['resource_id'])->find();
            if(empty($resource_info)){
                throw new \Exception('权限资源不存在');
            }
            $resource_info->status = intval($_REQUEST['status']);
            $resource_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('resource/add');
        }
        return $this->redirect('/admin/resource/index');
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