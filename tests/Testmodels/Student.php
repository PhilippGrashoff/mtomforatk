<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM\Tests\Testmodels;

use Atk4\Data\Model;
use PhilippR\Atk4\MToM\MToMTait;


class Student extends Model
{
    use MToMTait;

    public $table = 'student';


    protected function init(): void
    {
        parent::init();
        $this->addField('name');
        $this->addMToMReferenceAndDeleteHook(StudentToLesson::class);
    }
}
