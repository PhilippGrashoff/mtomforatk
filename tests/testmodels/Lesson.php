<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use Atk4\Data\Model;
use mtomforatk\ModelWithMToMTrait;


class Lesson extends Model
{
    use ModelWithMToMTrait;

    public $table = 'lesson';


    protected function init(): void {
        parent::init();

        $this->addField('name');

        $this->addMToMReferenceAndDeleteHook(StudentToLesson::class);
        $this->addMToMReferenceAndDeleteHook(TeacherToLesson::class);
    }
}
