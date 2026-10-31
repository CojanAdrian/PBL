<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMeta extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'service_metas';
    protected $fillable = ['img', 'service_id'];

    public function text()
    {
        return $this->hasMany(ServiceMetaTrans::class, 'service_meta_id', 'id');
    }

    public static function getServiceMeta($locale = 'en', $serviceId)
    {
        return ServiceMeta::select('service_metas.img', 'service_meta_trans.title')
            ->leftJoin('service_meta_trans', function ($join) use ($locale) {
                $join->on('service_metas.id', 'service_meta_trans.service_meta_id')
                    ->where('service_meta_trans.lang', $locale);
            })
            ->where('service_metas.service_id', $serviceId)
            ->first();
    }
}
