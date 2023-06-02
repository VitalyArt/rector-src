<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;

use Iterator;
use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class ArrayParserTest extends AbstractTestCase
{
    private ArrayParser $arrayParser;

    private TokenIteratorFactory $tokenIteratorFactory;

    protected function setUp(): void
    {
        $this->boot();

        $this->arrayParser = $this->getService(ArrayParser::class);
        $this->tokenIteratorFactory = $this->getService(TokenIteratorFactory::class);
    }

    /**
     * @param ArrayItemNode[] $expectedArrayItemNodes
     */
    #[DataProvider('provideData')]
    public function test(string $docContent, array $expectedArrayItemNodes): void
    {
        $betterTokenIterator = $this->tokenIteratorFactory->create($docContent);

        $arrayItemNodes = $this->arrayParser->parseCurlyArray($betterTokenIterator);
        $this->assertEquals($expectedArrayItemNodes, $arrayItemNodes);
    }

    public static function provideData(): Iterator
    {
        yield ['{key: "value"}', [new ArrayItemNode(new StringNode('value'), 'key')]];

        yield ['{"key": "value"}', [
            new ArrayItemNode(new StringNode('value'), new StringNode('key')),
        ]];

        yield ['{"value", "value2"}', [
            new ArrayItemNode(new StringNode('value')),
            new ArrayItemNode(new StringNode('value2')),
        ]];
    }
}
