## bt 类应用自动挖矿

生成大量地址，然后分配矿工费，质押物，质押，挖矿，归集等

和普通的laravel项目一样配置数据库这些。

## 业务功能

需要先在 address_group 表增加一个地址组，需要自己创建一个矿工费钱包，私钥填入 private_key 字段，然后挖出的bt归集地址为 collection_address。

字段说明：
id 自动生成
name 地址组昵称，区分不同地址组
collection_address  挖出的bt归集地址
address_nonce   不管，默认设置0
private_key       矿工费地址的私钥，用其他工具创建


然后给矿工费地址转qki，和其他质押物


批量创建地址 给第一组创建1000个地址
```
php artisan create_address  1 1000
```


挖矿,挖第一组，2000个地址
```
php artisan mint_qbt  1 1000
```



归集qki,第一组，2000个地址
```
php artisan qbt_qki  1 1000
```

挖矿这个命令建议使用定时任务自动执行
## 配置

RPC_HOST qki节点rpc地址