# mtomforatk
[![codecov](https://codecov.io/gh/PhilippGrashoff/mtomforatk/branch/master/graph/badge.svg)](https://codecov.io/gh/PhilippGrashoff/mtomforatk)

An addition to [atk4/data](https://github.com/atk4/data) to easily manage Many To Many (MToM) Relations. The purpose is to write as little code as possible for actual MToM operations.

# Project Content
The project consists of two files:
* **JunctionModel**: A base model for a junction class (like StudentToLesson). Working descendants can be coded with a few lines of code. Static methods to add (e.g. `StudentToLesson::addMToMRelation($student, $lesson);`), remove ((e.g. `StudentToLesson::removeMToMRelation($student, $lesson);`) and check ((e.g. `StudentToLesson::hasMToMRelation($student, $lesson);`) are implemented here.
* **MToMTrait**: A Trait which is added to the models to be linked, (like Student and Lesson). With this trait, the MToM relation can be defined with a single line in Model::init(): `$this->addMToMReferenceAndDeleteHook();`.

# How to use
## Installation
The easiest way to use this repository is to add it to your composer.json in the requirement section:
```json
{
  "require": {
    "philippgrashoff/mtomforatk": "4.0.*"
  }
}
```
## Sample code
As example, lets use Students and Lessons. A Student can have many Lessons, a Lesson can have many Students.
To map this MToM relationship, 3 classes are created. Demo models for this example can be found in tests\Testmodels:
* Student: A normal model which uses MToMTrait. 
* Lesson: A normal model which uses MToMTrait.
* StudentToLesson: The junction model carrying the student_id and lesson_id for each MToM relation between Students and Lessons. 

After setting these classes up using this project, MToM operations can be done easily:
```php
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
```

The sample code from this readme can be found in the `docs` directory.

# Versioning
The version numbers of this repository correspond with the atk4\data versions. So 4.0.x is compatible with atk4\data 4.0.x and so on.
