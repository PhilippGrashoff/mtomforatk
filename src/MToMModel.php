<?php declare(strict_types=1);

namespace mtomforatk;

use Atk4\Data\Exception;
use Atk4\Data\Model;


abstract class MToMModel extends Model
{

    /**
     * @param array<string,string> $fieldNamesForReferencedClasses
     * with 2 keys and 2 values. Set these four strings child classes.
     * Will be used to create hasOne Reference Fields in init()
     * e.g. [
     *          'student_id' => Student::class,
     *          'lesson_id' => Lesson::class
     *      ]
     */
    protected array $fieldNamesForReferencedClasses = [];

    /**
     * @param array<class-string,Model> $referenceObjects containing instances of the two linked models. Useful for re-using them and saving DB requests
     */
    protected array $referenceObjects = [];


    /**
     * check if $fieldNamesForReferencedClasses content is valid.
     * Create hasOne References to both linked classes.
     */
    protected function init(): void
    {
        parent::init();
        //make sure 2 classes to link are defined
        if (count($this->fieldNamesForReferencedClasses) !== 2) {
            throw new Exception(
                '2 Fields and corresponding classes need to be defined in fieldNamesForReferencedClasses array'
            );
        }
        if (
            !class_exists(reset($this->fieldNamesForReferencedClasses))
            || !class_exists(end($this->fieldNamesForReferencedClasses))
        ) {
            throw new Exception('Non existent Class defined in fieldNamesForReferencedClasses array');
        }

        foreach ($this->fieldNamesForReferencedClasses as $fieldName => $className) {
            /** @var class-string $className */
            $this->hasOne($fieldName, ['model' => [$className]]);
            $this->referenceObjects[$className] = null;
        }
    }


    /**
     *
     *  Shortcut to get a record from each of the linked classes. e.g.
     *  $studentToLesson = $student->addLesson(4); //add Lesson by ID, no lesson object yet
     *  $lesson = $studentToLesson->getObject(Lesson::class); //will return Lesson record with ID 4
     *
     * @param string $className
     * @return Model|null
     */
    public function getReferenceEntity(string $className): ?Model
    {
        if (!array_key_exists($className, $this->referenceObjects)) {
            throw new Exception('Invalid className passed in ' . __FUNCTION__);
        }

        //load if necessary
        if (!$this->referenceObjects[$className] instanceof $className) {
            $this->referenceObjects[$className] = new $className($this->persistence);
            //will throw exception if record isn't found
            $this->referenceObjects[$className]->load(
                $this->get(array_search($className, $this->fieldNamesForReferencedClasses))
            );
        }

        return $this->referenceObjects[$className];
    }


    /**
     *  used by ModelWithMToMTrait to make records available in getObject() without extra DB request
     *
     * @param Model $model
     * @return void
     */

    public function addReferenceEntity(Model $model): void
    {
        $modelClass = get_class($model);
        if (!array_key_exists($modelClass, $this->referenceObjects)) {
            throw new Exception('This class does not have a reference to ' . $modelClass);
        }

        $this->referenceObjects[$modelClass] = $model;
    }


    /**
     *  used by ModelWithMToMTrait to get the correct field name that corresponds to one of the linked Models
     *
     * @param Model $model
     * @return string
     */
    public function getFieldNameForModel(Model $model): string
    {
        $fieldName = array_search(get_class($model), $this->fieldNamesForReferencedClasses);
        if (!$fieldName) {
            throw new Exception(
                'No field name defined in $fieldNamesForReferencedClasses for Class ' . get_class($model)
            );
        }

        return $fieldName;
    }


    /**
     * results ín e.g. $this->addCondition('student_id', 5);
     *
     * @param Model $model
     * @return void
     */
    public function addConditionForModel(Model $model): void
    {
        $this->addCondition($this->getFieldNameForModel($model), $model->get($model->id_field));
    }


    /**
     * We will have 2 Model classes defined which the MToMModel will connect. This function returns the class name of
     * the other class if one is passed
     *
     * @param Model $model
     * @return string
     */
    public function getOtherModelClass(Model $model): string
    {
        $modelClass = get_class($model);
        if (!in_array($modelClass, $this->fieldNamesForReferencedClasses)) {
            throw new Exception('Class ' . $modelClass . 'not found in fieldNamesForReferencedClasses');
        }

        //as array has 2 elements, return second if passed class is the first, else otherwise
        if (reset($this->fieldNamesForReferencedClasses) === $modelClass) {
            return end($this->fieldNamesForReferencedClasses);
        }
        return reset($this->fieldNamesForReferencedClasses);
    }
}
