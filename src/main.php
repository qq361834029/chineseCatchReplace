<?php

namespace wushenglong\chineseCatchReplace;

/**
 * 主类
 * Class Main
 * @package wushenglong\chineseCatchReplace
 */
class Main
{
    // -- 是否开启调试 --
    public $debug = true;

    // -- config文件配置 --
    public $root_dir = '';
    public $controllerDir = [];
    public $viewDir = [];
    public $jsDir = [];
    public $controllerDirFormat = '';
    public $viewDirFormat = '';
    public $jsDirFormat = '';
    public $langDirFormat = '';
    public $noFindDir = [];
    public $noFindFile = [];
    public $common_lang_path = '';

    // -- 公共语言包 --
    public $commonLangArr = [];
    public $commonLangArrReverse = [];
    public $commonValues = [];
    public $commonLangChineseArr = [];

    // -- 模块语言包 --
    public $module_name = '';
    public $moduleLangArr = [];
    public $moduleLangArrReverse = [];
    public $moduleLangChineseArr = [];
    public $newLang = [];

    // js语言包中文对应英文键值
    public $jsLangArrReverse = [];

    // 有道云配置，每小时只允许1000次
    public $youdaoConfig = [];

    /**
     * 初始化
     * Main constructor.
     * @param array $controllerDir
     * @param array $viewDir
     * @param array $jsDir
     * @param string $module_name
     */
    public function __construct($controllerDir = [], $viewDir = [], $jsDir = [], $module_name = '')
    {
        // 自定义配置信息
        $config = require('config.php');
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
        // 目标目录配置
        $commonLangArr       = require($this->root_dir . $this->common_lang_path);
        $this->commonLangArr = $commonLangArr['common'];
        $this->controllerDir = $controllerDir;
        $this->viewDir       = $viewDir;
        $this->jsDir         = $jsDir;
        $this->module_name   = $module_name;
    }


    /**
     * 循环获取
     */
    public function loopGetHasChinaFromContent()
    {
        $this->commonValues = array_values($this->commonLangArr);

        // 获取模块已有的语言
        $moduleLangArr       = require($this->getModuleLangPath());
        $this->moduleLangArr = $moduleLangArr['common'];
        if (!empty($this->controllerDir)) {
            foreach ($this->controllerDir as $file_dir) {
                if (in_array($file_dir, $this->noFindDir)) {
                    return false;
                }
                $file_dir = $this->root_dir . $this->controllerDirFormat . $file_dir;
                $this->getHasChinaFromContent($file_dir);
            }
        }

        if (!empty($this->viewDir)) {
            foreach ($this->viewDir as $file_dir) {
                if (in_array($file_dir, $this->noFindDir)) {
                    return false;
                }
                $file_dir = $this->root_dir . $this->viewDirFormat . $file_dir;
                $this->getHasChinaFromContent($file_dir);
            }
        }

        if (!empty($this->jsDir)) {
            $this->commonLangArr        = [];
            $this->commonLangChineseArr = [];
            $this->moduleLangArr        = $moduleLangArr['js'];
            foreach ($this->jsDir as $file_dir) {
                if (in_array($file_dir, $this->noFindDir)) {
                    return false;
                }
                $file_dir = $this->root_dir . $this->jsDirFormat . $file_dir;
                $this->getHasChinaFromContent($file_dir);
            }
        }
        // 调试
        if ($this->debug === true) {
            print_r($this->newLang);
        }

    }

    /**
     * 循环目录文件内容替换
     */
    public function loopDirContentReplace()
    {
        // 公共
        $this->commonLangArrReverse = array_flip($this->commonLangArr);
        $this->commonLangChineseArr = $this->bubbleSort(array_values($this->commonLangArr));

        // 模块
        $moduleLangArr              = require($this->getModuleLangPath());
        $this->moduleLangArr        = $moduleLangArr['common'];
        $this->moduleLangArrReverse = array_flip($this->moduleLangArr);
        $this->moduleLangChineseArr = $this->bubbleSort(array_values($this->moduleLangArr));
        if (!empty($this->controllerDir)) {
            foreach ($this->controllerDir as $file_dir) {
                if (in_array($file_dir, $this->noFindDir)) {
                    return false;
                }
                $file_dir = $this->root_dir . $this->controllerDirFormat . $file_dir;
                $this->dirContentReplace($file_dir, 'controller');
            }
        }

        if (!empty($this->viewDir)) {
            foreach ($this->viewDir as $file_dir) {
                if (in_array($file_dir, $this->noFindDir)) {
                    return false;
                }
                $file_dir = $this->root_dir . $this->viewDirFormat . $file_dir;
                $this->dirContentReplace($file_dir, 'view');
            }
        }

        if (!empty($this->jsDir)) {
            $this->commonLangArr        = [];
            $this->commonLangChineseArr = [];
            $this->moduleLangArr        = $moduleLangArr['js'];
            foreach ($this->jsDir as $file_dir) {
                if (in_array($file_dir, $this->noFindDir)) {
                    return false;
                }
                $file_dir = $this->root_dir . $this->jsDirFormat . $file_dir;
                $this->dirContentReplace($file_dir, 'js');
            }
        }
    }

