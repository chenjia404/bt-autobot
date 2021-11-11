<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AssetsType
 *
 * @property int $id
 * @property string $net_type 主网类型
 * @property string $contract_address 合约地址
 * @property string $assets_name 资产名称
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType whereAssetsName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType whereContractAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType whereNetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $decimals 精度
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AssetsType whereDecimals($value)
 */
class AssetsType extends Model
{
    protected $table = 'assets_type';

}
