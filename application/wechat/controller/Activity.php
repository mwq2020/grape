<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;

class Activity extends \think\Controller
{
    public function index()
    {

        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('index');
    }

}