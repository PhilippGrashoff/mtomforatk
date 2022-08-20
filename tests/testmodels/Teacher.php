<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use Atk4\Data\Model;
use mtomforatk\ModelWithMToMTrait;


class Teacher extends Model
{
    use ModelWithMToMTrait;

    public $table = 'teacher';


    protected function init(): void {
        parent::init();

        $this->addField('name');

        $this->addMToMReferenceAndDeleteHook(TeacherToLesson::class);
    }
}
