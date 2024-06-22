<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM\Tests\Testmodels;

use PhilippR\Atk4\MToM\JunctionModel;

class StudentToLesson extends JunctionModel
{
    public $table = 'student_to_lesson';

    protected static array $relationFieldNames =
        [
            'student_id' => Student::class,
            'lesson_id' => Lesson::class
        ];

    /**
     * only used to add a field to test additionalFields parameter in tests
     */
    protected function init(): void
    {
        parent::init();
        $this->addField('some_other_field');
    }
}
