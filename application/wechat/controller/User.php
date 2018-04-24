<?php
namespace app\wechat\controller;
use \think\Db;
use think\Loader;

class User extends \think\Controller
{
    public function index()
    {
        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('index');
    }


    public function view_list()
    {
        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('view_list');
    }


    public function message_list()
    {
        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('message_list');
    }


    public function product_list()
    {
        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('product_list');
    }

    public function collect_list()
    {
        $this->assign('page_title','紫葡萄少儿艺术库');
        return $this->fetch('collect_list');
    }

}