<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;


use App\Model\SalariesModel;
use App\Model\TitlesModel;
use App\Model\DeptEmpModel;

/**
 * @Listener 
 */
class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event)
    {
        // $event->user;
        $emp_no     = $event->user->emp_no;
        $now        = date('Y-m-d');

        $salaries   = ['emp_no' => $emp_no, 'salary' => rand(50000, 90000), 'from_date' => $now, 'to_date' => '9999-01-01'];
        SalariesModel::insert($salaries);

        $titles     = ['emp_no' => $emp_no, 'title' => 'Senior Engineer', 'from_date' => $now, 'to_date' => '9999-01-01'];
        TitlesModel::insert($titles);

        $deptemp    = ['emp_no' => $emp_no, 'dept_no' => 'd007', 'from_date' => $now, 'to_date' => '9999-01-01'];
        DeptEmpModel::insert($deptemp);
    }
}