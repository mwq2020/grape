<?php

namespace app\admin\model;

use think\Model;

class Customer extends Model
{
    protected $pk = 'customer_id';
    protected $table = 'customer';

    public function getCustomerSelectList()
    {
        $list = Customer::select();
        return $list;
    }

    public function getTypeAttr($value)
    {
        $status = [0=>'试用客户',1=>'正式客户'];
        return $status[$value];
    }

}
