<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace PHPSA\Visitor\Expression;

use PhpParser\Node\Expr\Variable;
use PHPSA\CompiledExpression;
use PHPSA\Context;
use PHPSA\Definition\ClassDefinition;
use PHPSA\Visitor\Expression;

class MethodCall extends AbstractExpressionCompiler
{
    protected $name = '\PhpParser\Node\Expr\MethodCall';

    /**
     * @param \PhpParser\Node\Expr\MethodCall $expr
     * @param Context $context
     * @return CompiledExpression
     */
    public function compile($expr, Context $context)
    {
        if ($expr->var instanceof Variable) {
            $symbol = $context->getSymbol($expr->var->name);
            if ($symbol) {
                switch ($symbol->getType()) {
                    case CompiledExpression::OBJECT:
                    case CompiledExpression::DYNAMIC:
                        $symbol->incUse();

                        /** @var ClassDefinition $calledObject */
                        $calledObject = $symbol->getValue();
                        if ($calledObject instanceof ClassDefinition) {
                            $methodName = false;

                            if (is_string($expr->name)) {
                                $methodName = $expr->name;
                            } elseif ($expr->name instanceof Variable) {
                                $methodName = $expr->name->name;
                            }

                            if ($methodName) {
                                if (!$calledObject->hasMethod($methodName)) {
                                    $context->notice(
                                        'undefined-mcall',
                                        sprintf('Method %s() does not exist in %s scope', $methodName, $expr->var->name),
                                        $expr
                                    );

                                    //it's needed to exit
                                    return new CompiledExpression();
                                }

                                if ($calledObject->getMethod($methodName)->isStatic()) {
                                    $context->notice(
                                        'undefined-mcall',
                                        sprintf('Method %s() is a static function but called like class method in $%s variable', $methodName, $expr->var->name),
                                        $expr
                                    );
                                }

                                return new CompiledExpression();
                            }

                            return new CompiledExpression();
                        }

                        /**
                         * It's a wrong type or value, maybe it's implemented and We need to fix it in another compilers
                         */
                        $context->debug('Unknown $calledObject - is ' . gettype($calledObject));
                        return new CompiledExpression();
                }

                $context->notice(
                    'variable-wrongtype.mcall',
                    sprintf('Variable %s is not object\\callable and cannot be called like this', $expr->var->name),
                    $expr
                );
                return new CompiledExpression();
            } else {
                $context->notice(
                    'undefined-variable.mcall',
                    sprintf('Variable %s is not defined in this scope', $expr->var->name),
                    $expr
                );

                return new CompiledExpression();
            }
        }

        $expression = new Expression($context);
        $expression->compile($expr->var);

        $context->debug('Unknown method call');
        return new CompiledExpression();
    }
}
