<?php

namespace TokenAuth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Token
 *
 * @package App\Models
 *
 * @property mixed user_id
 * @property mixed token_type
 * @property mixed token
 * @property mixed expires_at
 */
class Token extends Model
{
    const TYPE_ACCESS = 'access';
    const TYPE_REFRESH = 'refresh';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token_type',
        'token',
        'expires_at',
    ];
}
