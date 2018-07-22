<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;
use think\Request;

class Base extends \think\Controller
{
    protected $beforeActionList = [
        'all_run'
    ];

    public function all_run() {
        $request= Request::instance();
        $admin_id = session('admin_id');

        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        //没登录切访问的是登录页面或者登出页面
        if(
            ($controller == 'index' && $action == 'login') ||
            ($controller == 'index' && $action == 'logout') ||
            ($controller == 'index' && $action == 'region_list')
        ){
            return true;
        }

        if(empty($admin_id)){
            if(Request::instance()->isAjax()){
                $this->ajax_return('请你重新登录再操作',400);
            } else {
                return redirect('/admin/index/login');
            }
        }

        //登录后的用户可以直接党文到一下几个公共页面[页面框架页面不带任何的功能]
        if($controller == 'index' && in_array($action,['index','top','left','right'])) {
            return true;
        }

        $account_name = session('account');
        if($account_name == 'admin'){
            return true;
        }

        $current_uri =strtolower($request->module().'/'.$request->controller().'/'.$request->action());
        $role_id = session('role_id');
        $privilege_map = session('privilege_map');
        if(empty($privilege_map)) {
            $map = [];
            $map['b.status'] = 1;
            $map['a.role_id'] = $role_id;
            $privilege_res = Db::table('role_resource')->alias('a')->join('resource b','a.resource_id=b.resource_id')->where($map)->field('a.resource_id,b.resource_name,b.url')->select();
            $privilege_map = [];
            foreach($privilege_res as $row){
                array_push($privilege_map,$row['url']);
            }
            session('privilege_map',$privilege_map);
        }

        if($account_name != 'admin'){
            if(!in_array($current_uri,$privilege_map)){
                if(Request::instance()->isAjax()){
                    $this->ajax_return('您无权限操作',400);
                } else {
                    echo "您无权限操作,如需要请联系管理员";exit;
                }
            }
        }

    }

    public function ajax_return ($data,$code=200)
    {
        echo json_encode(['code' => $code,'data' => $data]);
        exit;
    }

    public function _empty(){
        $this->error('方法不存在');
    }

}