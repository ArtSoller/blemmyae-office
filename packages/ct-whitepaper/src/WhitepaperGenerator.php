<?php

namespace Cra\CtWhitepaper;

use Scm\Entity\CustomPostTypeGenerator;

class WhitepaperGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $graphqlName = 'Whitepaper';
        $this->class->addConstant('HIDDEN_FROM_FEEDS_POST_STATUS', 'hidden_from_feeds');
        $this->class->addConstant('VENDOR__CONVERTR', 'convertr');
        $this->class->addConstant('VENDOR__INTERNAL_WHITEPAPER', 'internal_whitepaper');
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
