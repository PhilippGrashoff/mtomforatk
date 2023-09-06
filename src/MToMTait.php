<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Atk4\Data\Reference;


/**
 * @extends Model<Model>
 */
trait MToMTait
{

    /**
     *  Create a new MToM relation, e.g. a new StudentToLesson record. Called from either Student or Lesson class.
     *  First checks if record does exist already, and only then adds new relation.
     *
     * @param IntermediateModel $mToMModel
     * @param int|Model $otherEntity //if int, then it's only an ID
     * @param array<string,mixed> $additionalFields
     * @return IntermediateModel
     * @throws Exception
     * @throws \Atk4\Core\Exception
     */
    public function addMToMRelation(
        IntermediateModel $mToMModel,
        int|Model $otherEntity,
        array $additionalFields = []
    ): IntermediateModel {
        //$this needs to be loaded to get ID
        $this->assertIsLoaded();
        $otherEntity = $this->getOtherEntity($otherEntity, $mToMModel);
        //check if reference already exists, if so update existing record only
        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherEntity);
        //no reload necessary after insert
        $mToMModel->reloadAfterSave = false;
        $mToMEntity = $mToMModel->tryLoadAny() ?? $mToMModel->createEntity();

        $mToMEntity->set($mToMEntity->getFieldNameForModel($this), $this->getId());
        $mToMEntity->set($mToMEntity->getFieldNameForModel($otherEntity), $otherEntity->getId());

        //set additional field values
        foreach ($additionalFields as $fieldName => $value) {
            $mToMEntity->set($fieldName, $value);
        }

        //if that record already exists mysql will throw an error if unique index is set, catch here
        $mToMEntity->save();
        $mToMEntity->addReferencedEntity($this);
        $mToMEntity->addReferencedEntity($otherEntity);

        return $mToMEntity;
    }


    /**
     *  method used to remove a MToMModel record like StudentToLesson. Either used from Student or Lesson class.
     *  GuestToGroup etc.
     *
     * @param IntermediateModel $mToMModel
     * @param int|Model $otherEntity //if int, then it's only an ID
     * @return IntermediateModel
     * @throws Exception
     */
    public function removeMToMRelation(IntermediateModel $mToMModel, int|Model $otherEntity): IntermediateModel
    {
        //$this needs to be loaded to get ID
        $this->assertIsLoaded();
        $otherEntity = $this->getOtherEntity($otherEntity, $mToMModel);

        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherEntity);
        //loadAny as it will throw exception when record is not found
        $mToMModel = $mToMModel->loadAny();
        $mToMModel->delete();

        return $mToMModel;
    }


    /**
     * checks if a MtoM reference to the given entity exists or not, e.g. if a StudentToLesson record exists for a
     * specific student and lesson
     *
     * @param IntermediateModel $mToMModel
     * @param int|Model $otherEntity //if int, then it's only an ID
     * @return bool
     * @throws Exception
     */
    public function hasMToMRelation(IntermediateModel $mToMModel, int|Model $otherEntity): bool
    {
        $this->assertIsLoaded();
        $otherEntity = $this->getOtherEntity($otherEntity, $mToMModel);

        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherEntity);
        $mToMEntity = $mToMModel->tryLoadAny();

        return $mToMEntity !== null;
    }

    /**
     * 1) adds HasMany Reference to intermediate model.
     * 2) adds after delete hook which deletes any intermediate model linked to the deleted "main" model.
     * This way, no outdated intermediate models exist.
     * Returns HasMany reference for further modifying reference if needed.
     *
     * @param class-string<IntermediateModel> $mtomClassName
     * @param string $referenceName
     * @param array<string,mixed> $referenceDefaults
     * @param array<string,mixed> $mtomClassDefaults
     * @param bool $addDeleteHook
     * @return Reference\HasMany
     * @throws Exception
     */
    protected function addMToMReferenceAndDeleteHook(
        string $mtomClassName,
        string $referenceName = '',
        array $referenceDefaults = [],
        array $mtomClassDefaults = [],
        bool $addDeleteHook = true
    ): Reference\HasMany {
        //if no reference name was passed, use Class name
        if (!$referenceName) {
            $referenceName = $mtomClassName;
        }

        if (!class_exists($mtomClassName)) {
            throw new Exception('Class ' . $mtomClassName . ' not found in ' . __FUNCTION__);
        }

        $reference = $this->hasMany(
            $referenceName,
            array_merge(['model' => array_merge([$mtomClassName], $mtomClassDefaults)], $referenceDefaults)
        );
        if ($addDeleteHook) {
            $this->onHook(
                Model::HOOK_BEFORE_DELETE,
                function ($model) use ($referenceName): void {
                    foreach ($model->ref($referenceName) as $mtomModel) {
                        $mtomModel->delete();
                    }
                }
            );
        }

        return $reference;
    }


    /**
     * Used to load the other model if only ID was passed.
     * Make sure passed model is of the correct class.
     * Check other model is loaded so id can be gotten.
     *
     * @param int|Model $otherEntity //if int, then it's only an ID
     * @param IntermediateModel $mToMModel
     * @return Model
     * @throws Exception
     */
    protected function getOtherEntity(int|Model $otherEntity, IntermediateModel $mToMModel): Model
    {
        $otherModelClass = $mToMModel->getOtherModelClass($this);
        if (is_object($otherEntity)) {
            //only check if it's a model of the correct class; also check if accidentally $this was passed
            if (get_class($otherEntity) !== $otherModelClass) {
                throw new Exception(
                    'Object of wrong class was passed: ' . $mToMModel->getOtherModelClass($this)
                    . 'expected, ' . get_class($otherEntity) . ' passed.'
                );
            }
        } else {
            $id = $otherEntity;
            $otherEntity = new $otherModelClass($this->getPersistence());
            $otherEntity = $otherEntity->tryLoad($id);
        }

        //make sure entity is loaded
        if (!$otherEntity || !$otherEntity->isLoaded()) {
            throw new Exception('otherEntity could not be loaded in ' . __FUNCTION__);
        }

        return $otherEntity;
    }
}