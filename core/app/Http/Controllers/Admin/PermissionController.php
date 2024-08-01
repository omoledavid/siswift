<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PermissionController extends Controller
{

    public function index()
    {
        $pageTitle = 'All Permissions';
        $permissions = Permission::all()->groupBy('group');
        $routes = $this->getPermissions();
        return view('admin.permission.index', compact('permissions', 'pageTitle', 'routes'));
    }

    public function updatePermissions(Request $request)
    {

        $request->validate([
            'permission' => 'nullable|array',
            'permission.*.id' => 'required|integer|gt:0',
            'permission.*.name' => 'required|string'
        ]);

        foreach ($request->permission as $permission) {
            $permission = Permission::where('id', $permission['id'])->update(['name' => $permission['name']]);
        }

        $notify[] = ['success', 'Updated successfully'];
        return back()->withNotify($notify);
    }

    public function getPermissions()
    {
        $excludedControllers = ['LoginController', 'ForgotPasswordController', 'ResetPasswordController', 'PermissionController', 'CashierController', 'PosController', 'CleanerController', 'AdminController@profile', 'AdminController@profileUpdate', 'AdminController@password', 'AdminController@passwordUpdate'];

        return collect(collect(Route::getRoutes())
            ->filter(function ($route) use ($excludedControllers) {
                return str_starts_with($route->getName(), 'admin.') && !in_array(last(array_reverse(explode('@', class_basename($route->getAction('controller'))))), $excludedControllers) && !in_array(class_basename($route->getAction('controller')), $excludedControllers) && $route->getName() != 'admin.clear.cache';
            })
            ->map(function ($route) {
                $controller = explode('@', class_basename($route->getActionName()));
                return [
                    'name' => $route->getName(),
                    'method' => @array_shift($route->methods),
                    'controller' => @$controller[0],
                    'action' => last($controller)
                ];
            }));
    }
}
