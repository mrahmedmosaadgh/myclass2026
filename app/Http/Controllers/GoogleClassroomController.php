<?php

// app/Http/Controllers/GoogleClassroomController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Classroom;

class GoogleClassroomController extends Controller
{
    public function redirectToGoogle()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));

        $client->setScopes([
            Google_Service_Classroom::CLASSROOM_COURSES_READONLY,
            Google_Service_Classroom::CLASSROOM_ROSTERS_READONLY,
            Google_Service_Classroom::CLASSROOM_PROFILE_EMAILS,
            'email', 'profile'
        ]);

        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));

        if ($request->has('code')) {
            $client->fetchAccessTokenWithAuthCode($request->code);

            if ($client->getAccessToken()) {
                $service = new Google_Service_Classroom($client);
                // Fetch the user's courses
                $courses = $service->courses->listCourses();

                // You can store courses in the session, or send it to the frontend as a response
                session(['google_courses' => $courses]);

                return response()->json($courses);  // Or pass to a view
            }
        }

        return redirect('/')->with('error', 'Failed to connect to Google');
    }
}
