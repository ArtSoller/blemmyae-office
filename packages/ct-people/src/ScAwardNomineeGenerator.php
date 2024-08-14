<?php

namespace Cra\CtPeople;

use Scm\Entity\CustomPostTypeGenerator;

class ScAwardNomineeGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $graphqlName = 'ScAwardNominee';
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
