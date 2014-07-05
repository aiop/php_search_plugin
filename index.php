<?php
// 严格开发模式
ini_set('display_errors', 'On');
ini_set('memory_limit', '64M');
date_default_timezone_set('Asia/Shanghai');

require 'fenci/config.php';
require 'fenci/db.php';
require 'fenci/phpanalysis.class.php';
require 'fenci/search.class.php';

//MYSQL
$database  = 'mysql';
$pconnect  = 0;
$db        = new MYSQL_DB;

//SEARCH
$qs        = new search($table,$field,10);
$relate    = '';
$str = isset($_GET['q'])? trim(str_replace("'", " ", safeEncoding($_GET['q']))) : '搜索词' ;

//中文分词参数设置
$do_fork  = $do_unit = true;
$do_multi = $do_prop = $pri_dict = false;

//分词
if($str != '')
{
    //初始化类
    PhpAnalysis::$loadInit = false;
    $pa = new PhpAnalysis('utf-8', 'utf-8', $pri_dict);
    //载入词典
    $pa->LoadDict(); 
    //执行分词
    $pa->SetSource($str);
    $pa->differMax = $do_multi;
    $pa->unitWord = $do_unit;
    $pa->StartAnalysis( $do_fork );
    //获得分词
    $okresult = $pa->GetFinallyResult(' ', $do_prop);
    $pa_foundWordStr = $pa->foundWordStr;
    $pa = '';
}
//获得搜索的mysql的语句
$rsql=$qs->q($str,$okresult,0);
//获得搜索数据
$query=$db->query($rsql);

while ($rs=$db->fetch_array($query)) {
  if($rs[$field]!=$str){
    $relate.='<tr><td><a href="?q='.urlencode($rs[$field]).'">'.$qs->GetRedKeyWord($rs[$field]).'</a></td></tr>';
  }
}

echo '<meta charset="utf-8">';
echo '<table>';
echo $relate;
echo '</table>';
//汉字，短字符判断字体编码。
function safeEncoding($string,$outEncoding = 'UTF-8'){
  $encoding = "UTF-8";
  for($i=0;$i<strlen($string);$i++)
  {
    if(ord($string{$i})<128)continue;
    
    if((ord($string{$i})&224)==224)
    {
      //第一个字节判断通过
      $char = $string{++$i};
      if((ord($char)&128)==128)
      {
        //第二个字节判断通过
        $char = $string{++$i};
        if((ord($char)&128)==128)
        {
          $encoding = "UTF-8";
          break;
        }
      }
    }
    if((ord($string{$i})&192)==192){
      //第一个字节判断通过
      $char = $string{++$i};
      if((ord($char)&128)==128)
      {
        //第二个字节判断通过
        $encoding = "GB2312";
        break;
      }
    }
  }

if(strtoupper($encoding) == strtoupper($outEncoding))
return $string;
else 
return iconv($encoding,$outEncoding,$string);
}

?>