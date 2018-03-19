<?php
namespace app\index\controller;
use \think\Db;

class User extends \think\Controller
{
    /**
     * 登录操作
     * @return mixed
     */
    public function login()
    {
        $view_data = [];
        return $this->fetch('test',$view_data);
    }

    /**
     * 退出操作
     */
    public function logout()
    {

    }


    public function viewlist()
    {
        $view_data = [];
        return $this->fetch('viewlist',$view_data);
    }


    public function message()
    {
        $view_data = [];
        return $this->fetch('message',$view_data);
    }

    public function productionlist()
    {
        $view_data = [];
        return $this->fetch('productionlist',$view_data);
    }

    public function productioninfo()
    {
        $view_data = [];
        return $this->fetch('productioninfo',$view_data);
    }



}
