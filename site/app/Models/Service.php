<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, HasOrderColumn, HasTranslations;

    const ACTIVE = 1;

    protected $table = 'services';
    protected $fillable = [
        'img',
        'order_column',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function text()
    {
        return $this->hasMany(ServiceTrans::class, 'service_id', 'id');
    }

    public function meta()
    {
        return $this->hasOne(ServiceMeta::class, 'service_id', 'id');
    }

    public function gallery()
    {
        return $this->hasMany(ServiceGallery::class,'service_id', 'id')
            ->orderByRaw('COALESCE(order_column, id) asc');
    }

    public static function getServices($locale = 'en')
    {
        return Service::select(
            'services.id',
            'services.active',
            'services.img',
            'services.order_column',
            'service_trans.title',
            'service_trans.sub_title'
        )
            ->leftJoin('service_trans', function ($join) use ($locale) {
                $join->on('service_trans.service_id', 'services.id')
                    ->where('service_trans.lang', $locale);
            })
            ->orderBy('services.order_column', 'asc')
            ->get();
    }

    public static function getForFrontAllServices($locale = 'en')
    {
        return Service::select('services.id', 'services.active', 'services.img', 'services.created_at',
            'service_trans.slug',
            'service_trans.title', 'service_trans.sub_title', 'service_trans.text')
            ->leftJoin('service_trans', function ($join) use ($locale) {
                $join->on('service_trans.service_id', 'services.id')
                    ->where('service_trans.lang', $locale);
            })
            ->where('services.active', self::ACTIVE)
            ->get();
    }


    public static function getFrontSingleService($locale = 'en', $serviceId)
    {
        return Service::select('services.id', 'services.active', 'services.img', 'services.created_at', 'service_trans.slug',
            'service_trans.title', 'service_trans.sub_title', 'service_trans.text')
            ->leftJoin('service_trans', function ($join) use ($locale) {
                $join->on('service_trans.service_id', 'services.id')
                    ->where('service_trans.lang', $locale);
            })
            ->where([
                ['services.active', self::ACTIVE],
                ['services.id', $serviceId]
            ])
            ->with('gallery')
            ->first();
    }

    public static function getAllServiceOnPages($locale = 'en')
    {
        return Service::select('services.id', 'services.active', 'services.img', 'services.created_at',
            'service_trans.title', 'service_trans.sub_title', 'service_trans.slug', 'service_trans.text')
            ->leftJoin('service_trans', function ($join) use ($locale) {
                $join->on('service_trans.service_id', 'services.id')
                    ->where('service_trans.lang', $locale);
            })
            ->where('services.active', self::ACTIVE)
            ->latest('services.created_at')
            ->get();
    }

    public static function getAllServiceOnSingleServicePage($locale = 'en', $serviceId)
    {
        return Service::select('services.id', 'services.active', 'services.img', 'services.created_at',
            'service_trans.title', 'service_trans.sub_title', 'service_trans.slug', 'service_trans.text')
            ->leftJoin('service_trans', function ($join) use ($locale) {
                $join->on('service_trans.service_id', 'services.id')
                    ->where('service_trans.lang', $locale);
            })
            ->where('services.active', self::ACTIVE)
            ->whereNotIn('services.id', [$serviceId->service_id])
            ->latest('services.created_at')
            ->get();
    }

}
