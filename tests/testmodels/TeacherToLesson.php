<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use atk4\data\Model;
use mtomforatk\MToMModel;


class TeacherToLesson extends MToMModel
{
    public $table = 'teacher_to_lesson';

    protected $fieldNamesForReferencedClasses =
        [
            'teacher_id' => Teacher::class,
            'lesson_id' => Lesson::class
        ];
}
