<?php

namespace Cra\BlemmyaePpworks;

use Scm\Entity\CustomPostTypeGenerator;

class PpworksSegmentGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    public function __construct(string $className, string $machineName, string $namespace)
    {
        parent::__construct($className, $machineName, $namespace);
    }

    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $graphqlName = 'PpworksSegment';
        $this->class->addConstant('TAXONOMY__SHOW', 'ppworks_show');
        $this->class->addConstant('TAXONOMY__SEGMENT', 'ppworks_segment_type');
        $this->class->addConstant('TAXONOMY__TAG', 'ppworks_tag');
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
