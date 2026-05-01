<?php

namespace App\Http\Middleware;

use Closure;

class BusinessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if(!$user->companyCardStatus && $user->carte->cid <=0 ){
            abort(403, '请升级企业会员或绑定公司');
        }
        return $next($request);
    }
}
