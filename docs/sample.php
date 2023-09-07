<?php declare(strict_types=1);

use Atk4\Data\Persistence\Sql;
use PhilippR\Atk4\MToM\Tests\Testmodels\Lesson;
use PhilippR\Atk4\MToM\Tests\Testmodels\Student;

$persistence = new Sql('sqlite::memory:');

$studentHarry = (new Student($persistence))->createEntity();
$studentHarry->set('name', 'Harry');
$studentHarry->save();
$lessonGeography = (new Lesson($persistence))->createEntity();
$lessonGeography->set('name', 'Geography');
$lessonGeography->save();

//now, lets easily add Harry to the Geography lesson:
$studentHarry->addLesson($lessonGeography);
//the above line created a StudentToLesson record with student_id = studentHarry's ID and lesson_id = lessonGeography's ID

//let's add Harry to another lesson
$lessonBiology = (new Lesson($persistence))->createEntity();
$lessonBiology->set('name', 'Biology');
$lessonBiology->save();
//adding/removing can either be done by passing the other model or only it's ID. In this case, we just pass the ID - that's what you typically get from UI
$studentHarry->addLesson($lessonBiology->getId());
//this created another StudentToLesson record with student_id = studentHarry's ID and lesson_id = lessonBiology's ID

//Let's easily check if an MToM relation exists
$studentHarry->hasLesson($lessonGeography); //true;

//harry is tired of Geography, lets remove him from this lesson:
$studentHarry->removeLesson($lessonGeography);
//this removed the StudentToLesson Record linking Harry to Geography.
$studentHarry->hasLesson($lessonGeography); //false

//Linda attends both courses. Let's add Linda to both courses. But this time we do it the other way around: We use Lesson's helper functions:
$studentLinda = (new Student($persistence))->createEntity();
$studentLinda->set('name', 'Linda');
$studentLinda->save();
$lessonGeography->addStudent($studentLinda);
$lessonBiology->addStudent($studentLinda);
