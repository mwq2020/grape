<?php
namespace app\admin\controller;
use \think\Db;
use think\Loader;

//域名管理
class Domain extends Base
{

    /**
     * 域名列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if(!empty($_REQUEST['company_name'])){
            $where['company_name'] = ['like','%'.trim($_REQUEST['company_name']).'%'];
        }
        if(!empty($_REQUEST['copyright_year'])){
            $where['copyright_year'] = ['like','%'.trim($_REQUEST['copyright_year']).'%'];
        }
        if(!empty($_REQUEST['industry_record'])){
            $where['industry_record'] = ['like','%'.trim($_REQUEST['industry_record']).'%'];
        }
        if(!empty($_REQUEST['police_record'])){
            $where['police_record'] = ['like','%'.trim($_REQUEST['police_record']).'%'];
        }

        $domain_list = Loader::model('Domain')->where($where)->order('domain_id','desc')->paginate(10,false,['query' => $_GET]);
        $this->assign('domain_list', $domain_list);

        $this->view->engine->layout('layout');
        return $this->fetch('domain/index');
    }

    /**
     * 域名添加
     * @return mixed
     */
    public function add()
    {
        $error_msg = '';
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('domain/add');
        }

        try {
            if(empty($_REQUEST['company_name'])){
                throw new \Exception('公司名不能为空');
            }
            if(empty($_REQUEST['copyright_year'])){
                throw new \Exception('时间不能为空');
            }
            if(empty($_REQUEST['industry_record'])){
                throw new \Exception('工信部备案号不能为空');
            }
            if(empty($_REQUEST['industry_url'])){
                throw new \Exception('工信部备案链接');
            }
            if(empty($_REQUEST['police_record'])){
                throw new \Exception('公安网备号不能为空');
            }
            if(empty($_REQUEST['police_url'])){
                throw new \Exception('公安网备链接不能为空');
            }

            //检查账户名是否已经存在
            $account_exist = Loader::model('Domain')->where('company_name',$_REQUEST['company_name'])->find();
            if(!empty($account_exist)){
                throw new \Exception('公司名已存在，请更换');
            }

            $data = [];
            $data['company_name']    = $_REQUEST['company_name'];
            $data['copyright_year']  = $_REQUEST['copyright_year'];
            $data['industry_record'] = $_REQUEST['industry_record'];
            $data['industry_url']       = $_REQUEST['industry_url'];
            $data['police_record']      = $_REQUEST['police_record'];
            $data['police_url']         = $_REQUEST['police_url'];
            $data['status']             = intval($_REQUEST['status']);
            $data['add_time']           = time();
            $data['update_time']        = time();
            $flag = Loader::model('Domain')->insert($data);
            if(empty($flag)){
                throw new \Exception('域名添加失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('domain/add');
        }
        return $this->redirect('/admin/domain/index');
    }

    /**
     * 管理员修改
     * @return mixed
     */
    public function edit()
    {
        $error_msg = '';
        $domain_info = Loader::model('Domain')->find($_REQUEST['domain_id']);
        $this->assign('domain_info',$domain_info);
        if(empty($_POST)){
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('domain/edit');
        }

        try {
            if(empty($_REQUEST['company_name'])){
                throw new \Exception('公司名不能为空');
            }
            if(empty($_REQUEST['copyright_year'])){
                throw new \Exception('时间不能为空');
            }
            if(empty($_REQUEST['industry_record'])){
                throw new \Exception('工信部备案号不能为空');
            }
            if(empty($_REQUEST['industry_url'])){
                throw new \Exception('工信部备案链接');
            }
            if(empty($_REQUEST['police_record'])){
                throw new \Exception('公安网备号不能为空');
            }
            if(empty($_REQUEST['police_url'])){
                throw new \Exception('公安网备链接不能为空');
            }

            //检查账户名是否已经存在
            $map = [];
            $map['domain_id'] = ['<>',$_REQUEST['domain_id']];
            $map['company_name'] = $_REQUEST['company_name'];
            $domain_exist = Loader::model('Domain')->where($map)->find();
            if(!empty($domain_exist)){
                throw new \Exception('域名已存在，请更换');
            }

            $domain_info->company_name      = $_REQUEST['company_name'];
            $domain_info->copyright_year    = $_REQUEST['copyright_year'];
            $domain_info->industry_record   = $_REQUEST['industry_record'];
            $domain_info->industry_url      = $_REQUEST['industry_url'];
            $domain_info->police_record     = $_REQUEST['police_record'];
            $domain_info->police_url        = $_REQUEST['police_url'];
            $domain_info->status            = intval($_REQUEST['status']);
            $domain_info->update_time       = time();
            $flag = $domain_info->save();
            if(empty($flag)){
                throw new \Exception('域名修改失败');
            }
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
            $this->view->engine->layout('layout');
            return $this->fetch('domain/edit');
        }
        return $this->redirect('/admin/domain/index');
    }


    /**
     * 修改域名状态
     */
    public function change_status()
    {
        try {
            if(!isset($_REQUEST['status']) || empty($_REQUEST['domain_id'])){
                throw new \Exception('入参错误');
            }
            $domain_info = Loader::model('Domain')->where('domain_id',$_REQUEST['domain_id'])->find();
            if(empty($domain_info)){
                throw new \Exception('域名不存在');
            }
            $domain_info->status = intval($_REQUEST['status']);
            $domain_info->save();
        } catch (\Exception $e){
            $error_msg = $e->getMessage();
            $this->assign('error_msg',$error_msg);
        }
        return $this->redirect('/admin/domain/index');
    }

}