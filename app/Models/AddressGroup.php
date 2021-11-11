<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AddressGroup
 *
 * @property int $id
 * @property string $name
 * @property string $collection_address 归集地址
 * @property string $address_nonce nonce
 * @property string $private_key 私钥
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup whereCollectionAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup whereAddressNonce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup wherePrivateKey($value)
 * @property string|null $master_private_key
 * @property string|null $master_address_nonce
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup whereMasterAddressNonce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AddressGroup whereMasterPrivateKey($value)
 */
class AddressGroup extends Model
{
    protected $table = 'address_group';

}
