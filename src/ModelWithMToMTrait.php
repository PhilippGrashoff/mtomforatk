<?php declare(strict_types=1);

namespace mtomforatk;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Atk4\Data\Reference;


/**
 * @extends Model<Model>
 */
trait ModelWithMToMTrait
{

    /**
     *  Create a new MToM relation, e.g. a new StudentToLesson record. Called from either Student or Lesson class.
     *  First checks if record does exist already, and only then adds new relation.
     *
     * @param MToMModel $mToMModel
     * @param string|int|Model $otherModel //if string or int, then it's only an ID
     * @param array<string,mixed> $additionalFields
     * @return MToMModel
     * @throws Exception
     * @throws \Atk4\Core\Exception
     */
    public function addMToMRelation(
        MToMModel $mToMModel,
        string|int|Model $otherModel,
        array $additionalFields = []
    ): MToMModel {
        //$this needs to be loaded to get ID
        $this->assertIsLoaded();
        $otherModel = $this->getOtherEntity($otherModel, $mToMModel);
        //check if reference already exists, if so update existing record only
        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherModel);
        $mToMModel->tryLoadAny();

        //set values
        $mToMModel->set($mToMModel->getFieldNameForModel($this), $this->getId());
        $mToMModel->set($mToMModel->getFieldNameForModel($otherModel), $otherModel->getId());

        //set additional field values
        foreach ($additionalFields as $fieldName => $value) {
            $mToMModel->set($fieldName, $value);
        }

        //no reload necessary after insert
        $mToMModel->reloadAfterSave = false;
        //if that record already exists mysql will throw an error if unique index is set, catch here
        $mToMModel->save();
        $mToMModel->addReferenceEntity($this);
        $mToMModel->addReferenceEntity($otherModel);

        return $mToMModel;
    }


    /**
     *  method used to remove a MToMModel record like StudentToLesson. Either used from Student or Lesson class.
     *  GuestToGroup etc.
     *
     * @param MToMModel $mToMModel
     * @param string|int|Model $otherModel //if string or int, then it's only an ID
     * @return MToMModel
     * @throws Exception
     */
    public function removeMToMRelation(MToMModel $mToMModel, string|int|Model $otherModel): MToMModel
    {
        //$this needs to be loaded to get ID
        $this->assertIsLoaded();
        $otherModel = $this->getOtherEntity($otherModel, $mToMModel);

        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherModel);
        //loadAny as it will throw exception when record is not found
        $mToMModel->loadAny();
        $mToMModel->delete();

        return $mToMModel;
    }


    /**
     * checks if a MtoM reference to the given object exists or not, e.g. if a StudentToLesson record exists for a
     * specific student and lesson
     *
     * @param MToMModel $mToMModel
     * @param string|int|Model $otherModel //if string or int, then it's only an ID
     * @return bool
     * @throws Exception
     */
    public function hasMToMRelation(MToMModel $mToMModel, string|int|Model $otherModel): bool
    {
        $this->assertIsLoaded();
        $otherModel = $this->getOtherEntity($otherModel, $mToMModel);

        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherModel);
        $mToMModel->tryLoadAny();

        return $mToMModel->isLoaded();
    }

    /**
     * 1) adds HasMany Reference to intermediate model.
     * 2) adds after delete hook which deletes any intermediate model linked to the deleted "main" model.
     * This way, no outdated intermediate models exist.
     * Returns HasMany reference for further modifying reference if needed.
     *
     * @param class-string<MToMModel> $mtomClassName
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
     * @param string|int|Model $otherModel //if string or int, then it's only an ID
     * @param MToMModel $mToMModel
     * @return Model
     * @throws Exception
     */
    protected function getOtherEntity(string|int|Model $otherModel, MToMModel $mToMModel): Model
    {
        /** @var class-string<Model> $otherModelClass */
        $otherModelClass = $mToMModel->getOtherModelClass($this);
        if (is_object($otherModel)) {
            //only check if it's a model of the correct class; also check if accidentally $this was passed
            if (get_class($otherModel) !== $otherModelClass) {
                throw new Exception(
                    'Object of wrong class was passed: ' . $mToMModel->getOtherModelClass($this)
                    . 'expected, ' . get_class($otherModel) . ' passed.'
                );
            }
        } else {
            $id = $otherModel;
            $otherModel = new $otherModelClass($this->getPersistence());
            $otherModel->tryLoad($id);
        }

        //make sure object is loaded
        if (!$otherModel->isLoaded()) {
            throw new Exception('Object could not be loaded in ' . __FUNCTION__);
        }

        return $otherModel;
    }
}