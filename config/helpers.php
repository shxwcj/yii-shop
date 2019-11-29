<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/19
 * Time: 17:01
 */

function dd($params) {
    echo "<pre>";
    print_r($params);
    exit();
}


function input($type='') {
    return \Yii::$app->request->get($type);
}