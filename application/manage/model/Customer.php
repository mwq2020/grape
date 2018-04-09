<?php

namespace app\manage\model;

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

}
