<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delegado extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'delegados';

    protected $fillable = ['nombre_completo'];

    public function delegaciones(): BelongsToMany
    {
        return $this->belongsToMany(Delegacion::class, 'delegado_delegacion');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
