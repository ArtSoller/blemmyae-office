<?php

namespace Cra\CtPeople;

use Scm\Entity\CustomPostTypeGenerator;

class TestimonialGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $this->class->addConstant('GRAPHQL_NAME', 'Testimonial');
        $this->class->addConstant('GRAPHQL_PLURAL_NAME', 'Testimonials');
    }

    /**
     * @inheritdoc
     */
    protected function addMethods(): void
    {
    }
}
