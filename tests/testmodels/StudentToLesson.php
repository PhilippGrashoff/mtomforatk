<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use atk4\data\Model;
use mtomforatk\MToMModel;

class StudentToLesson extends MToMModel
{
    public $table = 'student_to_lesson';

    public $fieldNamesForLinkedClasses =
        [
            'student_id' => Student::class,
            'lesson_id' => Lesson::class
        ];
}
