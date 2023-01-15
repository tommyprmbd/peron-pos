<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new RoleService;    
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $model = Role::orderBy('id', 'desc')->get();
            return DataTables::of($model)
                ->addIndexColumn()
                ->addColumn('action', function($model){
                    if ($model->name != RoleService::SUPER_ADMIN) {
                        return view('partials.action', [
                            'model' => $model,
                            'route' => 'role'
                        ]);
                    }
                })
                ->make();
        }

        return view('role.index', [
            'model' => $this->service::GUARDS
        ]);
    }

    public function store(RoleRequest $request)
    {
        if (!$request->ajax())
            return response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);

        $validated = $request->validated();

        return response()->json($this->service->create($validated, new Role()));
    }

    public function update(RoleRequest $request, Role $role)
    {
        if (!$request->ajax())
            return response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);

        $validated = $request->validated();

        return response()->json($this->service->update($validated, $role));
    }

    public function destroy(Role $role)
    {
        return response()->json($this->service->destroy($role));
    }

    public function permission(int $id = null)
    {
        // listing all permissions
        $permissions = Permission::orderBy('id')->pluck('name', 'id');
        $permissionsX = [];
        foreach ($permissions as $key => $value) {
            $permissionsX[$key] = [
                'name' => $value,
                'checked' => false
            ];
        }

        if ($id != null) {
            $role = Role::findById($id);
            $roles = $role->getAllPermissions()->toArray();
            
            if (!empty($roles)) {
                foreach ($roles as $key => $value) {
                    if (array_key_exists($value['id'], $permissionsX)) {
                        $permissionsX[$value['id']]['checked'] = true;
                    }
                }
            }
        }
        return json_encode($permissionsX);
    }
}
