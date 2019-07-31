<?php
namespace app\admin\controller;

use app\admin\controller\Backend;
use think\Validate;
use think\facade\Request;
use app\admin\model\Problem as ProblemModel;
use app\admin\model\ProblemLog;
use app\common\vo\ResultVo;
use app\common\enums\ErrorCode;
use Db;

 class Problem extends Backend
 {
    public function index()
    {
        $where = [];
        $order = 'id DESC';
        $status = Request::get('status', '');
        if ($status !== ''){
            $where[] = ['problem.status','=',intval($status)];
            $order = '';
        }
        $path = Request::get('path', '');
        if ($path !== ''){
            $where[] = ['problem.path','in', $path];
            $order = '';
        }

        $type_id = Request::get('type_id', '');
        if (!empty($type_id)){
            $where[] = ['type_id', 'like', $type_id];
            $order = '';
        }
        $time = Request::get('time', []);
        if (count($time) !== 0){
            // where('create_time', 'between time', ['2015-1-1', '2016-1-1']);
            $timeType = Request::get('timeType','0');
            $timeType = $timeType === '0' ? 'create_time' : 'update_time';

            $where[] = ['problem.' . $timeType, 'between time', $time];
            $order = '';
        }
        // trace($where, 'tarace where');
        $page = Request::get('page', 1);
        $limit = Request::get('limit', 10);

        $list = $this->model::with(['logs'=>function($query){
                        $query->order('id desc');
                    }])
                    ->field('problem.*,admin.nickname,admin.department')
                    ->join('admin', 'problem.admin_id = admin.id')
                    ->where($where)
                    ->order($order)
                    ->page($page)
                    ->limit($limit)
                    ->select();

        $total = $this->model::where($where)
                    ->order($order)
                    ->count();
        $result = array("total" => $total, "list" => $list);

        return ResultVo::success($result);
    }
    /**
     * 添加
     */
    public function add()
    {
        $params = Request::post();
        $params['admin_id'] = $this->request->admin_id;
     
        $params['images'] = implode(',', array_column($params['images'], 'url'));
        // $params
        $result = ProblemModel::create($params);
        $result['nickname'] = $this->request->nickname;
        $result['department'] =  $this->request->department;
        // trace($result, 'result');
        return ResultVo::success($result);
    }

    /**
     * 编辑
     */
    public function edit() 
    {
        $params = Request::post();
        if (empty($params['id'])){
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL);
        }
        $id = $params['id'];
        $row = ProblemModel::get($id);
        if (!$row)
            return ResultVo::error(ErrorCode::DATA_NOT, "类型不存在");
        
        if(!empty($params['images'])) {
            $params['images'] = implode(',', array_column($params['images'], 'url')) ;

        }
        $result = $row->save($params);

        if (!$result){
            return ResultVo::error(ErrorCode::DATA_CHANGE);
        }
    }
    /**
     * 删除
     */
    public function delete(){
        $id = Request::post('id/d');
        if (empty($id)){
            return ResultVo::error(ErrorCode::DATA_VALIDATE_FAIL);
        }
        if (!ProblemModel::where('id',$id)->delete()){
            return ResultVo::error(ErrorCode::NOT_NETWORK);
        }
        return ResultVo::success();
    }
    
    // 添加日志
    public function log(){
        $params = Request::post();
        if($params) {
            $params['nickname'] = $this->request->nickname;
            $params['create_time'] = date("Y-m-d H:i:s");

            $result = ProblemLog::create($params);

            if(!$result) {
                return ResultVo::error(ErrorCode::NOT_NETWORK);
            }
            return ResultVo::success($result);

        }
        return ResultVo::error(ErrorCode::NOT_NETWORK, '数据不能为空');
      
    }
  
}
