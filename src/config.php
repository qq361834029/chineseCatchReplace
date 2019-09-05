<?php


return [

    // todo 根据项目情况自定义设置目录
    // 根目录
    'root_dir'            => '/Applications/MAMP/htdocs/miguan/cuishou/',
    // 控制器目录
    'controllerDirFormat' => 'app/Http/Controllers/',
    // 模版目录
    'viewDirFormat'       => 'resources/views/',
    // js目录
    'jsDirFormat'         => '/',
    // 语言包目录
    'langDirFormat'       => 'resources/lang/zh/',
    // 不需要查找的目录
    'noFindDir'           => ['css', 'font', 'images', 'tree_themes', 'style', 'json', 'tpl', 'extend', 'tree_themes'],
    // 不需要查找的文件
    'noFindFile'          => ['laydate.js', 'formSelects-v4.css', 'layui.all.js'],
    // 公共语言包路径
    'common_lang_path'    => '/resources/lang/zh/common.php',
    // 有道云配置，每小时只允许1000次
    'youdaoConfig' => [
        [
            'keyfrom' => 'yangchong',
            'key'     => '520150590',
        ],
        [
            'keyfrom' => 'cxvsdffd33',
            'key'     => '1310976914',
        ],
    ]

];