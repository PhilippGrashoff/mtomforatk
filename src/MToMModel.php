<?php declare(strict_types=1);

namespace mtomforatk;

use atk4\data\Exception;
use atk4\data\Model;


/**
 *
 */
abstract class MToMModel extends Model
{

    //set these four in child classes. Will be used to create hasOne Reference Fields in init()
    //e.g. fieldName1 = tour_id, className1 = Tour::class
    //     fieldName2 = group_id, className2 = Group::class
    protected $fieldName1;
    protected $className1;
    protected $fieldName2;
    protected $className2;

    //array containing instances of the two linked models. Useful for re-using them and saving DB requests
    protected $referenceObjects = [];


    /**
     *
     */
    public function init(): void
    {
        parent::init();
        $this->hasOne($this->fieldName1, [$this->className1]);
        $this->hasOne($this->fieldName2, [$this->className2]);
        $this->referenceObjects[$this->className1] = null;
        $this->referenceObjects[$this->className2] = null;
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
            $this->referenceObjects[$className]->load($this->get($className === $this->className1 ? $this->fieldName1 : $this->fieldName2));
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
}
