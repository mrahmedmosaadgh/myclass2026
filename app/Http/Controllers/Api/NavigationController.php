<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NavigationController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $isV2 = $request->query('v2', false);

        // 1. Fetch active menus ordered by hierarchy
        $query = Menu::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            }])
            ->orderBy('order');

        if ($isV2) {
            $query->where('v2_enabled', true);
            if ($role) {
                $query->where(function($q) use ($role) {
                    $q->where('role_specific', $role)
                      ->orWhereNull('role_specific');
                });
            }
        }

        $menus = $query->get();

        // 2. Filter by permissions (Server-side authority)
        $filteredMenus = $menus->filter(function ($menu) {
            return $this->userCanAccessParams($menu);
        })->map(function ($menu) {
            $menu->children = $menu->children->filter(function ($child) {
                return $this->userCanAccessParams($child);
            })->values();
            return $menu;
        })->values();

        return response()->json([
            'data' => $filteredMenus,
            'version' => md5($filteredMenus->toJson()), // simple versioning
        ]);
    }

    private function userCanAccessParams($menu)
    {
        if (empty($menu->permission)) {
            return true;
        }
        return Auth::user() ? Auth::user()->can($menu->permission) : false;
    }
}
