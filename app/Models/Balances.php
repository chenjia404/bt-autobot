<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\Balances
 *
 * @property int $id
 * @property int $addr_id 用户id
 * @property string $name 资源名称
 * @property int|null $token_id 通证id
 * @property float $amount 可用金额
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances whereAddrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances whereTokenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Balances whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Balances extends Model
{
    protected $table = 'balances';

    protected $fillable = ['addr_id','token_id','name'];
}
