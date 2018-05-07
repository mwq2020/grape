<?php
namespace app\manage\controller;
use \think\Db;
use think\Loader;

//合同管理
class Contract extends \think\Controller
{

    /**
     * 合同列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if(!empty($_REQUEST['title'])){
            $where['title'] = ['like','%'.trim($_REQUEST['title']).'%'];
        }
        if(!empty($_REQUEST['cat_id'])){
            $where['cat_id'] = intval($_REQUEST['cat_id']);
        }

        $contract_list = Loader::model('Contract')->where($where)->order('contract_id','desc')->paginate(10);
        $this->assign('contract_list', $contract_list);

        $page = $contract_list->render();
        $this->assign('page', $page);

        $this->view->engine->layout('layout');
        return $this->fetch('contract/index');
    }

    /**
     * 合同添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('contract/add');
        }

        try {
            if(empty($_REQUEST['title'])){
                throw new \Exception('合同名称不能为空');
            }
            if(empty($_REQUEST['contract_start_time'])){
                throw new \Exception('合同开始时间不能为空');
            }
            if(empty($_REQUEST['contract_end_time'])){
                throw new \Exception('合同结束时间不能为空');
            }
            if(empty($_REQUEST['customer_name'])){
                throw new \Exception('客户姓名不能为空');
            }
            if(empty($_REQUEST['order_amount'])){
                throw new \Exception('合同金额不能为空');
            }
            if(empty($_REQUEST['contract_time'])){
                throw new \Exception('合同签订时间不能为空');
            }

            $contract_img = '';
            $file = request()->file('contract_img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/contract');
                if($info){
                    $contract_img =  '/static/image/contract/'.$info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }

            //检查账户名是否已经存在
            $account_exist = Loader::model('Contract')->where('title',$_REQUEST['title'])->find();
            if(!empty($account_exist)){
                throw new \Exception('合同名称已存在，请更换');
            }

            $data = [];
            $data['title']    = $_REQUEST['title'];
            $data['contract_start_time']    = strtotime($_REQUEST['contract_start_time']);
            $data['contract_end_time']      = strtotime($_REQUEST['contract_end_time']);
            $data['customer_name']  = $_REQUEST['customer_name'];
            $data['order_amount']   = $_REQUEST['order_amount'];
            $data['contract_time']  = strtotime($_REQUEST['contract_time']);

            $data['link_person']        = $_REQUEST['link_person'];
            $data['link_person_phone']  = $_REQUEST['link_person_phone'];
            $data['contract_img']       = $contract_img;
            $data['remark']             = $_REQUEST['remark'];
            $data['add_time']           = time();
            $flag = Loader::model('Contract')->insert($data);
            if(empty($flag)){
                throw new \Exception('合同添加失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('contract/add');
        }
        return $this->redirect('/manage/contract/index');
    }

    /**
     * 管理员修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $contract_info = Loader::model('Contract')->find($_REQUEST['contract_id']);
        $this->assign('contract_info',$contract_info);
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('contract/edit');
        }

        try {
            if(empty($_REQUEST['title'])){
                throw new \Exception('合同名称不能为空');
            }
            if(empty($_REQUEST['contract_start_time'])){
                throw new \Exception('合同开始时间不能为空');
            }
            if(empty($_REQUEST['contract_end_time'])){
                throw new \Exception('合同结束时间不能为空');
            }
            if(empty($_REQUEST['customer_name'])){
                throw new \Exception('客户姓名不能为空');
            }
            if(empty($_REQUEST['order_amount'])){
                throw new \Exception('合同金额不能为空');
            }
            if(empty($_REQUEST['contract_time'])){
                throw new \Exception('合同签订时间不能为空');
            }

            $contract_img = '';
            $file = request()->file('contract_img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'image/contract');
                if($info){
                    $contract_img =  '/static/image/contract/'.$info->getSaveName();
                }else{
                    throw new \Exception('图片保存失败【'.$file->getError().'】');
                }
            }

            //检查账户名是否已经存在
            $map = [];
            $map['contract_id'] = ['<>',$_REQUEST['contract_id']];
            $map['title'] = $_REQUEST['title'];
            $account_exist = Loader::model('Contract')->where($map)->find();
            if(!empty($account_exist)){
                throw new \Exception('合同名已存在，请更换');
            }

            $contract_info->title    = $_REQUEST['title'];
            $contract_info->contract_start_time     = strtotime($_REQUEST['contract_start_time']);
            $contract_info->contract_end_time       = strtotime($_REQUEST['contract_end_time']);

            $contract_info->customer_name   = $_REQUEST['customer_name'];
            $contract_info->order_amount    = intval($_REQUEST['order_amount']);
            $contract_info->contract_time   = strtotime($_REQUEST['contract_time']);

            $contract_info->link_person         = $_REQUEST['link_person'];
            $contract_info->link_person_phone   = $_REQUEST['link_person_phone'];
            $contract_info->remark              = $_REQUEST['remark'];
            if(!empty($contract_img)){
                $contract_info->contract_img = $contract_img;
            }

            $flag = $contract_info->save();
            if(empty($flag)){
                throw new \Exception('合同修改失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('contract/edit');
        }
        return $this->redirect('/manage/contract/index');
    }

}