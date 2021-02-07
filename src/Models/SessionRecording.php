<?php

namespace Kalizi\LaravelSpyhole\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SessionRecording
 *
 * @property int id
 * @property string path
 * @property string session_id
 * @property array recordings
 * @property string|null user_id
 * @package Kalizi\LaravelSpyhole\Models
 */
class SessionRecording extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'session_recordings';

    public function getRecordingsAttribute()
    {
        return json_decode(gzdecode(base64_decode($this->attributes['recordings'])));
    }

    public function setRecordingsAttribute($value)
    {
        $this->attributes['recordings'] = base64_encode(gzencode(json_encode($value)));
    }
}
