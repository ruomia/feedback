<?php
namespace app\admin\controller;

use app\admin\controller\Backend;
use think\Validate;
use think\facade\Request;
use app\admin\model\Problem as ProblemModel;
use app\common\vo\ResultVo;
use app\common\enums\ErrorCode;

 class Problem extends Backend
 {
    public function index()
    {
        $where = [];
        $order = 'id ASC';
        $status = Request::get('status', '');
        if ($status !== ''){
            $where[] = ['problem.status','=',intval($status)];
            $order = '';
        }
        $path = Request::get('path', '');
        if ($path !== ''){
            $where[] = ['problem.path','=', $path];
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
        trace($where, 'tarace where');
        $res = ProblemModel::getLists($where, $order);
       
        return ResultVo::success($res);
    }
    /**
     * 添加
     */
    public function add()
    {
        $params = Request::post();
        // $validate = Validate::make([
        //     'name|名称' => 'require|unique:module,name',
        // ]);
        // $result = $validate->check($params);
        // if(!$result) {
        //     return error($validate->getError());
        // }
        $params['admin_id'] = $this->request->admin_id;
        // $images = '';
        // foreach($params['images'] as $k => $v) {
        //     $images .= $v['url'];
        // }
        $params['images'] = implode(',', array_column($params['images'], 'url'));
        // $params
        $result = ProblemModel::create($params);
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
        // 超级管理员组不允许修改
        // $validate = Validate::make([
        //     'pid|上级模块' => 'require',
        //     'name|名称' => 'require|unique:module,name,' . $row->id,
        // ]);
        // if(!$validate->check($params)){
        //     return ResultVo::error(ErrorCode::DATA_NOT, $validate->getError());
        // }
        $row->path = $params['path'];
        $row->content = $params['content'];
        $row->programme = $params['programme'];
        $row->type_id = $params['type_id'];
        $row->link = $params['link'];
        $row->remark = $params['remark'];
        $row->status = $params['status'];
        $row->images = implode(',', array_column($params['images'], 'url'));
        $row->serious = $params['serious'];
        $row->weigh = $params['weigh'];
        $result = $row->save();

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

  
}
