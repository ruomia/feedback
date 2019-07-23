<?php
namespace app\admin\model;

use think\Model;

class Problem extends Model
{
    protected $table = 'problem';
    protected $autoWriteTimestamp = 'datetime';

    public static function getLists($where=[], $order=[], $page=1, $limit=10)
    {
        
        $res = self::with('logs')
                    ->field('problem.*,admin.nickname,admin.department')
                    ->join('admin', 'problem.admin_id = admin.id')
                    ->where($where)
                    ->order($order)
                    ->page($page)
                    ->limit($limit)
                    ->select();
        return $res;
    }
    public function logs()
    {
        return $this->hasMany('ProblemLog');
    }
}