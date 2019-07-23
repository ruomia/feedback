<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\enums\ErrorCode;
use app\common\utils\PublicFileUtils;
use app\common\vo\ResultVo;
use think\facade\Env;
use think\File;
use think\Controller;
use think\Request;

/**
 * 上传文件（管理文件的）
 * Class UploadFile
 * @package app\admin\controller
 */
class Upload extends Backend
{
    protected $middleware = [
        'CORS'
    ];

    /**
     * 上传token
     */
    public function qiuNiuUpToken()
    {

        $res = [];
        $res["upload_url"] = PublicFileUtils::getUploadBaseUrl();
        $res["up_token"] = "xxxxxxxx";
        $res["domain"] = PublicFileUtils::getDomainBaseUrl();

        return ResultVo::success($res);
    }


    public function save(Request $request)
    {
        $file = $request->file('file');
        trace($file, 'trace file');
        // 移动到框架应用根目录/uploads/目录下
        $info = $file->move('uploads');
        trace(Env::get('app.domain'), 'domain');
        if($info){
            // 成功上传后，获取上传信息
            $result =  [
                'code' => 0,
                'msg'  => '',
                'data' => [
                    'src' => Env::get('app.domain') . 'uploads/' . $info->getSaveName()
                ]
            ];
            return json($result);
        }else{
            $result =  [
                'code' => 1,
                'msg'  => $file->getError(),
                'data' => ''
            ];
            return json($result);
        }
    }

    public function delete(Request $request)
    {
        preg_match('/(uploads.*)/', $request->url, $url);
        @unlink($url[1]);
        return json([
            'msg' => '删除成功'
        ]);
    }

}