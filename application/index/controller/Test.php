<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;
use PHPExcel_IOFactory;
use PHPExcel;


class Test extends \think\Controller
{
    public $file_list = [];
    /**
     * 导出execl
     * @throws \PHPExcel_Reader_Exception
     */
    public function index()
    {
        $path = dirname(__FILE__); //找到当前脚本所在路径
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('demo'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1','姓名')->setCellValue('B1','分数');//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue('A1','');
        $PHPSheet->setCellValue('A2','张三')->setCellValue('B2','50');
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007'表示生成2007版本的xlsx，
        $PHPWriter->save('/tmp/demo.xlsx'); //表示在$path路径下面生成demo.xlsx文件
        exit('success');
    }

    /**
     * 导出execl
     * @throws \PHPExcel_Reader_Exception
     */
    public function export_execl()
    {
        $path = dirname(__FILE__); //找到当前脚本所在路径
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('demo'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1','姓名')->setCellValue('B1','分数');//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue('A1','');
        $PHPSheet->setCellValue('A2','张三')->setCellValue('B2','50');
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007'表示生成2007版本的xlsx，'Excel5'表示生成2003版本Excel文件
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
        //header('Content-Type:application/vnd.ms-excel');//告诉浏览器将要输出Excel03版本文件
        header('Content-Disposition: attachment;filename="01simple.xlsx"');//告诉浏览器输出浏览器名称
        header('Cache-Control: max-age=0');//禁止缓存
        $PHPWriter->save("php://output");
        exit;
    }

    /**
     * 读取execl内容
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * CLI 访问  php public/index.php index/test/read_execl
     */
    public function read_execl()
    {
        $video_dir = '/Users/mwq/Downloads/grape_videos/手工191';
        //$video_dir = '/www/www/grape/public/static/*';
        //echo $video_dir."<br>";

        $file_list = [];
        $this->searchDir($video_dir,$file_list);
        //echo "<pre>";
        //print_r($file_list);
        //exit;


        $execl_path = '/www/www/grape/紫葡萄资源目录整理0401.xlsx';
        //读excel表中内容生成对应数组
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($execl_path);

        $objPHPExcel->setActiveSheetIndex(0);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $data = array();
        foreach($objWorksheet->getRowIterator() as $row){
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $temp = array();
            foreach($cellIterator as $cell){
                array_push($temp,trim($cell->getValue()));
            }
            array_push($data,$temp);
        }

        $temp_cat_arr = [];
        $execl_data_list = [];
        foreach($data as $key => $row) {
            if($key == 0){ continue;}

            //分类处理
            $first_cat = trim($row[6]);
            $second_cat = trim($row[7]);
            if($first_cat != '手工'){continue;}

            if(!isset($temp_cat_arr[$first_cat])){
                $first_cat_info =  Loader::model('Category')->where('cat_name',$first_cat)->where('parent_id',0)->find();
                if(!empty($first_cat_info)){
                    //已存在一级分类,直接更新数据
                    $temp_cat_arr[$first_cat]['cat_id'] = $first_cat_info['cat_id'];
                } else {
                    //不存在一级分类，插入数据
                    $insert_data = ['cat_name' => $first_cat,'parent_id' => 0,'add_time' => time()];
                    Loader::model('Category')->insert($insert_data);
                    $temp_cat_arr[$first_cat]['cat_id'] = Loader::model('Category')->getLastInsID();
                }
            }
            $row['cat_id'] = $temp_cat_arr[$first_cat]['cat_id'];

            if(!isset($temp_cat_arr[$first_cat]['child'][$second_cat])){
                $second_cat_info =  Loader::model('Category')->where('cat_name',$second_cat)->where('parent_id',$temp_cat_arr[$first_cat]['cat_id'])->find();
                if(!empty($second_cat_info)){
                    //已存在一级分类,直接更新数据
                    $temp_cat_arr[$first_cat]['child'][$second_cat] = $second_cat_info['cat_id'];
                } else {
                    //不存在一级分类，插入数据
                    $insert_data = ['cat_name' => $second_cat,'parent_id' => $temp_cat_arr[$first_cat]['cat_id'],'add_time' => time()];
                    Loader::model('Category')->insert($insert_data);
                    $temp_cat_arr[$first_cat]['child'][$second_cat] = Loader::model('Category')->getLastInsID();
                }
            }
            $row['second_cat_id'] = $temp_cat_arr[$first_cat]['child'][$second_cat];
            //视频数据入库处理



            //视频文件对应及检查处理

            $execl_data_list[$row[3]] = $row;
        }

        $exist_file_list = [];
        foreach($file_list as $file_row) {
            $file_info = pathinfo($file_row);
            $extension = $file_info['extension'];
            if($extension != 'mp4'){
                continue;
            }
            $temp_file_name = str_replace(['.mp4','0','1','2','3','4','5','6','7','8','9','《','》'],['','','','','','','','','','','','',''],$file_info['basename']);
            echo $temp_file_name.PHP_EOL;
            if(isset($execl_data_list[$temp_file_name])){
                array_push($exist_file_list,$file_row);
            }
        }

        //print_r($temp_cat_arr);
        print_r($exist_file_list);
        echo PHP_EOL.'execl中一共有'.count($execl_data_list)."个视频";
        echo PHP_EOL.'文件中一共有'.count($file_list)."个视频";
        echo PHP_EOL.'视频中文件名和execl中文件名对应的一共有'.count($exist_file_list)."个视频";
        exit;
    }

    public function searchDir($path,&$data)
    {
        if(is_dir($path)){
            $dp = dir($path);
            while($file=$dp->read()){
                if($file!='.'&& $file != '..' && $file != '.DS_Store'){
                    $this->searchDir($path.'/'.$file,$data);
                }
            }
            $dp->close();
        }
        if(is_file($path)){
            $data[]=$path;
        }
    }


}
