<?php

namespace app\admin\validate;

use think\Validate;

class Module extends Validate
{
    protected $rule =   [
        'name' => 'require'
    ];
    
    protected $message  =   [
        'name.require' => '名称必须',
    ];
}