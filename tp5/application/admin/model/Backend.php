<?php
namespace app\admin\model;

use think\Model;
use think\Session;

class Backend extends Model
{
    
    public function searchNameAttr($query, $value, $data)
    {
        if(!empty($value)) {
            $query->where('name','like', $value . '%');               
        }
    }
    public function searchStatusAttr($query, $value, $data)
    {
        if(!empty($value)){
            $query->where('status', '=', intval($value));
        }
    }

    
}