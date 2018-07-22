<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

class Index extends Base
{

    /**
     * 默认页面
     * @return mixed|\think\response\Redirect
     */
    public function index()
    {
        header("Cache-control: private");
        session('[start]');
        $admin_id = session('admin_id');
        if(empty($admin_id)){
            return redirect('/admin/index/login');
        }

        $view_data = [];
        //$this->view->engine->layout('layout');
        return $this->fetch('index',$view_data);
    }



    public function top(){
        return $this->fetch('top');
    }

    public function left(){
        return $this->fetch('left');
    }

    public function right(){
        return $this->fetch('right');
    }



    /**
     * 登录操作
     */
    public function login()
    {
        if(empty($_POST)){
            $view_data = [];
            return $this->fetch('login',$view_data);
        }

        $ajax_data = ['status' => 200 ,'message' => ''];
        try{

            $account = isset($_REQUEST['account']) ? $_REQUEST['account'] : '';
            $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
            if(empty($account) || empty($password)){
                throw new \Exception('账号密码不能为空');
            }

            $admin = Db::table('admin')->where(['account' => $account])->find();
            if(empty($admin)){
                throw new \Exception('用户不存在');
            }

            if(md5($password.$admin['salt']) != $admin['password']){
                throw new \Exception('用户或密码错误');
            }

            Db::table('admin')->where(['admin_id'=>$admin['admin_id']])->update(['last_login_time'=>time()]);
            session('[start]');
            session('admin_info',$admin);
            session('admin_id',$admin['admin_id']);
            session('account',$admin['account']);
            session('role_id',$admin['role_id']);

        } catch (\Exception $e) {
            $ajax_data['status'] = 500;
            $ajax_data['message'] = $e->getMessage();
        }
        echo json_encode($ajax_data);exit;
    }

    public function logout()
    {
        //  http://www.thinkphp.cn/info/137.html  session 的使用情况
        session(null);
        return redirect('/admin/index/index');
    }


    public function region_list()
    {
        $parent_id = isset($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
        $map = ['parent_id' => $parent_id];
        $region_list = Db::table('region')->where($map)->select();
        $this->ajax_return($region_list);
    }


    public function test()
    {
        $view_data = [];
        $this->view->engine->layout('layout');
        return $this->fetch('test',$view_data);
    }

    //上传图片
    public function upload_image()
    {
        $image_name = '';
        $file = request()->file('image');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/temp');
            if($info){
                $image_name =  $info->getSaveName();
            }else{
                throw new \Exception('图片保存失败【'.$file->getError().'】');
            }
        }

    }
}
