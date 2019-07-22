<?php
namespace app\admin\model;

use think\Model;

class Problem extends Model
{
    protected $table = 'problem';
    protected $autoWriteTimestamp = 'datetime';

    public static function getLists($where=[], $order=[])
    {
        
        $lists = self::field('problem.*,admin.nickname,admin.department')
                    ->join('admin', 'problem.admin_id = admin.id')
                    ->where($where)
                    ->order($order)
                    ->select();
                    // ->toArray();
        // $lists = generateTree($lists);

        $res['total'] = count($lists);
        $res['list'] = $lists;
        return $res;
    }
}