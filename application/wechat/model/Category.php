<?php

namespace app\wechat\model;

use think\Model;

class Category extends Model
{
    protected $pk = 'cat_id';
    protected $table = 'category';

    //获取列表筛选的列表选项
    public function selectOption()
    {
        // 使用数组查询
        $list = User::all(['status'=>1]);
        // 使用闭包查询
        $list = User::all(function($query){
            $query->where('status', 1)->limit(3)->order('id', 'asc');
        });


        $list = Category::all();
    }

    /**
     * 获取分类列表
     * @return array
     */
    public function getCategoryList()
    {
        $list = [];
        $res = Category::where([])->select();
        foreach($res as $row){
            $list[$row['cat_id']] =  $row;
        }
        return $list;
    }

}
