<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;
use Hyperf\DbConnection\Db;
use App\Model\EmployeesModel;
use App\Model\SalariesModel;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');
Router::get('/ykf', function(){
	$id = 10002;
	$maxId = 10004;


	$employees 		= EmployeesModel::leftJoin('salaries', 'salaries.emp_no', '=', 'employees.emp_no')->leftJoin('dept_emp', 'dept_emp.emp_no', '=', 'employees.emp_no')->orderBy('employees.emp_no', 'ASC')->orderBy('salaries.to_date', 'DESC')->groupBy('salaries.emp_no')->limit(20, 0);


	$selects 		= SalariesModel::query()->joinSub(EmployeesModel::query()->orderBy('emp_no', 'ASC')->limit(20, 0), 'employees', function($join){
		$join->on('employees.emp_no', '=', 'salaries.emp_no');
	})->leftJoin('dept_emp', 'dept_emp.emp_no', '=', 'salaries.emp_no')->orderBy('salaries.to_date', 'DESC')->groupBy('salaries.emp_no');





	$datas = [
		['dept_no' => 'd110', 'dept_name' => 'test110'],
		['dept_no' => 'd111', 'dept_name' => 'test111'],
		['dept_no' => 'd112', 'dept_name' => 'test112'],
	];


	// 事务
	DB::transaction(function () {
		// 一些操作
	    DB::table('departments')->whereIn('dept_no', ['d110', 'd111', 'd112'])->delete();
	});

	// 批量插入
	Db::table('departments')->insert($datas);

	// 更新,软删除同理
	Db::table('departments')->where('dept_no', 'd112')->update(['dept_name' => 'test1122']);

	// 直接删除
	Db::table('departments')->where('dept_no', '=', 'd111')->delete();


	// page分页器
	$page = 1;// page 为变量
	$limit = 10;
	$pageData = EmployeesModel::query()->orderBy('emp_no', 'asc')->limit($limit, ($page - 1) * $limit)->get();
	$pageArr  = ['data' => $pageData, 'meta' => ['page' => $page, 'limit' => $limit]];

	// lastID分页
	$lastID = 1;// lastID 为变量
	$limit = 10;
	$lastIDData = EmployeesModel::query()->where('emp_no', '>', $lastID)->orderBy('emp_no', 'asc')->limit($limit)->get()->toArray();
	$lastIDArr = ['data' => $lastIDData, 'meta' => ['lastID' => $lastIDData[(count($lastIDData) - 1)]['emp_no'], 'limit' => $limit]];


	return [
		'row' => EmployeesModel::query()->where('emp_no', $id)->first(),// 单条
		'list' => EmployeesModel::where('emp_no', '<', $maxId)->get(),// 集合
		'like' => EmployeesModel::where('last_name', 'like', 'Demke%')->limit(10, 0)->get(),// 模糊匹配
		'hasMany' => EmployeesModel::query()->where('emp_no', $id)->first()->salaries,// 查找某员工所有工资,暂时不考虑分页
		// 'joins' => $employees->get(),// 联合查询
		'selects' => $selects->get(),// 子查询, 获取员工信息和最后一次的工资与部门信息
		'pageArr' => $pageArr,
		'lastIDArr' => $lastIDArr,
	];
});
