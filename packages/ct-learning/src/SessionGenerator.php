<?php

namespace Cra\CtLearning;

use Scm\Entity\CustomPostTypeGenerator;

class SessionGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritDoc
     */
    protected function addConstants(): void
    {
        $graphqlName = 'Session';
        $this->class->addConstant('VENDOR__SWOOGO', 'swoogo');
        $this->class->addConstant('VENDOR_TYPE__SWOOGO', 'Swoogo');
        $this->class->addConstant('TAXONOMY__VENDOR_TYPE', 'learning_vendor_type');
        $this->class->addConstant('GRAPHQL_NAME', $graphqlName);
        $this->class->addConstant('GRAPHQL_PLURAL_NAME', $graphqlName . 's');
    }

    /**
     * @inheritDoc
     */
    protected function addMethods(): void
    {
        // TODO: Implement addMethods() method.
    }
}
