<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['key'])]
class SettingMedia extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'setting_media';

    public static function brand(): self
    {
        return self::query()->firstOrCreate(['key' => 'brand']);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('favicon')->singleFile();
    }
}
