<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class CheckUserSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('admin_user')) {
		$request->session()->flash('msg', 'Please log in first to access the dashboard.');
		$request->session()->flash('msg_class', 'danger');
		return redirect()->route('admin.login');
	}

        return $next($request);
    }
}
