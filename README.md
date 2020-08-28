# mtomforatk
[![codecov](https://codecov.io/gh/PhilippGrashoff/mtomforatk/branch/master/graph/badge.svg)](https://codecov.io/gh/PhilippGrashoff/mtomforatk)

An addition to atk4/data to easily manage Many To Many (MToM) Relations. The purpose
is to write as little code as possible for actual MToM operations.

## Example code
As Example, lets use Students and Lessons. A Student can have many Lessons, a Lesson can have many Students.
To map this MToM relationship, 3 classes are created:
* Student
* Lesson
* StudentToLesson

After setting these classes up using this project, MToM operations can be done easily:
```
$student = new Student($app->db);
$student->save();

//Add Lesson by its ID
$student->addLesson(1); //creates a new StudentToLesson record
//remove lesson by its ID
$student->removeLesson(1); //removes the StudentToLesson record

//Add a lesson by passing the object
$lesson = new Lesson($app->db);
$lesson->save();
$student->addLesson($lesson);
$student->hasLessonRelation($lesson); //true
//remove a lesson by passing object
$student->removeLession($lesson);
$student->hasLessonRelation($lesson); //false
```

## Project Content
The project consists of two files:
* MToMModel: A base model for the intermediate class (like StudentToLesson). Working descendants can be coded with a few lines of code.
* MToMRelationForModelTrait: A Trait which is added to the models to be linked, (like Student and Lesson). With this trait, only a few more lines need to be added to make operations like `$lesson->addStudent(5);` work.

For an example implementation, have a look at tests/testmodels. Here you can find Student, Lesson and StudentToLesson Models.
