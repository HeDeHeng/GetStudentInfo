<?php
include 'libraries/Run.php';

$re = StudentToSql('201241402635');
//var_dump($re);
$DB = new DB_Mysql($CONFIG);
// var_dump($CONFIG);



function GetStudent($stu_id){
	$url            = 'http://xsc.dgut.edu.cn/szcx/Student.asp?Number='.$stu_id;
	//$mode           = '/width="12%">([\xa0-\xff]{2}){0,4}/';//GBK
	$mode           = '/width="12%">([\xe0-\xef][\x80-\xbf]{2}){0,4}/';//utf8
	$mode_type      = '/ width="8%">([\xe0-\xef][\x80-\xbf]{2}){1,10}/';
	$mode_birth     = '/<td align="center">[^<.]*/';
	$mode_class     = '/class=[^".]*/';
	$mode_score     = '/<b>[0-9\.]*<\/b>/';
	$mode_pro       = '/height="28">.*<\/td>[\t\n\x0B\f\r\s]*<\/tr>/U';
	$mode_pro_type  = '/height="28">.*<\/td>/U';
	$mode_pro_time  = '/align="center">.*<\/td>/U';
	$mode_pro_name  = '/<td>.*<\/td>/U';
	$mode_pro_score = '/align="center">[\.0-9]*<\/td><\/tr>/U';
	//获取页面
	$str =  file_get_contents($url);
	$str = mb_convert_encoding($str, 'utf-8', 'gbk');
	$str = preg_replace("/[\s]{2,}/","",$str);
	
	$student['stu_id'] = $stu_id;
	
	//姓名+性别
	preg_match_all($mode,$str,$data);
	if(empty($data[0][0])){
		return false;
	}
	$student['name'] = substr($data[0][0], 12);
	$student['sex'] = substr($data[0][1], 12);
	
	
	//政治面貌
	preg_match_all($mode_type,$str,$data);
	$student['type'] = substr($data[0][0], 12);
	
	//生日
	preg_match($mode_birth,$str,$data);
	$student['birth_day'] = substr($data[0], 19);
	
	//班级
	preg_match($mode_class,$str,$data);
	$student['class'] = substr($data[0], 6);
	
	//分数
	preg_match($mode_score,$str,$data);
	$student['score'] = substr($data[0], 3, -4);
	
	//得分项目
	preg_match_all($mode_pro,$str,$data);
	foreach ($data[0] as $k=>$v){
		$project[$k]['stu_id'] = $stu_id;
		//得分类型
		preg_match($mode_pro_type,$v,$data);
		$project[$k]['type'] = substr($data[0], 12, -5);
	
		//日期
		preg_match($mode_pro_time,$v,$data);
		$project[$k]['time'] = substr($data[0], 15, -5);
	
		//名称
		preg_match($mode_pro_name,$v,$data);
		$project[$k]['name'] = substr($data[0], 4, -5);
	
		//得分
		preg_match($mode_pro_score,$v,$data);
		$project[$k]['score'] = substr($data[0], 15, -10);
		if(substr($project[$k]['score'], 0, 1)=='.'){
			$project[$k]['score'] = '0'.$project[$k]['score'];
		}
	}
	$re['student'] = $student;
	$re['score_info']    = $project;
	return $re;
}


function StudentToSql($stu_id){
	global $DB;
	$student = GetStudent($stu_id);
	if ($student) {
			$DB->insert('student', $student['student']);
		foreach ($student['score_info'] as $k=>$v){
			$DB->insert('score_info', $v);
		}
		return true;
	}else {
		return false;
	}
}

?>