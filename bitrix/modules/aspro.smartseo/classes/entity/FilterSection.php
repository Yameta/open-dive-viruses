<?php

namespace Aspro\Smartseo\Entity;

use Aspro\Smartseo;

class FilterSection extends Smartseo\Models\EO_SmartseoFilterSection
{
    public function setParentId($value) {
        parent::setParentId($value);

        $parentFilterSection = static::wakeUp($value);

        $parentFilterSection->fillDepthLevel();
        if($parentFilterSection) {
            $this->setDepthLevel($parentFilterSection->getDepthLevel() + 1);
        }
    }
}