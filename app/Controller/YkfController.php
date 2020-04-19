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

/**
 * @OA\Info(
 *     title="基于测试库的一些操作",
 *     version="1.0.0",
 *     description="参数检查，注册，队列，解耦，事件，返回格式",
 * )
 */
/**
 * @OA\Server(
 *     description="开发",
 *     url="http://mining.test/api/"
 * )
 * @OA\Server(
 *     description="测试",
 *     url="http://47.111.142.19:81/api/"
 * )
 * @OA\ExternalDocumentation(
 *     description="项目开发规范",
 *     url="http://47.111.142.19:82/index.php?m=doc&f=view&docID=5"
 * )
 *
 */


namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Snowflake\IdGeneratorInterface;

use App\Middleware\AuthMiddleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Model\EmployeesModel;
use App\Model\SalariesModel;
use App\Model\TitlesModel;

use Psr\EventDispatcher\EventDispatcherInterface;
use App\Event\UserRegistered; 

/**
 * @AutoController()
 * @Middleware(AuthMiddleware::class)
 * Class YkfController
 * @package App\Controller
 */
class YkfController extends BaseController{
    /**
     * @Inject
     * @var EmployeesModel
     */
    private $employees;
    /**
     * @Inject
     * @var TitlesModel
     */
    private $titles;

    /**
     * @Inject 
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @OA\Get(
     *     path="/employees",
     *     tags={"员工"},
     *     summary="员工",
     *     description="员工信息",
     *     @OA\RequestBody(
     *          description="参数",
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="emp_no",
     *                  type="string",
     *                  description="员工ID",
     *              ),
     *              #@OA\Property(
     *              #    property="task_id",
     *              #    type="string",
     *              #    description="任务ID",
     *              #),
     *          )
     *     ),
     *     @OA\Response(response=200, description="请求成功"),
     * )
     */

    /**
     * @return array
     */
    public function employees(RequestInterface $request){
        $emp_no     = $request->input('emp_no');
        if($emp_no){
            $employee   = $this->employees
                            ->select('employees.*', 'salaries.salary', 'departments.dept_name')
                            ->leftJoin('salaries', 'salaries.emp_no', '=', 'employees.emp_no')
                            ->leftJoin('dept_emp', 'dept_emp.emp_no', '=', 'employees.emp_no')
                            ->leftJoin('departments', 'departments.dept_no', '=', 'dept_emp.dept_no')
                            ->where('employees.emp_no', $emp_no)
                            ->orderBy('salaries.to_date', 'DESC')
                            ->orderBy('dept_emp.to_date', 'DESC')->first();
            $titles     = $this->titles->where('emp_no', $emp_no)->orderBy('to_date', 'DESC')->get();
            $employee['titles']     = $titles;


            if($employee){
                return $employee;
            }else{
                
            }
        }else{
            $page       = $request->input('page', 1);
            $size       = $request->input('size', $this->employees->limit);
            $orderCol   = $request->input('orderby');
            $orderType  = $request->input('ordertype');
            $totals     = $this->employees->count();

            $orderCol   = in_array($orderCol, $this->employees->orderCols) ? $orderCol : $this->employees->orderBy;
            $orderType  = in_array($orderType, $this->employees->orderTypes) ? $orderType : $this->employees->orderType;
            $start      = ($page - 1) * $size;

            $list       = $this->employees->orderBy($orderCol, $orderType)->offset($start)->limit($size)->get();
            return [
                'data' => $list,
                'meta' => [
                    'page' => $page,
                    'size' => $size,
                    'order'=> $orderCol,
                    'type' => $orderType,
                ],
            ];
        }
    }


    /**
     * @OA\Get(
     *     path="/register",
     *     tags={"注册"},
     *     summary="注册",
     *     description="注册,走事件机制解耦自动添加部门，工资和职位",
     *     @OA\RequestBody(
     *          description="参数",
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="emp_no",
     *                  type="string",
     *                  description="员工编号",
     *              ),
     *              #@OA\Property(
     *              #    property="birth_date",
     *              #    type="string",
     *              #    description="生日",
     *              #),
     *              #@OA\Property(
     *              #    property="first_name",
     *              #    type="string",
     *              #    description="名字",
     *              #),
     *              #@OA\Property(
     *              #    property="last_name",
     *              #    type="string",
     *              #    description="姓",
     *              #),
     *              #@OA\Property(
     *              #    property="gender",
     *              #    type="string",
     *              #    description="性别",
     *              #),
     *              #@OA\Property(
     *              #    property="hire_date",
     *              #    type="string",
     *              #    description="入职日期",
     *              #),
     *          )
     *     ),
     *     @OA\Response(response=200, description="请求成功"),
     * )
     */

    /**
     * @return array
     * header: authorization=bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1ODcyODAxOTUsImV4cCI6MTU4OTg3MjE5NSwidWlkIjoicm9vdHMifQ.bgRo4Be0Za18Z4wH62xQHPM4O2wX0-3QE-Mj-De3eL4
     * 测试调用地址 http://192.168.10.10:9501/ykf/register?emp_no=1&birth_date=9999-01-01&first_name=test&last_name=we2&gender=M&hire_date=2020-04-19
     * 正式项目或不用 query 形式参数
     * 结果返回还没找到如何修改 header 状态码,所以先以其他方式返回
     */
    public function register(RequestInterface $request, ResponseInterface $response){
        $emp_no         = $request->input('emp_no');
        $birth_date     = $request->input('birth_date');
        $first_name     = $request->input('first_name');
        $last_name      = $request->input('last_name');
        $gender         = $request->input('gender');
        $hire_date      = $request->input('hire_date');

        if(!is_numeric($emp_no) || !in_array($gender, $this->employees->genders)){
            return ['code' => -1, 'msg' => '参数不正确'];
        }else{
            $this->employees->emp_no        = $emp_no;
            $this->employees->birth_date    = $birth_date;
            $this->employees->first_name    = $first_name;
            $this->employees->last_name     = $last_name;
            $this->employees->gender        = $gender;
            $this->employees->hire_date     = $hire_date;
            if($res = $this->employees->save()){
                $this->eventDispatcher->dispatch(new UserRegistered($this->employees));
            }
            return $res;
        }
    }

}
