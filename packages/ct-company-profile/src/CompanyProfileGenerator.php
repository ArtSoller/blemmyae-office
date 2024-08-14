<?php

namespace Cra\CtCompanyProfile;

use Scm\Entity\CustomPostTypeGenerator;

class CompanyProfileGenerator extends CustomPostTypeGenerator
{
    /**
     * @inheritdoc
     */
    protected function addConstants(): void
    {
        $graphqlName = 'CompanyProfile';
        $this->class->addConstant('TAXONOMY__COMPANY_PROFILE_TYPE', 'company_profile_type');
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
