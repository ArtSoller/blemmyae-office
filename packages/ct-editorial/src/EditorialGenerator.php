<?php

namespace Cra\CtEditorial;

use Scm\Entity\CustomPostTypeGenerator;

class EditorialGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $graphqlName = 'Editorial';
        $this->class->addConstant('EDITORIAL_TYPE_TAXONOMY', 'editorial_type');
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
