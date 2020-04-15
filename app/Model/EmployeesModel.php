<?php

declare(strict_types=1);

namespace App\Model;
// use App\Model\SalariesModel;
class EmployeesModel extends BaseModel{
    protected $table = 'employees';
    public function salaries(){
        return $this->hasMany(SalariesModel::class, 'emp_no', 'emp_no');
    }
}
