<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

//统计管理
class Statistics extends Base
{

    public function index()
    {
        $customer_list = Loader::model('Customer')->getCustomerSelectList();
        $this->assign('customer_list', $customer_list);

        $this->view->engine->layout('layout');
        return $this->fetch('statistics/index');
    }


    public function report_data()
    {
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 1;
        $customer_id = isset($_REQUEST['customer_id']) ? intval($_REQUEST['customer_id']) : 0;

        $is_custom_time = false;
        $is_show_day = true;
        //如果时间不为空
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']);
            $end_time = strtotime($_REQUEST['end_time']);
            if($end_time - $start_time <= 24*3600){
                $is_show_day = true;
            } else {
                $end_time += 24*3600;
            }
            $is_custom_time = true;
        }

        if($is_custom_time == false){
            if($type == 1){ //当天
                $start_time = strtotime(date('Y-m-d 00:00:00'));
                $end_time = strtotime(date('Y-m-d 23:59:59'));
                $is_show_day = false;
            } elseif($type == 2){//昨天
                $start_time = strtotime(date('Y-m-d 00:00:00'))-24*3600;
                $end_time = strtotime(date('Y-m-d 23:59:59'))-24*3600;
                $is_show_day = false;
            }elseif($type == 3){//本周
                $start_time = strtotime(date('Y-m-d 00:00:0',strtotime('this week')));
                $end_time = $start_time + (7*24*3600) -1;
            } elseif($type == 4){//上周
                $start_time = strtotime(date('Y-m-d 00:00:0',strtotime('this week')))- (7*24*3600);
                $end_time = $start_time + (7*24*3600) -1;
            } elseif($type == 5){//本月
                $start_time = strtotime(date('Y-m-01 00:00:00'));
                $end_time = strtotime(date('Y-m-d 23:59:59',$start_time)." +1 month -1 day");
            }elseif($type == 6){//上月
                $start_time = strtotime(date('Y-m-01', strtotime('-1 month')));
                $end_time = strtotime(date('Y-m-t 23:59:59', strtotime('-1 month')));
            }else {
                $start_time = strtotime(date('Y-m-d 00:00:00'));
                $end_time = strtotime(date('Y-m-d 23:59:59'));
            }
        }

        //查询日志数据
        $query = Db::table('view_log')->where('add_time','>',$start_time)->where('add_time','<',$end_time)->field('customer_id,ip,add_time');
        if(!empty($customer_id)){
            $query->where(['customer_id' => $customer_id]);
        }
        $log_list = $query->select();


        //初始化返回数据
        $return_data = [
            'labels' => [],//用于对应数据的key
            'labels_key' => [],//用于显示的值
            'data'=>[
                [
                    'data'=>[],
                    'markPoint' => ['data'=> null],
                    'name'=>'独立ip',
                    'type'=>'line'
                ],
                [
                    'data'=>[],
                    'markPoint' => ['data'=> null],
                    'name'=>'访问量',
                    'type'=>'line'
                ]
            ]
        ];


        if($is_show_day == false){ //当天[以小时为单位]
            for($i=0; $i<24; $i++){
                $return_data['labels_key'][] = $i;
                $return_data['labels'][$i] = date('Y-m-d '.$i, $start_time);
            }

            $view_temp = [];
            $ip_temp= [];
            if($log_list){
                foreach($log_list as $row){
                    //访问量统计
                    $key = date('H',$row['add_time']);
                    if(isset($view_temp[$key])) {
                        $view_temp[$key]++;
                    } else {
                        $view_temp[$key] = 1;
                    }

                    //ip统计
                    if(!isset($ip_temp[$key][$row['ip']])){
                        $ip_temp[$key][$row['ip']] = $row['ip'];
                    }
                }
            }

            foreach($return_data['labels_key'] as $row){
                //ip数据
                $return_data['data'][0]['data'][] = isset($ip_temp[$row]) ? count($ip_temp[$row]) : 0;
                //访问量数据
                $return_data['data'][1]['data'][] = isset($view_temp[$row]) ? $view_temp[$row] : 0;
            }
        } else {
            for($i=$start_time;$i<$end_time;$i += 24*3600){
                $return_data['labels_key'][$i] = strtotime(date('Y-m-d',$i));
                $return_data['labels'][] = date('Y-m-d',$i);
            }

            $view_temp = [];
            $ip_temp= [];
            if($log_list){
                foreach($log_list as $row){
                    //访问量统计
                    $key = strtotime(date('Y-m-d',$row['add_time']));
                    if(isset($view_temp[$key])) {
                        $view_temp[$key]++;
                    } else {
                        $view_temp[$key] = 1;
                    }

                    //ip统计
                    if(!isset($ip_temp[$key][$row['ip']])){
                        $ip_temp[$key][$row['ip']] = $row['ip'];
                    }
                }
            }

            foreach($return_data['labels_key'] as $row){
                //ip数据
                $return_data['data'][0]['data'][] = isset($ip_temp[$row]) ? count($ip_temp[$row]) : 0;
                //访问量数据
                $return_data['data'][1]['data'][] = isset($view_temp[$row]) ? $view_temp[$row] : 0;
            }
        }

        exit(json_encode($return_data));
    }

}