<?php
namespace app\index\controller;
use \think\Db;

class Activity extends \think\Controller
{
    public function index()
    {
        $view_data = [];
        return $this->fetch('index',$view_data);
    }

    public function info()
    {
        $view_data = [];
        return $this->fetch('info',$view_data);
    }

    public function rule()
    {
        $view_data = [];
        return $this->fetch('rule',$view_data);
    }

}
