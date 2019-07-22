<?php

namespace app\http\middleware;

use \Firebase\JWT\JWT as JWTCHECK;
use think\facade\Env;
class Check
{
    public function handle($request, \Closure $next)
    {
        /* 说明：客户端在提交令牌时，是把令牌放到 http 协议头中（不是表单中！！）
                并且 JWT 规定前7个字符必须是 bearer （后面这里有个空格）
            HTTP_AUTHORIZATION: bearer fdkl;ajsf;dsajlfjl;jxxxxx
            所以我们在获取令牌时，要从 $_SERVER 中获取，不是 $_POST
            在 Laravel 中要获取 $_SERVER 使用  $request->server 函数
        */
        // 从协议头是取出令牌
        $jwt = substr($request->server('HTTP_AUTHORIZATION'), 7);
        try
        {
            $jwt = JWTCHECK::decode($jwt, Env::get('jwt.key'), array('HS256'));
            
            $request->admin_id = $jwt->id;
            $request->authRules = $jwt->authRules;
        }
        catch(\Exception $e)
        {
            return json([
                'code'=> 2,
                // 'message'=>'HTTP/1.1 403 Forbidden'
                'message' => $e->getMessage()
            ]);
        }
        // if($jwt->authRules[0] !== 'admin') {
        //     $controllerName = strtolower($request->controller());
        //     $actionName = strtolower($request->action());
            
        //     $path = str_replace('.', '/', $controllerName) . '/' . $actionName;
        //     // trace($path, 'core path');
        //     // trace($jwt->authRules, 'jwt_authRules');
        //     $rules = array_merge($jwt->authRules, ['index/userinfo']);
        //     if(!in_array($path, $rules)){
        //         return json([
        //             'code'=> 2,
        //             // 'message'=>'HTTP/1.1 403 Forbidden'
        //             'message' => ''
        //         ]);
        //     }
        // }
        
        return $next($request);

    }
}
