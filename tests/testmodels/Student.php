<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use Atk4\Data\Model;
use mtomforatk\ModelWithMToMTrait;


class Student extends Model
{
    use ModelWithMToMTrait;

    public $table = 'student';


    protected function init(): void {
        parent::init();

        $this->addField('name');

        $this->addMToMReferenceAndDeleteHook(StudentToLesson::class);
    }

    public function addLesson($lesson, array $additionalFields = []) {
        return $this->addMToMRelation(new StudentToLesson($this->persistence), $lesson, $additionalFields);
    }

    public function removeLesson($lesson) {
        return $this->removeMToMRelation(new StudentToLesson($this->persistence), $lesson);
    }

    public function hasLessonRelation($lesson) {
        return $this->hasMToMRelation(new StudentToLesson($this->persistence), $lesson);
    }
}
