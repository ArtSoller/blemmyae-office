<?php

namespace Cra\CtLearning;

use Scm\Entity\CustomPostTypeGenerator;

class LearningGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $graphqlName = 'Learning';
        $this->class->addConstant('VENDOR__SWOOGO', 'swoogo');
        $this->class->addConstant('VENDOR__GOTOWEBINAR', 'gotowebinar');
        $this->class->addConstant('VENDOR_TYPE__SWOOGO', 'Swoogo');
        $this->class->addConstant('VENDOR_TYPE__MSSP', 'Mssp');
        $this->class->addConstant('VENDOR_TYPE__CE2E', 'Ce2e');
        $this->class->addConstant('VENDOR_TYPE_GO_TO_WEBINAR', 'GoToWebinar');
        $this->class->addConstant('TERM_UNCATEGORIZED', 'Uncategorized');
        $this->class->addConstant('BRAND__TERM__CSF', 'Cybersecurity Collaboration Forum');
        $this->class->addConstant('TAXONOMY__COMMUNITY_REGION', 'community_region');
        $this->class->addConstant('TAXONOMY__TOPIC', 'topic');
        $this->class->addConstant('TAXONOMY__LEARNING_TYPE', 'learning_type');
        $this->class->addConstant('TAXONOMY__VENDOR_TYPE', 'learning_vendor_type');
        $this->class->addConstant('TAXONOMY__BRAND', 'brand');
        $this->class->addConstant('VENDOR__EXTERNAL_EVENT', 'external_event');
        $this->class->addConstant('GRAPHQL_NAME', $graphqlName);
        $this->class->addConstant('GRAPHQL_PLURAL_NAME', $graphqlName . 's');
    }

    /**
     * @inheritdoc
     */
    protected function addMethods(): void
    {
        // TODO: Implement addMethods() method.
    }
}
