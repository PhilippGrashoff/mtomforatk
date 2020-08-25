<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use atk4\data\Model;
use mtomforatk\ModelWithMToMTrait;

/**
 *
 */
class Student extends Model
{

    use ModelWithMToMTrait;

    public $table = 'student';


    public function init(): void {
        parent::init();

        $this->addField('name');

        $this->addMToMReferenceAndDeleteHook(StudentToLesson::class);
    }

    public function addStudent($lesson) {
        return $this->addMToMRelation($lesson, new StudentToLesson($this->persistence), Student::class, 'student_id', 'lesson_id');
    }

    public function removeStudent($lesson) {
        return $this->removeMToMRelation($lesson, new StudentToLesson($this->persistence), Student::class, 'student_id', 'lesson_id');
    }

    public function hasStudentRelation($lesson) {
        return $this->hasMToMRelation($lesson, new StudentToLesson($this->persistence), Student::class, 'student_id', 'lesson_id');
    }
}
