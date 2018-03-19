<?php
namespace app\index\controller;
use \think\Db;

class Category extends \think\Controller
{
    public function index()
    {
        $view_data = [];
        return $this->fetch('index',$view_data);
    }

}
