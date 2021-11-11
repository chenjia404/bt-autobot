<?php
/**
 * 16进制转10进制
 * @param string $hex
 * @return int|string
 */
function HexDec2(string $hex)
{
    $dec = 0;
    $hex = str_replace('0x','',$hex);
    $len = strlen($hex);
    for ($i = 1; $i <= $len; $i++) {
        $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
    }
    return $dec;
}