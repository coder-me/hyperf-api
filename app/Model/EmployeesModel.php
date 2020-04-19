<?php

declare(strict_types=1);

namespace App\Model;

class EmployeesModel extends BaseModel
{
    protected $table = 'employees';
    public $genders = ['M', 'F'];
    public $limit = 20;
    public $orderBy = 'emp_no';
    public $orderCols = ['emp_no', 'birth_date', 'hire_date'];
    public $orderType = 'ASC';
    public $orderTypes = ['ASC', 'DESC'];
    public $timestamps = false;
}
