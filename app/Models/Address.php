<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Address
 *
 * @property int $id
 * @property string $address
 * @property string $chain
 * @property int $group_id 地址组id
 * @property int $is_export 是否导出 1没有导出 2导出
 * @property int $last_check_time 上次检查时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereIsExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereLastCheckTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $private_key
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address wherePrivateKey($value)
 * @property int $nonce
 * @property int $power 算力
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereNonce($value)
 */
class Address extends Model
{
    protected $table = 'address';

}
