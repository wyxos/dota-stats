<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DotaMatch extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'match_id',
        'player_slot',
        'radiant_win',
        'duration',
        'game_mode',
        'lobby_type',
        'hero_id',
        'start_time',
        'version',
        'kills',
        'deaths',
        'assists',
        'average_rank',
        'leaver_status',
        'party_size',
        'hero_variant',
        'details',
        'is_parsed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'radiant_win' => 'boolean',
        'party_size' => 'integer',
        'version' => 'string',
        'details' => 'array',
        'is_parsed' => 'boolean',
    ];
}
