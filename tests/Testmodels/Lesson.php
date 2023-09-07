<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM\Tests\Testmodels;

use Atk4\Data\Model;
use PhilippR\Atk4\MToM\MToMTait;


class Lesson extends Model
{
    use MToMTait;

    public $table = 'lesson';


    protected function init(): void
    {
        parent::init();

        $this->addField('name');

        $this->addMToMReferenceAndDeleteHook(StudentToLesson::class);
        $this->addMToMReferenceAndDeleteHook(TeacherToLesson::class);
    }

    public function addStudent($student, array $additionalFields = []): StudentToLesson
    {
        return $this->addMToMRelation(new StudentToLesson($this->getPersistence()), $student, $additionalFields);
    }

    public function removeStudent($student): StudentToLesson
    {
        return $this->removeMToMRelation(new StudentToLesson($this->getPersistence()), $student);
    }

    public function hasStudent($student): bool
    {
        return $this->hasMToMRelation(new StudentToLesson($this->getPersistence()), $student);
    }
}
