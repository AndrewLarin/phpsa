<?php

namespace Tests\PHPSA\Expression\BinaryOp;

use PhpParser\Node;
use PHPSA\CompiledExpression;
use PHPSA\Compiler\Expression;
use PHPSA\Compiler\Expression\Operators\UnaryMinus;

/**
 * Class ExpressionCompilerTest
 * @package Tests\PHPSA\Expression\BinaryOp
 */
class ExpressionCompilerTest extends \Tests\PHPSA\TestCase
{
    public function testPassUnexpectedExpression()
    {
        $expr = new UnaryMinus();
        $this->assertSame('PhpParser\Node\Expr\UnaryMinus', $expr->getName());

        $this->setExpectedException(
            'InvalidArgumentException',
            'Passed $expression must be instance of PhpParser\Node\Expr\UnaryMinus'
        );
        $expr->pass($this->newFakeScalarExpr(), $this->getContext());
    }
}
