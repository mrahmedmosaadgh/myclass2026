<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $preview = $request->boolean('preview', false);

        // If preview is requested, only allow admin users with manage-menus permission
        if ($preview) {
            $user = Auth::user();
            if (!$user || !$user->can('manage-menus')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            // Return role-scoped structure without filtering by the authenticated user's permissions
            // Also include inactive menus so admins can manage them
            $menus = $this->menuService->getMenuStructure($role, $isV2, true);
        } else {
            // Normal behavior: filter by the current user's permissions
            $menus = $this->menuService->getMenus($role, $isV2, false);
        }
        
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
