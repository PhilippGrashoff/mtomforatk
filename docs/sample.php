<?php declare(strict_types=1);

use Atk4\Data\Persistence\Sql;
use PhilippR\Atk4\MToM\Tests\Testmodels\Lesson;
use PhilippR\Atk4\MToM\Tests\Testmodels\Student;
use PhilippR\Atk4\MToM\Tests\Testmodels\StudentToLesson;

$persistence = new Sql('sqlite::memory:');

$studentHarry = (new Student($persistence))->createEntity();
$studentHarry->set('name', 'Harry');
$studentHarry->save();
$lessonGeography = (new Lesson($persistence))->createEntity();
$lessonGeography->set('name', 'Geography');
$lessonGeography->save();

//now, lets easily add Harry to the Geography lesson:
StudentToLesson::addMToMRelation($studentHarry, $lessonGeography);
//the above line created a StudentToLesson record with student_id = studentHarry's ID and lesson_id = lessonGeography's ID

//let's add Harry to another lesson
$lessonBiology = (new Lesson($persistence))->createEntity();
$lessonBiology->set('name', 'Biology');
$lessonBiology->save();
//adding/removing can either be done by passing the other model or only it's ID. In this case, we just pass the ID - that's what you typically get from UI
StudentToLesson::addMToMRelation($studentHarry, $lessonBiology->getId());
//this created another StudentToLesson record with student_id = studentHarry's ID and lesson_id = lessonBiology's ID

//Let's easily check if an MToM relation exists
StudentToLesson::hasMToMRelation($studentHarry, $lessonGeography); //true;

//harry is tired of Geography, lets remove him from this lesson:
StudentToLesson::removeMToMRelation($studentHarry, $lessonGeography);
//this removed the StudentToLesson Record linking Harry to Geography.
StudentToLesson::hasMToMRelation($studentHarry, $lessonGeography);  //false

//Linda attends both courses. Let's add Linda to both courses. But this time we do it the other way around and pass the lesson model as first argument:
$studentLinda = (new Student($persistence))->createEntity();
$studentLinda->set('name', 'Linda');
$studentLinda->save();
StudentToLesson::addMToMRelation($lessonGeography, $studentLinda);
StudentToLesson::addMToMRelation($lessonBiology, $studentLinda);
