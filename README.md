# mtomforatk
[![codecov](https://codecov.io/gh/PhilippGrashoff/mtomforatk/branch/master/graph/badge.svg)](https://codecov.io/gh/PhilippGrashoff/mtomforatk)

An addition to atk4/data to easily manage Many To Many (MToM) Relations. The purpose
is to write as little code as possible for actual MToM operations.

## Example code
As Example, lets use Students and Lessons. A Teacher can have many Lessons, a Lesson can have many Teachers.
To map this MToM relationship, 3 classes are created:
* Teacher
* Lesson
* TeacherToLesson

After setting these classes up using this project, MToM operations can be done easily:
```
$teacher = (new Teacher($persistence))->createEntity();
$teacher->save();

//Add Lesson by its ID, in this case 123
$teacherToLesson = $teacher->addMToMRelation(new TeacherToLesson($persistence), 123); //creates a new TeacherToLesson record
$lessonWithId123 = $teacherToLesson->getReferencedEntity(Lesson::class); //easy way to get Lesson object. No extra DB query is used.

//remove lesson by its ID
$teacher->removeMToMRelation(new TeacherToLesson($persistence), 123); //removes the TeacherToLesson record

//Add a lesson by passing the Entity
$lesson = (new Lesson($persistence))->createEntity();
$lesson->save();
$teacher->addMToMRelation(new TeacherToLesson($persistence), $lesson);
$teacher->hasMToMRelation(new TeacherToLesson($persistence), $lesson); //true

//remove a lesson by passing object
$teacher->removeMToMRelation(new TeacherToLesson($persistence), $lesson);
$teacher->hasMToMRelation(new TeacherToLesson($persistence), $lesson); //falses
```

If you want even more comfort, implement some wrapper functions which further shorten the code.
As Example, another MToMRelation is set up in test/testmodels: StudentToLesson. A Student can
have many Lessons, a Lesson can have many Students:
* Student
* StudentToLesson

See Student where addLesson(), removeLesson() and hasLessonRelation() wrapper functions are implemented:
```
$student->addLesson($lesson);
$student->hasLessonRelation($lesson); //true
$student->removeLession($lesson);
$student->hasLessonRelation($lesson); //false
```

## Project Content
The project consists of two files:
* MToMModel: A base model for the intermediate class (like StudentToLesson). Working descendants can be coded with a few lines of code.
* MToMRelationForModelTrait: A Trait which is added to the models to be linked, (like Student and Lesson). With this trait, only a few more lines need to be added to make operations like `$lesson->addStudent(5);` work.

For an example implementation, have a look at tests/testmodels. Here you can find Student, Lesson and StudentToLesson Models.
