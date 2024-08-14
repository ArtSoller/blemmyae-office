<?php

namespace Cra\CtPeople;

use Scm\Entity\CustomPostTypeGenerator;

class PeopleGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $this->class->addConstant('TAXONOMY__JOB_TITLE', 'job_title');
        $this->class->addConstant('TAXONOMY__PEOPLE_TYPE', 'people_type');
        $this->class->addConstant('TERM__INDUSTRY_FIGURE__ID', 72347);
        $this->class->addConstant('TERM__SPEAKER__ID', 72346);
        $this->class->addConstant('TAXONOMY__COMMUNITY_REGION', 'community_region');
        $this->class->addConstant('COMMUNITY_REGION__TERM__UNCATEGORIZED', 'Uncategorized');
        $this->class->addConstant('TAXONOMY__SWOOGO_SPEAKER_TYPE', 'swoogo_speaker_type');
        $this->class->addConstant('GRAPHQL_NAME', 'Person');
        $this->class->addConstant('GRAPHQL_PLURAL_NAME', 'People');
    }

    /**
     * @inheritdoc
     */
    protected function addMethods(): void
    {
        // TODO: Implement addMethods() method.
    }
}
