<?php declare(strict_types=1);

namespace mtomforatk;

use atk4\data\Exception;
use atk4\data\Model;


/**
 *
 */
abstract class MToMModel extends Model
{

    //array with 2 keys and 2 values. Set these four strings child classes. Will be used to create hasOne Reference Fields in init()
    //e.g. [
    //         'student_id' => Student::class,
    //         'lesson_id' => Lesson::class
    //     ]
    public $fieldNamesForLinkedClasses = [];

    //array containing instances of the two linked models. Useful for re-using them and saving DB requests
    protected $referenceObjects = [];


    /**
     *
     */
    public function init(): void
    {
        parent::init();
        if(count($this->fieldNamesForLinkedClasses) !== 2) {
            throw new Exception('2 Fields and corresponding classes need to be defined in $fieldNamesForLinkedClasses array');
        }
        if(
            !class_exists(reset($this->fieldNamesForLinkedClasses))
            || !class_exists(end($this->fieldNamesForLinkedClasses))
        ) {
            throw new Exception('Non existant Class defined in $fieldNamesForLinkedClasses array');
        }

        foreach($this->fieldNamesForLinkedClasses as $fieldName => $className) {
            $this->hasOne($fieldName, [$className]);
            $this->referenceObjects[$className] = null;
        }
    }


    /**
     *
     */
    public function getObject(string $className): ?Model {
        if(!array_key_exists($className, $this->referenceObjects)) {
            throw new Exception('Invalid className passed in ' . __FUNCTION__);
        }

        //load if necessary
        if(!$this->referenceObjects[$className] instanceof Model) {
            $this->referenceObjects[$className] = new $className($this->persistence);
            $this->referenceObjects[$className]->load($this->get(array_search($className, $this->fieldNamesForLinkedClasses)));
        }

        return $this->referenceObjects[$className];
    }


    /**
     *
     */
    public function addLoadedObject(Model $model): void {
        $modelClass = get_class($model);
        if(!array_key_exists($modelClass, $this->referenceObjects)) {
            throw new Exception('This class does not have a reference to ' . $modelClass);
        }

        $this->referenceObjects[$modelClass] = $model;
    }


    /**
     *
     */
    public function getFieldNameForModel(Model $model): string {
        $fieldName = array_search(get_class($model), $this->fieldNamesForLinkedClasses);
        if(!$fieldName) {
            throw new Exception('No field name defined in ' . __CLASS__ . '->fieldNamesForLinkedClasses for Class ' . get_class($model));
        }

        return $fieldName;
    }


    /**
     * We will have 2 Model classes defined which the MToMmodel will connect. This function returns the class name of
     * the other class if one is passed
     */
    public function getOtherModelClass(Model $model): string {
        $modelClass = get_class($model);
        if(!in_array($modelClass, $this->fieldNamesForLinkedClasses)) {
            throw new Exception('Class ' . $modelClass . 'not found in fieldNamesForLinkedClasses');
        }

        //as array has 2 elements, return second if passed class is the first, else otherwise
        if(reset($this->fieldNamesForLinkedClasses) === $modelClass) {
            return end($this->fieldNamesForLinkedClasses);
        }
        return reset($this->fieldNamesForLinkedClasses);
    }
}