    /**
     * 循环目录文件内容替换
     */
    public function loopJsDirContentReplace()
    {
        if (!empty($this->jsDir)) {
            if (empty($this->jsLangArrReverse)) {
                // 获取中文语言包
                $zhLangArr = $this->getJsLang('zh');
                // 获取英文语言包
                $enLangArr = $this->getJsLang('en');
                foreach ($zhLangArr as $key => $chinese) {
                    $this->jsLangArrReverse[$chinese] = $enLangArr[$key];
                }
            }
            foreach ($this->jsDir as $file_dir) {
                if (in_array($file_dir, $this->noFindDir)) {
                    return false;
                }
                $file_dir = $this->root_dir . $this->jsDirFormat . $file_dir;
                $this->jsDirContentReplace($file_dir, 'js');
            }
        } else {
            exit('jsDir不能为空！');
        }
    }

    /**
     * 查询是否存在中文
     * @param $file_dir
     * @return bool
     */
    public function getHasChinaFromContent($file_dir)
    {
        if (is_dir($file_dir)) {
            $ch = opendir($file_dir); // 打开文件夹的句柄
            if ($ch) {
                while (($file_name = readdir($ch)) != false) {
                    if ($file_name == '.' || $file_name == '..') {
                        continue;
                    }
                    $file_path = $file_dir . "/" . $file_name;
                    if (is_dir($file_path)) {
                        // 不需要跑的目录
                        if (in_array($file_name, $this->noFindDir)) {
                            continue;
                        }
                        $this->getHasChinaFromContent($file_path);
                    } else {
                        // 不需要跑的文件
                        if (in_array($file_name, $this->noFindFile)) {
                            continue;
                        }
                        if ($this->debug === true) {
                            echo $file_path . PHP_EOL;
                        }
                        $file_arr = file($file_path);
                        for ($i = 0; $i < count($file_arr); $i++) { // 逐行读取文件内容
                            if (preg_match_all('/[\/\/]*[\x{4e00}-\x{9fff}]+/u', $file_arr[$i], $nameArr)) {
                                foreach ($nameArr[0] as $chinese) {
                                    // 不获取：包含/
                                    if (strpos($chinese, '/') === false
                                        // 不获取：存在公共语言包的
                                        && !in_array($chinese, $this->commonValues)
                                        // 不获取：已存在的已有语言包的
                                        && !in_array($chinese, array_values($this->moduleLangArr))
                                        // 不获取：已获取到的
                                        && !in_array($chinese, array_values($this->newLang))
                                    ) {
                                        if ($this->debug === false) {
                                            // 翻译并转小写
                                            $words = strtolower($this->translate($chinese));
                                            if (empty($words)) {
                                                exit('翻译请求出错！');
                                            }
                                            // 将空格替换成'_'
                                            $field = str_replace(' ', '_', $words);
                                            // 输出字段对应中文
                                            echo "         '$field' => '$chinese'," . PHP_EOL;
                                            $this->newLang[$field] = $chinese;
                                        } else {
                                            // 调试模式
                                            $this->newLang[] = $chinese;
                                        }
                                    }
                                }
                                fclose($file_arr);
                            }
                        }
                    }
                }
            }
            closedir($ch);
        } else {
            exit('目录不存在，必须是目录！');
        }
        return true;
    }


