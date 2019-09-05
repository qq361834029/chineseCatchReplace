<?php
require_once './vendor/autoload.php';
use wushenglong\chineseCatchReplace\Main;


$controllerDir = ['Cases'];
$viewDir       = ['complain',
                  'complain_handle',
                  'cuiji',
                  'customer_address',
                  'linkman',
                  'linkman_show_config',
                  'cases_message',
                  'product_type',
                  'quick_note',
                  'stage',
];
// -- 实例化 --
$jsDir         = ['miui_en/layui'];
$controllerDir = $viewDir = [];
$cn_file_name  = 'common';
$main          = new Main($controllerDir, $viewDir, $jsDir, $cn_file_name);

// -- 英文转key
//echo $main->getField($_GET['a']);

// -- 查找语言包重复的键
//$main->repeatLangKeys();

// -- 查找文件是否不包含 --
//$dir_path = $main->root_dir . $main->viewDirFormat;
//$main->noFindSearchContentFile($dir_path);


// -- 获取中文语言包 --
//$main->loopGetHasChinaFromContent();


// -- 中文替换语言包函数 --
//$main->loopDirContentReplace();

// -- js中文转英文 --
//$main->loopJsDirContentReplace();


