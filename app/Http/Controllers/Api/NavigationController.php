<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index(Request $request)
    {
        $role = $request->query('role');
        $isV2 = $request->query('v2', false);

        $menus = $this->menuService->getMenus($role, $isV2);
        
        $version = md5($menus->toJson());
        $etag = '"' . $version . '"';
        
        // Check for 304 Not Modified
        if ($request->hasHeader('If-None-Match') && $request->header('If-None-Match') === $etag) {
            return response(null, 304)->header('ETag', $etag);
        }

        return response()->json([
            'data' => $menus,
            'version' => $version,
        ])->header('ETag', $etag);
    }
}
