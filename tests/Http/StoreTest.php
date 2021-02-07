<?php

namespace Kalizi\LaravelSpyhole\Tests\Http;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Kalizi\LaravelSpyhole\Models\SessionRecording;
use Kalizi\LaravelSpyhole\Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $kernel = app('Illuminate\Contracts\Http\Kernel');
        $kernel->pushMiddleware('Illuminate\Session\Middleware\StartSession');
    }

    /**
     * This test check if can correctly store a request with recording.
     * @test
     */
    public function can_store_first_recording_request()
    {
        $this->assertFalse(config('laravel-spyhole.track_request_session_id'));

        $requestData = [
            'frames' => [
                // some example data
                [
                    'timestamp' => now()->unix(),
                    'data' => [
                        'x' => 0,
                        'y' => 0,
                        'type' => 0
                    ]
                ]
            ],
            'path' => '/',
        ];

        $response = $this->json(
            'POST',
            route('spyhole.store-entry'),
            $requestData
        );

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'recording',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertIsNumeric($recordingId = decrypt($response->json('recording')));

        $this->assertDatabaseHas(
            'session_recordings',
            [
                'id' => (int)$recordingId,
                'path' => '/',
                'recordings' => base64_encode(gzencode(json_encode($requestData['frames']))),
                'user_id' => null,
            ]
        );

        $recording = SessionRecording::find((int) $recordingId);
        $this->assertNotEquals($this->app['session']->getId(), $recording->session_id);
    }

    /**
     * This test check if the fake session id is kept between calls.
     * @test
     */
    public function can_store_recordings_keeping_generated_session_id()
    {
        $this->assertFalse(config('laravel-spyhole.track_request_session_id'));

        $requestData = [
            'frames' => [
                // some example data
                [
                    'timestamp' => now()->unix(),
                    'data' => [
                        'x' => 0,
                        'y' => 0,
                        'type' => 0
                    ]
                ]
            ],
            'path' => '/',
        ];

        $response = $this->json(
            'POST',
            route('spyhole.store-entry'),
            $requestData
        );

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'recording',
        ]);

        $recordingId = decrypt($response->json('recording'));
        $recording = SessionRecording::find((int) $recordingId);
        $this->assertNotEquals($this->app['session']->getId(), $recording->session_id);

        $requestData['path'] = '/path_changed';

        $secondResponse = $this->json(
            'POST',
            route('spyhole.store-entry'),
            $requestData
        );
        $secondResponse->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'recording',
        ]);

        $this->assertDatabaseHas(
            'session_recordings',
            [
                'id' => (int)$recordingId,
                'path' => '/',
                'recordings' => base64_encode(gzencode(json_encode($requestData['frames']))),
                'user_id' => null,
                'session_id' => $recording->session_id
            ]
        );
    }

    /**
     * This test check if can correctly store frames into the same row of an existing session.
     * @test
     */
    public function can_store_frames_for_a_started_session()
    {
        $recording = new SessionRecording();
        $recording->recordings = [
            [
                'timestamp' => now()->unix(),
                'data' => [
                    'x' => 0,
                    'y' => 0,
                    'type' => 0
                ]
            ]
        ];
        $recording->path = '/';
        $recording->session_id = Str::uuid();
        $recording->save();

        $requestData = [
            'frames' => [
                [
                    'timestamp' => now()->unix(),
                    'data' => [
                        'x' => 0,
                        'y' => 0,
                        'type' => 0
                    ]
                ]
            ],
            'path' => '/',
            'recording' => encrypt($recording->id),
        ];

        $response = $this->json(
            'POST',
            route('spyhole.store-entry'),
            $requestData
        );

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'recording',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertIsNumeric($recordingId = decrypt($response->json('recording')));

        $this->assertDatabaseHas(
            'session_recordings',
            [
                'id' => (int)$recordingId,
                'path' => '/',
                'recordings' => base64_encode(gzencode(json_encode(array_merge(
                    $recording->recordings,
                    $requestData['frames']
                )))),
                'user_id' => null,
            ]
        );
    }

    /**
     * This test check if can correctly store the user id while the configuration option is enabled.
     * @test
     */
    public function can_store_recording_with_logged_in_user()
    {
        config()->set('laravel-spyhole.record_user_id', true);

        // Mock a fake user
        $user = new FakeUser();
        $user->id = rand(0, 1000);

        Auth::shouldReceive('user')->andReturn($user)->once();

        $requestData = [
            'frames' => [
                // some example data
                [
                    'timestamp' => now()->unix(),
                    'data' => [
                        'x' => 0,
                        'y' => 0,
                        'type' => 0
                    ]
                ]
            ],
            'path' => '/',
        ];

        $response = $this->json(
            'POST',
            route('spyhole.store-entry'),
            $requestData
        );

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'recording',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertIsNumeric($recordingId = decrypt($response->json('recording')));

        $this->assertDatabaseHas(
            'session_recordings',
            [
                'id' => (int)$recordingId,
                'path' => '/',
                'recordings' => base64_encode(gzencode(json_encode($requestData['frames']))),
                'user_id' => $user->id,
            ]
        );
    }

    /**
     * This test check if can correctly store the user id while the configuration option is enabled.
     * @test
     */
    public function can_store_correct_session_id()
    {
        config()->set('laravel-spyhole.track_request_session_id', true);

        $requestData = [
            'frames' => [
                // some example data
                [
                    'timestamp' => now()->unix(),
                    'data' => [
                        'x' => 0,
                        'y' => 0,
                        'type' => 0
                    ]
                ]
            ],
            'path' => '/',
        ];

        $response = $this->json(
            'POST',
            route('spyhole.store-entry'),
            $requestData
        );

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'recording',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertIsNumeric($recordingId = decrypt($response->json('recording')));

        $this->assertDatabaseHas(
            'session_recordings',
            [
                'id' => (int)$recordingId,
                'path' => '/',
                'recordings' => base64_encode(gzencode(json_encode($requestData['frames']))),
                'user_id' => null,
                'session_id' => $this->app['session']->getId()
            ]
        );
    }
}

class FakeUser implements Authenticatable
{
    /**
     * @var int $id Fake Identifier
     */
    public $id;

    public function getAuthIdentifierName(): string
    {
        return 'test';
    }

    public function getAuthIdentifier(): int
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        return 'password';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
