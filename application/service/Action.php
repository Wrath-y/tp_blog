<?php
namespace app\service;
use think\Db;

/**
* 
*/
class Action
{
	/**
	* @param int $id 删除对象的id
	* @param int $table 删除对象所在表
	*/
	public static function del($id,$table)
	{
		$res = Db::table($table)->where('id', $id)->delete();
		return $res;
	}
}