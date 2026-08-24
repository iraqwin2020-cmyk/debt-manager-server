<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'title', 'link', 'read_at'])]
class PlatformNotification extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime:Y-m-d H:i',
            'created_at' => 'datetime:Y-m-d H:i',
        ];
    }

    public static function notify(string $type, string $title, ?string $link = null): self
    {
        return static::create(['type' => $type, 'title' => $title, 'link' => $link]);
    }
}
