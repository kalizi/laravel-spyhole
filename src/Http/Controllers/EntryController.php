<?php

namespace Kalizi\LaravelSpyhole\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Kalizi\LaravelSpyhole\Http\Requests\StoreEntryRequest;
use Kalizi\LaravelSpyhole\Models\SessionRecording;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class EntryController extends Controller
{
    public function store(StoreEntryRequest $request): JsonResponse
    {
        $recordingId = null;
        if ($request->has('recording')) {
            $recordingId = (int)decrypt($request->get('recording'));

            if (SessionRecording::whereId($recordingId)->count() === 0) {
                throw new NotAcceptableHttpException();
            }
        }

        if (config('laravel-spyhole.track_request_session_id')) {
            $sessionId = $request->session()->getId();
        } else {
            if (session()->has('spyhole_session_id')) {
                $sessionId = session()->get('spyhole_session_id');
            } else {
                do {
                    $sessionId = Str::uuid()->toString();
                } while (
                    SessionRecording::whereSessionId($sessionId)->count() > 0 &&
                    $sessionId !== $request->session()->getId()
                );
                session()->put('spyhole_session_id', $sessionId);
            }
        }

        $userId = null;
        if (config('laravel-spyhole.record_user_id')) {
            $user = Auth::user();
            $userId = $user ? $user->getAuthIdentifier() : null;
        }

        if ($recordingId === null) {
            $recording = new SessionRecording();
            $recording->session_id = $sessionId;
            $recording->user_id = $userId;
            $recording->path = $request->get('path');
            $recording->recordings = $request->get('frames');
        } else {
            $recording = SessionRecording::wherePath($request->get('path'))
                ->whereId($recordingId)
                ->first();

            if ($recording === null) {
                throw new NotAcceptableHttpException();
            }

            // Merge frames from the same session
            $recording->recordings = array_merge(
                $recording->recordings,
                $request->get('frames')
            );
        }

        $recording->save();

        return response()->json([
            'success' => true,
            'recording' => encrypt($recording->id),
        ]);
    }
}
