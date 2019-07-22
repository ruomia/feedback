<?php
namespace app\admin\model;


class Module extends Backend
{
    protected $table = 'module';

    public static function getLists($where=[], $order=[])
    {
        
        $lists = self::withSearch(['name'], $where)
                    ->select()
                    ->toArray();
        $lists = generateTree($lists);

        $res['total'] = count($lists);
        $res['list'] = $lists;
        return $res;
    }
}