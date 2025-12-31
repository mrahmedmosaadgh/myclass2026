<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\hr;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class SchoolHrAdminRegistrationController extends Controller
{
    public function logo(string $path)
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        $absolutePath = Storage::disk('public')->path($path);

        return response()->file($absolutePath, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    public function create()
    {
        return Inertia::render('my_table_mnger/SchoolHrAdminRegistration');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'school_name' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'hr_admin', // Custom role
            ]);

            // Assign role using Spatie Permission if exists
            // Assuming 'hr_admin' role might not exist, fallback to 'admin' or create it?
            // For now, let's strictly assign the string role field as per User model check
            // and try to assign spatie role if possible.
            $roleName = 'hr_admin';
            if (!Role::where('name', $roleName)->exists()) {
                 Role::create(['name' => $roleName, 'guard_name' => 'web']);
            }
            $user->assignRole($roleName);


            // 2. Create HR Linked to User
            $hr = HR::create([
                'name' => $user->name,
                'user_id' => $user->id,
                'active' => true,
            ]);

            // 3. Create School Linked to HR
            $school = School::create([
                'name' => $validated['school_name'],
                'h_r_id' => $hr->id,
            ]);

            // 4. Update User with school_id (if not auto-handled by accessors, explicit save is safer)
            // The User model has 'school_id' in fillable, but 'schoolId()' method tries to derive it.
            // Let's see if we need to explicitly save it. User model has 'school_id' in fillable.
            $user->school_id = $school->id;
            $user->save();
            
            Auth::login($user);
        });

        return redirect()->route('dashboard');
    }

    public function mySchool()
    {
        $user = Auth::user();
        
        // Find the school where this user is the HR
        $hr = HR::where('user_id', $user->id)->firstOrFail();
        $school = School::where('h_r_id', $hr->id)->firstOrFail();

        // Ensure data exists and has the expected keys for the form
        $defaultData = [
            'school_code' => '',
            'name_official' => '',
            'name_short' => '',
            'school_type' => 'private',
            'education_levels' => [],
            'gender_type' => 'mixed',
            'ownership_type' => 'private',
            'authority' => '',
            'year_established' => null,
            'status' => 'active',
            'contact' => [
                'phone_primary' => '',
                'phone_secondary' => '',
                'email_official' => '',
                'website' => '',
            ],
            'address' => [
                'country' => '',
                'region' => '',
                'city' => '',
                'district' => '',
                'street_address' => '',
                'postal_code' => '',
                'latitude' => null,
                'longitude' => null,
            ],
            'logo' => [
                'current_logo_url' => '',
                'current_logo_path' => '',
                'logo_version' => 1,
                'last_changed_at' => null,
            ],
            'key_personnel' => [
                'principal' => ['name' => '', 'phone' => '', 'email' => ''],
                'vice_principal' => ['name' => '', 'phone' => '', 'email' => ''],
                'academic_coordinator' => ['name' => '', 'phone' => '', 'email' => ''],
                'admin_contact' => ['name' => '', 'phone' => '', 'email' => ''],
                'emergency_contact' => '',
            ]
        ];

        $mergedData = array_merge($defaultData, $school->data ?? []);
        $mergedData['logo'] = array_merge($defaultData['logo'], $mergedData['logo'] ?? []);

        if (
            empty($mergedData['logo']['current_logo_path']) &&
            !empty($mergedData['logo']['current_logo_url'])
        ) {
            $urlPath = parse_url($mergedData['logo']['current_logo_url'], PHP_URL_PATH);
            if (is_string($urlPath) && str_starts_with($urlPath, '/storage/')) {
                $mergedData['logo']['current_logo_path'] = ltrim(substr($urlPath, strlen('/storage/')), '/');
            }
        }

        if (!empty($mergedData['logo']['current_logo_path'])) {
            $mergedData['logo']['current_logo_url'] = route('school.logo', [
                'path' => $mergedData['logo']['current_logo_path'],
            ]);
        }

        $school->data = $mergedData;

        return Inertia::render('my_table_mnger/SchoolManager', [
            'school' => $school,
        ]);
    }

    public function update(Request $request, School $school)
    {
        $user = Auth::user();
        $hr = HR::where('user_id', $user->id)->firstOrFail();

        if ($school->h_r_id !== $hr->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'data' => 'required|array',
            'logo_file' => 'nullable|image|max:4096',
            // Detailed validation can be added here if needed
        ]);

        $data = $validated['data'];

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('school-logos', 'public');

            $logo = $data['logo'] ?? [];
            $logo['current_logo_path'] = $path;
            $logo['current_logo_url'] = route('school.logo', ['path' => $path]);
            $logo['logo_version'] = ((int)($logo['logo_version'] ?? 0)) + 1;
            $logo['last_changed_at'] = now()->toDateTimeString();
            $data['logo'] = $logo;
        }

        $school->update([
            'name' => $validated['name'],
            'data' => $data,
        ]);

        return redirect()->back()->with('success', 'School updated successfully');
    }
}
