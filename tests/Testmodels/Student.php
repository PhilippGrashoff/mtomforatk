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

    public function addLesson($lesson, array $additionalFields = []): StudentToLesson
    {
        return $this->addMToMRelation(new StudentToLesson($this->getPersistence()), $lesson, $additionalFields);
    }

    public function removeLesson($lesson): StudentToLesson
    {
        return $this->removeMToMRelation(new StudentToLesson($this->getPersistence()), $lesson);
    }

    public function hasLesson($lesson): bool
    {
        return $this->hasMToMRelation(new StudentToLesson($this->getPersistence()), $lesson);
    }
}
