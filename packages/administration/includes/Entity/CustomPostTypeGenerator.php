<?php

/**
 * CustomPostTypeGenerator class.
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Scm\Entity;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

/**
 * CustomPostTypeGenerator class.
 */
abstract class CustomPostTypeGenerator
{
    protected ClassType $class;
    protected PhpNamespace $namespace;

    /**
     * CustomPostType constructor.
     *
     * @param string $className
     * @param string $machineName
     * @param string|null $namespace
     */
    public function __construct(string $className, string $machineName, ?string $namespace = null)
    {
        $this->namespace = new PhpNamespace($namespace ? "Cra\\$namespace" : "Cra\Ct$className");
        $this->namespace->addUse(CustomPostType::class);

        $this->class = new ClassType("{$className}CT");
        $this->class
            ->setFinal()
            ->setExtends(CustomPostType::class)
            ->addComment("$className class.\n@phpcs:disable Generic.Files.LineLength.TooLong\n")
            ->addConstant('POST_TYPE', $machineName);

        $this->addAcfConstants($machineName);
        $this->addConstants();
        $this->addConstructor();
        $this->addMethods();

        $this->namespace->add($this->class);
        $printer = new PsrPrinter();
        $printer->indentation = '    ';
        echo $printer->printNamespace($this->namespace);
    }

    /**
     * Create ACF constants for given content type.
     *
     * @param string $machineName
     */
    private function addAcfConstants(string $machineName): void
    {
        $groups = acf_get_field_groups(['post_type' => $machineName]);
        foreach ($groups as $group) {
            $groupName = str_replace(' ', '_', $group['title']);
            $fields = acf_get_fields($group['key']);

            foreach ($fields as $field) {
                $fieldConst = strtoupper("GROUP_{$groupName}__FIELD_{$field['name']}");
                $this->class->addConstant($fieldConst, $field['key']);

                $subfields = $field['sub_fields'] ?? [];
                foreach ($subfields as $subfield) {
                    $this->class->addConstant(
                        strtoupper("{$fieldConst}__SUBFIELD_{$subfield['name']}"),
                        $subfield['key']
                    );
                }
            }
        }
    }

    /**
     * Add custom constructor.
     */
    private function addConstructor(): void
    {
        $function = new Method('__construct');
        $function->setVisibility('public');
        $function->addComment('Initialize hooks.');
        $function->addBody('parent::__construct();');
        $function->addBody('add_filter(\'register_post_type_args\', static function ($args, $postType) {');
        $function->addBody('    if (self::POST_TYPE === $postType) {');
        $function->addBody('        $args[\'show_in_graphql\'] = true;');
        $function->addBody('        $args[\'graphql_single_name\'] = self::GRAPHQL_NAME;');
        $function->addBody('        $args[\'graphql_plural_name\'] = self::GRAPHQL_PLURAL_NAME;');
        $function->addBody('    }');
        $function->addBody('    return $args;');
        $function->addBody('}, 10, 2);');
        $this->class->addMember($function);
    }

    /**
     * Add content type specific constants to the generated class.
     */
    abstract protected function addConstants(): void;

    /**
     * Add content type specific methods to the generated class.
     */
    abstract protected function addMethods(): void;
}
