<?php declare(strict_types=1);

namespace mtomforatk;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\data\Reference;


/**
 * Trait MToMTrait
 */
trait ModelWithMToMTrait
{

    /**
     * Create a new MToM relation, e.g. a new StudentToLesson record. Called from either Student or Lesson class.
     * First checks if record does exist already, and only then adds new relation.
     */
    protected function addMToMRelation(
        $otherModel,
        MToMModel $mToMModel,
        string $otherModelClass,
        array $additionalFields = []
    ): MToMModel {
        //$this needs to be loaded to get ID
        $this->checkThisIsLoaded();

        $otherModel = $this->mToMLoadObject($otherModel, $otherModelClass);

        //check if reference already exists, if so update existing record only
        $ourField   = $this->getFieldNameFromMToMModel($this);
        $theirField = $this->getFieldNameFromMToMModel($otherModel);
        $mToMModel->addCondition($ourField, $this->get('id'));
        $mToMModel->addCondition($theirField, $otherModel->get('id'));
        $mToMModel->tryLoadAny();

        //set values
        $mToMModel->set($ourField, $this->get('id'));
        $mToMModel->set($theirField, $otherModel->get('id'));

        //set additional field values
        foreach ($additionalFields as $field_name => $value) {
            $mToMModel->set($field_name, $value);
        }

        //no reload necessary after insert
        $mToMModel->reload_after_save = false;
        //if that record already exists mysql will throw an error if unique index is set, catch here
        $mToMModel->save();
        $mToMModel->addLoadedObject($this);
        $mToMModel->addLoadedObject($otherModel);

        return $mToMModel;
    }


    /**
     * function used to remove a MToMModel record like StudentToLesson. Either used from Student or Lesson class.
     * GuestToGroup etc.
     */
    protected function removeMToMRelation(
        $otherModel,
        MToMModel $mToMModel,
        string $otherModelClass
    ): MToMModel {
        //$this needs to be loaded to get ID
        $this->checkThisIsLoaded();

        $otherModel = $this->mToMLoadObject($otherModel, $otherModelClass);

        $mToMModel->addCondition($this->getFieldNameFromMToMModel($this), $this->get('id'));
        $mToMModel->addCondition($this->getFieldNameFromMToMModel($otherModel), $otherModel->get('id'));
        $mToMModel->loadAny();
        $mToMModel->delete();

        return $mToMModel;
    }


    /**
     * checks if a MtoM reference to the given object exists or not, e.g. if a StudentToLesson record exists for a
     * specific student and lesson
     */
    protected function hasMToMRelation(
        $otherModel,
        MToMModel $mToMModel,
        string $otherModelClass
    ): bool {
        $this->checkThisIsLoaded();

        $otherModel = $this->mToMLoadObject($otherModel, $otherModelClass);

        $mToMModel->addCondition($this->getFieldNameFromMToMModel($this), $this->get('id'));
        $mToMModel->addCondition($this->getFieldNameFromMToMModel($otherModel), $otherModel->get('id'));
        $mToMModel->tryLoadAny();

        return $mToMModel->loaded();
    }


    /**
     * helper function for MToMFunctions: Loads the object if only id is passed,
     * else checks if object is of the right class
     */
    private function mToMLoadObject($object, string $objectClass): Model
    {
        //if object is passed, extract id
        if (is_object($object)) {
            //check if passed object is of desired type
            if (!$object instanceof $objectClass) {
                throw new Exception('Wrong class:' . get_class($object) . ' was passed, ' . $objectClass . ' was expected in ' . __FUNCTION__);
            }
        }
        else {
            $object_id = $object;
            $object = new $objectClass($this->persistence);
            $object->tryLoad($object_id);
        }

        //make sure object is loaded
        if (!$object->loaded()) {
            throw new Exception('Object could not be loaded in ' . __FUNCTION__);
        }

        return $object;
    }


    /**
     * In each MToM operation, $this needs to be loaded to pull id. This function throws an exception if its not.
     */
    protected function checkThisIsLoaded(): void {
        if(!$this->loaded()) {
            throw new Exception('$this needs to be loaded in ' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']);
        }
    }


    /**
     * 1) adds HasMany Reference to intermediate model.
     * 2) adds after delete hook which deletes any intermediate model linked to the deleted "main" model. This way, no outdated intermediate models exist.
     * Returns HasMany reference for further modifying reference if needed.
     */
    protected function addMToMReferenceAndDeleteHook(
        string $mtomClassName,
        string $referenceName = '',
        array $referenceDefaults = []
    ): Reference\HasMany {
        //if no reference name was passed, use Class name without namespace
        if(!$referenceName) {
            $referenceName = (new \ReflectionClass($mtomClassName))->getShortName();
        }

        $reference = $this->hasMany($referenceName, array_merge([$mtomClassName], $referenceDefaults));
        $this->onHook(
            Model::HOOK_AFTER_DELETE,
            function ($model) use ($referenceName) {
                foreach($model->ref($referenceName) as $mtomModel) {
                    $mtomModel->delete();
                }
            }
        );

        return $reference;
    }
    
    
    /**
     * selects the corresponding field name from MToMModel setting, e.g. "student_id" when Student is passed as Model
     */
    protected function getFieldNameFromMToMModel(Model $model, MToMModel $mToMModel): string {
        $fieldName = array_search(get_class($model), $mToMModel->fieldNamesForLinkedClasses);
        if(!$fieldName) {
            throw new Exception('No field name defined in ' . get_class($mToMModel) . '->fieldNamesForLinkedClasses for Class ' . get_class($model));
        }

        return $fieldName;
    }
}