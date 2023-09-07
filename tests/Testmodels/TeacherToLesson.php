<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM\Tests\Testmodels;

use PhilippR\Atk4\MToM\JunctionModel;


class TeacherToLesson extends JunctionModel
{
    public $table = 'teacher_to_lesson';

    protected array $relationFieldNames =
        [
            'teacher_id' => Teacher::class,
            'lesson_id' => Lesson::class
        ];
}
