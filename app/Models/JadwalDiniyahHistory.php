<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalDiniyahHistory extends Model
{
    use HasFactory;

    protected $table = 'jadwal_diniyah_histories';

    protected $fillable = [
        'jadwal_diniyah_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'user_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalDiniyah::class, 'jadwal_diniyah_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
