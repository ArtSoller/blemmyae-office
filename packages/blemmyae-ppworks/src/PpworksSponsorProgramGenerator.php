<?php

namespace Cra\BlemmyaePpworks;

use Scm\Entity\CustomPostTypeGenerator;

class PpworksSponsorProgramGenerator extends CustomPostTypeGenerator
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
        $graphqlName = 'PPWorksSponsorProgram';
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
