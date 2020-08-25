<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use atk4\data\Model;
use mtomforatk\ModelWithMToMTrait;

/**
 *
 */
class Lesson extends Model
{

    use ModelWithMToMTrait;

    public $table = 'lesson';


    public function init(): void {
        parent::init();

        $this->addField('name');

        $this->addMToMReferenceAndDeleteHook(StudentToLesson::class);
    }

    public function addStudent($student) {
        return $this->addMToMRelation($student, new StudentToLesson($this->persistence), Lesson::class, 'lesson_id', 'student_id');
    }

    public function removeStudent($student) {
        return $this->removeMToMRelation($student, new StudentToLesson($this->persistence), Lesson::class, 'lesson_id', 'student_id');
    }

    public function hasStudentRelation($student) {
        return $this->hasMToMRelation($student, new StudentToLesson($this->persistence), Lesson::class, 'lesson_id', 'student_id');
    }
}
