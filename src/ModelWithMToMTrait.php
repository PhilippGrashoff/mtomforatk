<?php declare(strict_types=1);

namespace mtomforatk;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\data\Reference;
use PMRAtk\Data\MToMModel;


/**
 * Trait MToMTrait
 */
trait ModelWithMToMTrait
{

    /**
     * function used to add data to the MtoM relations like GroupToTour,
     * GuestToGroup etc.
     * First checks if record does exist already, and only then adds new relation.
     */
    protected function _addMToMRelation(
        $otherObject,
        MToMModel $mToMObject,
        string $otherObjectClass,
        string $outField,
        string $theirField,
        array $additionalFields = []
    ): MToMModel {
        //$this needs to be loaded to get ID
        $this->checkThisIsLoaded();

        $otherObject = $this->_mToMLoadObject($otherObject, $otherObjectClass);

        //check if reference already exists, if so update existing record only
        $mToMObject->addCondition($outField, $this->get('id'));
        $mToMObject->addCondition($theirField, $otherObject->get('id'));
        $mToMObject->tryLoadAny();

        //set values and conditions
        $mToMObject->set($outField, $this->get('id'));
        $mToMObject->set($theirField, $otherObject->get('id'));

        //set additional field values
        foreach ($additionalFields as $field_name => $value) {
            $mToMObject->set($field_name, $value);
        }

        //no reload neccessary after insert
        $mToMObject->reload_after_save = false;
        //if that record already exists mysql will throw an error if unique index is set, catch here
        $mToMObject->save();
        $mToMObject->addLoadedObject($this);
        $mToMObject->addLoadedObject($otherObject);

        return $mToMObject;
    }


    /**
     * function used to remove a record the MtoM relations like GroupToTour,
     * GuestToGroup etc.
     */
    protected function _removeMToMRelation(
        $otherObject,
        MToMModel $mToMObject,
        string $otherObjectClass,
        string $ourField,
        string $theirField
    ): MToMModel {
        //$this needs to be loaded to get ID
        $this->checkThisIsLoaded();

        $otherObject = $this->_mToMLoadObject($otherObject, $otherObjectClass);

        $mToMObject->addCondition($ourField, $this->get('id'));
        $mToMObject->addCondition($theirField, $otherObject->get('id'));
        $mToMObject->loadAny();
        $mToMObject->delete();

        return $mToMObject;
    }


    /**
     * checks if a MtoM reference to the given object exists or not
     *
     * @param object The object to check if its referenced with $this
     * @param object The MToM Refence class, e.g. GroupToTour
     *
     * @return bool
     */
    protected function _hasMToMRelation(
        $otherObject,
        MToMModel $mToMModel,
        string $otherObjectClass,
        string $ourField,
        string $theirField
    ): bool {
        $this->checkThisIsLoaded();

        $otherObject = $this->_mToMLoadObject($otherObject, $otherObjectClass);

        $mToMModel->addCondition($ourField, $this->get('id'));
        $mToMModel->addCondition($theirField, $otherObject->get('id'));
        $mToMModel->tryLoadAny();

        return $mToMModel->loaded();
    }


    /**
     * helper function for MToMFunctions: Loads the object if only id is passed,
     * else checks if object matches rules
     */
    private function _mToMLoadObject($object, string $objectClass): Model
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
     * In each MToM operation, $this needs to be loaded to pull id.
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
    protected function addMToMReference(string $mtomClassName, string $referenceName = '', array $referenceDefaults = []): Reference\HasMany {
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
}