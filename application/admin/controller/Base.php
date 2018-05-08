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
//        echo $request->module();echo"<br>";
//        echo $request->controller();echo"<br>";
//        echo $request->action();echo"<br>";
//        exit;
        $admin_id = session('admin_id');
        if(empty($admin_id)
            && !( $request->controller() == 'Index' && $request->action() == 'login' )
            && !( $request->controller() == 'Index' && $request->action() == 'logout' )
        ){
            if(Request::instance()->isAjax()){
                $this->ajax_return('',400);
            } else {
                return redirect('/admin/index/login');
            }
        }

    }

    public function ajax_return ($data,$code)
    {
        echo json_encode(['code' => $code,'data' => $data]);
        exit;
    }


}