<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LiveEvent extends Model
{
    use HasFactory;

    protected $table = 'live_events';

    protected $fillable = [
        'is_paid',
        'price',
        'event_at',
        'duration_event',
        'event_presenter',
        'name',
        'description',
        'agenda',
        'status',
        'image'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'price' => 'float',
        'agenda' => 'json'
    ];

    protected $appends = [
        'meeting_info',
        'purchased'
    ];

    public const ACTIVE_STATUS = 'active';

    public function usersAttendee()
    {
        return $this->belongsToMany(User::class, 'live_event_attendees');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::ACTIVE_STATUS);
    }

    public function getImageAttribute($image)
    {
        if (!empty($image)) {
            return asset('/upload/images/live-events/' . $image);
        }
        return $image;
    }

    public function getMeetingInfoAttribute()
    {
        if ($this->price || $this->is_paid) {
            if (!$this->usersAttendee()->where('user_id', request()->get('user_id'))->exists()) {
                return null;
            }
        }

        return $this->meeting;
    }

    public function meeting()
    {
        return $this->morphOne(ZoomMeeting::class, 'meeting', 'related_type', 'related_id')->latest();
    }

    public function getPurchasedAttribute()
    {
        return $this->usersAttendee()->where('user_id', request()->get('user_id'))->exists();
    }
}
