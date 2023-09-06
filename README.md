# mtomforatk
[![codecov](https://codecov.io/gh/PhilippGrashoff/mtomforatk/branch/master/graph/badge.svg)](https://codecov.io/gh/PhilippGrashoff/mtomforatk)
An addition to atk4/data to easily manage Many To Many (MToM) Relations. The purpose
is to write as little code as possible for actual MToM operations.

# Project Content
The project consists of two files:
* MToMModel: A base model for the intermediate class (like StudentToLesson). Working descendants can be coded with a few lines of code.
* MToMRelationForModelTrait: A Trait which is added to the models to be linked, (like Student and Lesson). With this trait, only a few more lines need to be added to make operations like `$lesson->addStudent(5);` work.

# How to use
## Installation
The easiest way to use this repository is to add it to your composer.json in the require section:
```json
{
  "require": {
    "philippgrashoff/cronforatk": "4.0.*"
  }
}
```
## Sample code
As example, lets use Students and Lessons. A Student can have many Lessons, a Lesson can have many Students.
To map this MToM relationship, 3 classes are created. Demo models for this example can be found in tests\Testmodels:
* Student: A normal model which additionally uses ModelWithMToMTrait. 3 little helpers methods are implemented to make MToM handling easier: addLesson(), removeLesson() and hasLesson();
* Lesson: A normal model which additionally uses ModelWithMToMTrait. 3 little helpers methods are implemented to make MToM handling easier: addStudent(), removeStudent() and hasStudent();
* StudentToLesson: The intermediate model carrying the student_id and lesson_id for each MToM relation between Students and Lessons. 

After setting these classes up using this project, MToM operations can be done easily:
```php
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
//adding/removing can either be done by passong the other model or only it's ID. In this case, we just pass the ID
$studentHarry->addLesson($lessonBiology->getId());
//this created another StudentToLesson record with student_id = studentHarry's ID and lesson_id = lessonBiology's ID

//Let's easily check if an MToM relation exists
$studentHarry->hasLesson($lessonGeography); //true;

//harry is tired of Geography, lets remove him from this lesson:
$studentHarry->removeLesson($lessonGeography);
//this removed the StudentToLesson Record linking Harry to Geography.
$studentHarry->hasLesson($lessonGeography); //false
```