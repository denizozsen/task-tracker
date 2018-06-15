<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Task;

class TasksTableSeeder extends Seeder
{
    const FIRST_OF_MONTH = '2018-06-01';
    const A_MONDAY       = '2018-06-04';

    public function run()
    {
        (new Task(['user'=>1, 'title'=>'Buy Bread',          'starts_on'=>$this->firstOfMonth(), 'interval_type'=>Task::DAY]))->save();
        (new Task(['user'=>1, 'title'=>'Pay Credit Card',    'starts_on'=>$this->firstOfMonth(), 'interval_type'=>Task::MONTH]))->save();
        (new Task(['user'=>1, 'title'=>'Dentist',            'starts_on'=>$this->firstOfMonth(), 'interval_type'=>Task::YEAR]))->save();

        (new Task(['user'=>1, 'title'=>'Petrol for the car', 'starts_on'=>$this->aMonday(), 'interval_type'=>Task::WEEK]))->save();
        (new Task(['user'=>1, 'title'=>'A Bi-Weekly task',   'starts_on'=>$this->aMonday(), 'interval_type'=>Task::WEEK, 'interval'=>2]))->save();
    }

    private function firstOfMonth($min = 0, $max = 1)
    {
        return new Carbon('2018-06-01');
    }

    private function aMonday($min = 0, $max = 1)
    {
        return new Carbon('2018-06-04');
    }
}
