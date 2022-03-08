<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;

class WebHook
{
    /**
     * Handle an incoming request.
     *[]
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        
        $from = $request->from;
        $msg = strtolower($request->message);
        $chat = ['info','check license','activate domain','deactivate domain'];
        if(License::whereCustomerMobile($from)->count() < 1 && in_array($msg,$chat)){
            return redirect('notRegistered');
        } 
        return $next($request);
    }
}
