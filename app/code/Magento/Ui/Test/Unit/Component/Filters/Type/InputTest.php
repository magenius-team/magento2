<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface as UiContext;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Input;
use Magento\Ui\View\Element\BookmarkContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{
    /**
     * @var UiContext|MockObject
     */
    protected $contextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var FilterBuilder|MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var FilterModifier|MockObject
     */
    protected $filterModifierMock;

    /**
     * @var BookmarkContextInterface|MockObject
     */
    private $bookmarkContextMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockForAbstractClass(
            UiContext::class,
            [],
            '',
            false
        );
        $this->uiComponentFactory = $this->createPartialMock(
            UiComponentFactory::class,
            ['create']
        );
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->filterModifierMock = $this->createPartialMock(
            FilterModifier::class,
            ['applyFilterModifier']
        );

        $this->bookmarkContextMock = $this->getMockForAbstractClass(
            BookmarkContextInterface::class
        );
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName(): void
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        $this->bookmarkContextMock->expects($this->once())
            ->method('getFilterData');
        $date = new Input(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            [],
            $this->bookmarkContextMock
        );

        $this->assertSame(Input::NAME, $date->getComponentName());
    }

    /**
     * Run test prepare method
     *
     * @param string $name
     * @param array $filterData
     * @param array|null $expectedCondition
     * @dataProvider getPrepareDataProvider
     * @return void
     */
    public function testPrepare(string $name, array $filterData, ?array $expectedCondition): void
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        /** @var UiComponentInterface $uiComponent */
        $uiComponent = $this->getMockForAbstractClass(
            UiComponentInterface::class,
            [],
            '',
            false
        );

        $uiComponent->expects($this->any())
            ->method('getContext')
            ->willReturn($this->contextMock);

        $this->contextMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn(Input::NAME);
        $this->contextMock->expects($this->any())
            ->method('addComponentDefinition')
            ->with(Input::NAME, ['extends' => Input::NAME]);
        $dataProvider = $this->getMockForAbstractClass(
            DataProviderInterface::class,
            [],
            '',
            false
        );

        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProvider);

        $this->bookmarkContextMock->expects($this->once())
            ->method('getFilterData')
            ->willReturn($filterData);
        $this->contextMock->expects($this->any())
            ->method('getRequestParam')
            ->with(UiContext::FILTER_VAR)
            ->willReturn($filterData);

        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->with($name, Input::COMPONENT, ['context' => $this->contextMock])
            ->willReturn($uiComponent);

        if ($expectedCondition !== null) {
            $this->filterBuilderMock->expects($this->once())
                ->method('setConditionType')
                ->with('like')
                ->willReturnSelf();

            $this->filterBuilderMock->expects($this->once())
                ->method('setField')
                ->with($name)
                ->willReturnSelf();

            $this->filterBuilderMock->expects($this->once())
                ->method('setValue')
                ->with($expectedCondition['like'])
                ->willReturnSelf();

            $filterMock = $this->getMockBuilder(Filter::class)
                ->disableOriginalConstructor()
                ->getMock();

            $this->filterBuilderMock->expects($this->once())
                ->method('create')
                ->willReturn($filterMock);
        }

        $date = new Input(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            ['name' => $name],
            $this->bookmarkContextMock
        );

        $date->prepare();
    }

    /**
     * @return array
     */
    public function getPrepareDataProvider(): array
    {
        return [
            [
                'test_date',
                ['test_date' => ''],
                null,
            ],
            [
                'test_date',
                ['test_date' => null],
                null,
            ],
            [
                'test_date',
                ['test_date' => '0'],
                ['like' => '%0%'],
            ],
            [
                'test_date',
                ['test_date' => 'some_value'],
                ['like' => '%some\_value%'],
            ],
            [
                'test_date',
                ['test_date' => '%'],
                ['like' => '%\%%'],
            ],
        ];
    }
}
