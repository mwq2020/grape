<?php
namespace app\index\controller;
use \think\Db;
use think\Loader;
use PHPExcel_IOFactory;
use PHPExcel;


class Test extends \think\Controller
{
    public $file_list = [];

    public function login(){

        try {
            $param = [];
            $param['strUserName'] = '080812300021';
            $param['strUserPin'] = 751008;
            $param['strAutherizeKey'] = '';

            libxml_disable_entity_loader(false);
            $client = new \SoapClient('http://58.60.1.135:8085/service.asmx?WSDL');
            $ret = $client->Login($param);
            echo "<pre>";
            print_r($ret);


//            $client = new \SoapClient('http://58.60.1.135:8085/service.asmx?op=Login');
//            $result =  $client->__soapCall('Login', $param);
//            echo "<pre>";
//            print_r($result);

        } catch (\Exception $e){
            echo "<pre>";
            echo ''.$e;
        }

    }
    /**
     * 导出execl
     * @throws \PHPExcel_Reader_Exception
     */
    public function index()
    {
        $video_num = 0;
        $image_num = 0;
        $video_list = Loader::model('Video')->select();
        foreach($video_list as $row){
            $video_img = "E:/website/grape/public".iconv("UTF-8","GBK",$row['video_img']);
            if(!file_exists($video_img)){
                echo "视频id:{$row['video_id']}   {$row['video_img']} 封面不存在<br>";
                $image_num ++;
            }
            $video_url = "E:/website/grape/public".iconv("UTF-8","GBK",$row['video_url']);
            if(!file_exists($video_url)){
                $video_num++;
                echo "视频id:{$row['video_id']}   {$row['video_url']} 视频不存在<br>";
            }
        }
        echo "一共{$image_num}个视频封面不存在!<br>";
        echo "一共{$video_num}个视频视频不存在!<br>";
        exit;
    }


    public function mwq()
    {
        echo "this is a test page";
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


    /**
     * 读取execl内容
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * CLI 访问  php public/index.php index/test/read_execl
     */
    public function import_videos()
    {
        exit;
        echo "<pre>";
        $video_dir = 'E:/website/grape/public/static/video/美术798';
        //$video_dir = '/www/www/grape/public/static/*';
        //echo $video_dir."<br>";

//        $file_list = [];
//        $this->searchDir($video_dir,$file_list);
//        echo "<pre>";
//        print_r($file_list);
//        exit;


        $execl_path = 'E:/website/grape/紫葡萄文件列表整理0410new.xlsx';
        $execl_path = iconv("UTF-8","GBK",$execl_path);
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

//        print_r($data);
//        exit;


        $temp_cat_arr = [];
        $execl_data_list = [];
        $video_data_list = [];
        foreach($data as $key => $row) {
            if($key == 0){ continue;}

            //分类处理
            $first_cat = trim($row[4]);
            $second_cat = trim($row[5]);
            if($first_cat != '民间艺术'){continue;}

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

            $row[8] = str_replace(' ','',$row[8]);
            $row[8] = str_replace('郭老师资源1827/','',$row[8]);
            if(strpos($row[8],'国画132') !== false){
                $row[8] = '美术798/'.$row[8];
            } elseif(strpos($row[8],'色彩儿童画173') !== false){
                $row[8] = '美术798/'.$row[8];
            }

            //视频文件对应及检查处理
            $video_dir = iconv("UTF-8","GBK",'E:/website/grape/public/static/video/'.$row[8]);
            if(file_exists($video_dir)){
                $row['file_status'] = '存在';
            } else {
                $row['file_status'] = '不存在';
                echo $row[8]."【视频不存在】<br>";
            }
            $video_face_dir = iconv("UTF-8","GBK",str_replace('mp4','jpg','E:/website/grape/public/static/video/'.$row[8]));
            if(file_exists($video_face_dir)){
                $row['image_file_status'] = '存在';
            } else {
                $row['image_file_status'] = '不存在';
                echo $row[8]."【视频封面不存在】<br>";
            }

            $execl_data_list[$row[3]] = $row;

            $video_data = [];
            $video_data['video_sn'] = $row[0];
            $video_data['cat_id'] = $row['cat_id'];
            $video_data['second_cat_id'] = $row['second_cat_id'];
            $video_data['title'] = $row[6];
            $video_data['video_img'] = '/static/video/'.str_replace('mp4','jpg',$row[8]);
            $video_data['video_url'] = '/static/video/'.$row[8];
            $video_data['mark'] = $row[13];
            $video_data['series_video_desc'] = $row[12];
            $video_data['supplier_name'] = $row[14];
            $video_data['status'] = 1;
//            $flag = Loader::model('Video')->insert($video_data);
//            if(empty($flag)){
//                echo $video_data['video_url']."插入数据库失败<br>";
//            }
            $video_data_list[] = $video_data;
        }

        print_r($video_data_list);
        exit;



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


    public function cate_data()
    {
        $res = Db::table('video')->where(['cat_id' => 4,'status'=>1])->order('video_sn asc')->field('video_id,video_sn,title')->select();
        echo "<pre>";
        print_r($res);
        exit;

    }

    public function import_video()
    {
        // 34 围棋 35 象棋

        echo "<pre>导入文件开始<br>";
        $dir = "E:/website/grape/public/static/video/棋类/象棋/*";
        $win_dir = iconv("UTF-8","GBK",$dir);
        foreach(glob($win_dir) as $row){
            $file_name = str_replace('E:/website/grape/public','',iconv("GBK","UTF-8",$row));

            $file_info = pathinfo($file_name);
            if($file_info['extension'] == 'jpg'){
                continue;
            }


            echo "视频文件地址：".$file_name."<br>";
            echo "封面文件地址：".str_replace('.mp4','.jpg',$file_name)."<br>";

            $video_data = [];
            $video_data['video_sn'] = '';
            $video_data['cat_id'] = 6;
            $video_data['second_cat_id'] = 35;
            $video_data['title'] = basename(str_replace('.mp4','',$file_name));
            $video_data['video_url'] = $file_name;
            $video_data['video_img'] = str_replace('.mp4','.jpg',$file_name);
            $video_data['mark'] = '象棋';
            $video_data['series_video_desc'] = '';
            $video_data['supplier_name'] = 'admin';
            $video_data['status'] = 1;
            $video_data['add_time'] = time();
            $video_data['update_time'] = time();
            print_r($video_data);
//            $flag = Loader::model('Video')->insert($video_data);
//            if(empty($flag)){
//                echo $video_data['video_url']."插入数据库失败<br>";
//            } else{
//                echo '<hr>'.$video_data['video_url']."插入数据库成功<br>";
//            }

        }
    }


}