    /**
     * 目录文件内容替换
     * @param $file_dir
     * @param string $search_type
     * @return bool
     */
    public function dirContentReplace($file_dir, $search_type = '')
    {
        if (is_dir($file_dir)) {
            $ch = opendir($file_dir);
            if ($ch) {
                while (($file_name = readdir($ch)) != false) {//判断是不是有子文件或者文件夹
                    if ($file_name == '.' || $file_name == '..') {
                        continue;
                    }
                    $file_path = $file_dir . "/" . $file_name;
                    if (is_dir($file_path)) {
                        // 不需要跑的目录
                        if (in_array($file_name, $this->noFindDir)) {
                            continue;
                        }
                        $this->dirContentReplace($file_path, $search_type);
                    } else {
                        $this->fileContentReplace($file_path, $file_name, $search_type);
                    }

                }
            }
            closedir($ch);
        }
        return true;
    }

    /**
     * 文件内容替换
     * @param $file_path
     * @param $file_name
     * @param $search_type
     * @return bool
     */
    public function fileContentReplace($file_path, $file_name, $search_type)
    {
        if (is_file($file_path)) {
            // 不需要跑的文件
            if (in_array($file_name, $this->noFindFile)) {
                return false;
            }

            // 调试输出
            if ($this->debug === true) {
                echo $file_path . PHP_EOL;
            }
            $file_content = file_get_contents($file_path);
            // 模块语言包匹配
            if (!empty($this->moduleLangChineseArr)) {
                foreach ($this->moduleLangChineseArr as $chinese) {
                    $result = $this->_getStrReplaceContent($search_type, $this->module_name, $chinese, $this->moduleLangArrReverse, $file_content);
                    if (!empty($result)) {
                        $file_content = $result;
                    }
                }
            }
            // 公共语言包匹配
            if (!empty($this->commonLangChineseArr)) {
                foreach ($this->commonLangChineseArr as $chinese) {
                    $result = $this->_getStrReplaceContent($search_type, 'common', $chinese, $this->commonLangArrReverse, $file_content);
                    if (!empty($result)) {
                        $file_content = $result;
                    }
                }
            }

            // 防止错误
            if ($file_content != '') {
                file_put_contents($file_path, $file_content);
            }
        } else {
            exit('必须是文件！');
        }
    }

    /**
     * 获取替换内容
     * @param $search_type
     * @param $module_name
     * @param $chinese
     * @param $LangArrReverse
     * @param $file_content
     * @return mixed
     */
    private function _getStrReplaceContent($search_type, $module_name, $chinese, $LangArrReverse, $file_content)
    {
        // 控制器下
        if ($search_type == 'controller') {
            $search  = "'" . $chinese . "'";
            $replace = "trans('$module_name.common.{$LangArrReverse[$chinese]}')";
        } else if ($search_type == 'view') {
            // 模版下
            $search  = $chinese;
            $replace = "{{ trans('$module_name.common.{$LangArrReverse[$chinese]}') }}";
        } else if ($search_type == 'js') {
            // 模版下
            $search  = "/[\[]" . $chinese . "[\]]/";
            $replace = "[\" + window.commonLang.js.{$LangArrReverse[$chinese]} + \"]";
        } else {
            exit('搜索类型值不存在！');
        }
        return preg_replace($search, $replace, $file_content);
    }

    /**
     * 目录文件内容替换
     * @param $file_dir
     * @return bool
     */
    public function jsDirContentReplace($file_dir)
    {
        if (is_dir($file_dir)) {
            $ch = opendir($file_dir);
            if ($ch) {
                while (($file_name = readdir($ch)) != false) {//判断是不是有子文件或者文件夹
                    if ($file_name == '.' || $file_name == '..') {
                        continue;
                    }
                    $file_path = $file_dir . "/" . $file_name;
                    if (is_dir($file_path)) {
                        // 不需要跑的目录
                        if (in_array($file_name, $this->noFindDir)) {
                            continue;
                        }
                        $this->jsDirContentReplace($file_path);
                    } else {
                        $this->jsFileContentReplace($file_path, $file_name);
                    }

                }
            }
            closedir($ch);
        }
        return true;
    }

