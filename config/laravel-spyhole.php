<?php

return [
    /**
     * This will enable recording the real session id from requests using
     * $request->session()->getId()
     * If set to false, this will build a fake session id and store it.
     */
    'track_request_session_id' => env('SPYHOLE_TRACK_SESSION_ID', false),

    /**
     * This will enable real user_id tracking in sessions.
     */
    'record_user_id' => env('SPYHOLE_TRACK_USER', false),

    /**
     * The minimum tracking data to be sent from frontend as a single payload.
     */
    'min_sampling_rate' => env('SPYHOLE_SAMPLING_RATE', 50),
];
