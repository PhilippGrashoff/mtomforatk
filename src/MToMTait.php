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
     * 1) adds HasMany Reference linking the junction model.
     * 2) adds after delete hook which deletes any junctions models linked to the deleted "main" model.
     * This way, no outdated junction models exist.
     * Returns HasMany reference for further modifying reference if needed.
     *
     * @param class-string<JunctionModel> $mtomClassName
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
}