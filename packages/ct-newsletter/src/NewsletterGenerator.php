<?php

namespace Cra\CtNewsletter;

use Scm\Entity\CustomPostTypeGenerator;

class NewsletterGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $graphqlName = 'Newsletter';
        $this->class->addConstant('GENERATE_NEWSLETTER_POST_ID', 'options');
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
