<?php
namespace app\admin\controller;

use think\Controller;
use think\Validate;
use think\Request;
use app\facade\Admin;
use app\admin\model\Admin as AdminModel;
use app\admin\model\AuthRoleAdmin;
use app\admin\model\AuthRule;
use app\admin\model\AuthRuleRole;
use app\common\vo\ResultVo;

/**
 * 后台首页
 * @internal
 */

 class Index extends Controller
 {
    protected $middleware = [
        'CORS',
        'Check' => ['except' => ['login'] ]
    ];

    
    // protected $noNeedLogin = ['login'];
    /**
     * 后台首页
     */

    public function index()
    {
    }

    /**
     * 获取用户信息和权限列表
     */
    public function userinfo()
    {
        // 权限信息
        $admin_id = $this->request->admin_id;
        
        $admin = AdminModel::where('id', $admin_id)
                    ->field('id,username,avatar')
                    ->find();
        return success([
            'username'=> $admin->username,
            'avatar'=> $admin->avatar,
            'authRules'=>$this->request->authRules
        ]);
    }

    /**
     * 管理员登录
     */
    public function login(Request $req)
    {
        // $url = Request::get('url');
        // echo $url;
        if ($req->isPost()) {
            $username = $req->post('username');
            $password = $req->post('password');
            // $captcha = Request::post('captcha');
            $validate = Validate::make([
                'username|用户名' => 'require|length:2,20',
                'password|密码' => 'require|length:6,30',
                // 'captcha|验证码'  => 'require|captcha', 
            ]);
            $data = [
                'username' => $username,
                'password' => $password,
                // 'captcha'  => $captcha,
            ];
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError());
            }
            $result = Admin::login($username, $password);
            if ($result) {
              
                // 发给前端
                return ResultVo::success($result);
            } 
            else 
            {
                $msg = Admin::getError();
                $msg = $msg ? $msg : ('Username or password is incorrect');
                // $this->error($msg);
                return error($msg);
            }

        }
    }

    /**
     * 注销登录 
     */
    public function logout()
    {

    }
}
