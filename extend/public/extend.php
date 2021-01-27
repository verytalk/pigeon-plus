<?php

 function getDateFormat($time) {
    $nowTime = time();
    if(($nowTime - $time) <= 10) {
        return "几秒前";
    } elseif(($nowTime - $time) <= 60) {
        return " " . ($nowTime - $time) . " 秒前";
    } elseif(($nowTime - $time) <= 3600) {
        return " " . round(($nowTime - $time) / 60) . " 分钟前";
    } elseif(($nowTime - $time) <= 86400) {
        return " " . round(($nowTime - $time) / 3600) . " 小时前";
    } elseif(($nowTime - $time) <= 604800) {
        return " " . round(($nowTime - $time) / 86400) . " 天前";
    } else {
        return " " . date("Y-m-d H:i:s", $time);
    }
}


/**
 *
 *	生成 UUID
 */
function guid() {
    if(function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double)microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $uuid = substr($charid, 0, 8) . "-"
            . substr($charid, 8, 4) . "-"
            . substr($charid,12, 4) . "-"
            . substr($charid,16, 4) . "-"
            . substr($charid,20,12);
        return $uuid;
    }
}




