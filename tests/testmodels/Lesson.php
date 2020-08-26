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

    public function addStudent($student, array $additionalFields = []) {
        return $this->addMToMRelation($student, new StudentToLesson($this->persistence), $additionalFields);
    }

    public function removeStudent($student) {
        return $this->removeMToMRelation($student, new StudentToLesson($this->persistence));
    }

    public function hasStudentRelation($student) {
        return $this->hasMToMRelation($student, new StudentToLesson($this->persistence));
    }
}