    /**
     * 文件内容替换
     * @param $file_path
     * @param $file_name
     * @return bool
     */
    public function jsFileContentReplace($file_path, $file_name)
    {
        if (is_file($file_path)) {
            // 不需要跑的文件
            if (in_array($file_name, $this->noFindFile)) {
                return false;
            }
            // 调试输出
            if ($this->debug === true) {
                echo $file_path . PHP_EOL;
            }
            $file_content = file_get_contents($file_path);
            // js模块语言包匹配
            if (!empty($this->jsLangArrReverse)) {
                foreach ($this->jsLangArrReverse as $chinese => $english) {
                    // 首尾带"字符中文
                    $search  = '/"' . $chinese . '"/';
                    $replace = '"' . $english . '"';
                    $result  = preg_replace($search, $replace, $file_content);
                    if (!empty($result)) {
                        $file_content = $result;
                    }
                    // 首尾带'字符中文
                    $search  = "/'" . $chinese . "'/";
                    $replace = "'" . $english . "'";
                    $result  = preg_replace($search, $replace, $file_content);
                    if (!empty($result)) {
                        $file_content = $result;
                    }
                    // 带>中文<字符
                    $search  = '/>' . $chinese . '</';
                    $replace = '>' . $english . '<';
                    $result  = preg_replace($search, $replace, $file_content);
                    if (!empty($result)) {
                        $file_content = $result;
                    }
                }
            }
            // 防止错误
            if ($file_content != '') {
                file_put_contents($file_path, $file_content);
            }
        } else {
            exit('必须是文件！');
        }
    }

    /**
     * 查找文件不包含某字符
     * @param string $dir_path
     * @param string $pattern
     */
    public function noFindSearchContentFile($dir_path = '', $pattern = '/frame.frame/')
    {
        if (is_dir($dir_path)) {
            $ch = opendir($dir_path); // 打开文件夹的句柄
            if ($ch) {
                while (($file_name = readdir($ch)) != false) {
                    if ($file_name == '.' || $file_name == '..' || $file_name == 'export.blade.php') {
                        continue;
                    }
                    $file_path = $dir_path . '/' . $file_name;
                    if (is_dir($file_path)) {
                        $this->noFindSearchContentFile($file_path);
                    } else {
                        $file_content = file_get_contents($file_path);
                        if (!preg_match($pattern, $file_content, $nameArr)) {
                            echo $file_path . PHP_EOL;
                        }
                    }
                }

            }
            closedir($ch);
        } else {
            exit('目录不存在，必须是目录！');
        }
    }

    /**
     * 翻译
     * @param $text
     * @return mixed
     */
    public function translate($text)
    {
        $text    = urlencode($text); //必须做 url 编码
        $url     = "http://fanyi.youdao.com/openapi.do?keyfrom=yangchong&key=520150590&type=data&doctype=json&version=1.1&q=$text";
        $content = file_get_contents($url);
        $result  = json_decode($content, 1);
        return $result['translation'][0];
    }

    /**
     * 获取语言包重复键
     */
    public function repeatLangKeys()
    {
        $file_arr = file($this->getModuleLangPath());
        if (empty($file_arr)) {
            exit('不存在该语言包文件！');
        }
        $result = [];
        for ($i = 0; $i < count($file_arr); $i++) { // 逐行读取文件内容
            if (preg_match('/\'([a-z_])+\'/u', $file_arr[$i], $nameArr)) {
                if (in_array($nameArr[0], $result)) {
                    echo $nameArr[0] . PHP_EOL;
                } else {
                    $result[] = $nameArr[0];
                }
            }
        }
    }

    /**
     * 获取语言包路径
     * @return string
     */
    public function getModuleLangPath()
    {
        return $this->root_dir . $this->langDirFormat . $this->module_name . '.php';
    }

    /**
     * 获取js语言包
     * @param string $type
     * @return mixed
     */
    public function getJsLang($type = '')
    {
        $path    = $this->root_dir . 'resources/lang/' . $type . '/common.php';
        $LangArr = require($path);
        return $LangArr['js'];
    }

    /**
     * 冒泡排序
     * @param $arr
     * @return mixed
     */
    public function bubbleSort($arr)
    {
        // 第一层可以理解为从数组中键为0开始循环到最后一个
        for ($i = 0; $i < count($arr); $i++) {
            for ($j = $i + 1; $j < count($arr); $j++) {
                if (strlen($arr[$i]) < strlen($arr[$j])) {
                    $tem     = $arr[$i]; // 这里临时变量，存贮$i的值
                    $arr[$i] = $arr[$j]; // 第一次更换位置
                    $arr[$j] = $tem; // 完成位置互换
                }
            }
        }
        return $arr;
    }

    /**
     * 获取字段
     * @param $words
     * @return string
     */
    public function getField($words)
    {
        return strtolower(str_replace(' ', '_', $words));
    }
}


?>