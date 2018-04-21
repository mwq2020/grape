<?php

namespace app\wechat\model;

use think\Model;

class Activity extends Model
{
    protected $pk = 'activity_id';
    protected $table = 'activity';

    /**
     * 获取文件的大小
     * @param $size
     * @param $format
     * @return string
     */
    function getsize($file_path , $format='mb') {
        if(empty($file_path)) {
            return 0;
        }
        $size  = file_exists(ROOT_PATH.'public'.$file_path) ? filesize(ROOT_PATH.'public'.$file_path) : 0 ;
        if(empty($size)){
            return 0;
        }
        $p = 0;
        if ($format == 'kb') {
            $p = 1;
        } elseif ($format == 'mb') {
            $p = 2;
        } elseif ($format == 'gb') {
            $p = 3;
        }
        $size /= pow(1024, $p);
        return number_format($size, 3).strtoupper($format);
    }
}
