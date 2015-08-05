<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchDSL\Tests\Unit\DSL\Aggregation;

use ONGR\ElasticsearchDSL\Aggregation\FiltersAggregation;
use ONGR\ElasticsearchDSL\BuilderInterface;

/**
 * Unit test for filters aggregation.
 */
class FiltersAggregationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if exception is thrown when not anonymous filter is without name.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage In not anonymous filters filter name must be set.
     */
    public function testIfExceptionIsThrown()
    {
        $mock = $this->getMockBuilder('ONGR\ElasticsearchDSL\BuilderInterface')->getMock();
        $aggregation = new FiltersAggregation('test_agg');
        $aggregation->addFilter($mock);
    }

    /**
     * Test GetArray method.
     */
    public function testFiltersAggregationGetArray()
    {
        $mock = $this->getMockBuilder('ONGR\ElasticsearchDSL\BuilderInterface')->getMock();
        $aggregation = new FiltersAggregation('test_agg');
        $aggregation->setAnonymous(true);
        $aggregation->addFilter($mock, 'name');
        $result = $aggregation->getArray();
        $this->assertArrayHasKey('filters', $result);
    }

    /**
     * Tests getType method.
     */
    public function testFiltersAggregationGetType()
    {
        $aggregation = new FiltersAggregation('foo');
        $result = $aggregation->getType();
        $this->assertEquals('filters', $result);
    }

    /**
     * Test for filter aggregation toArray() method.
     */
    public function testToArray()
    {
        $aggregation = new FiltersAggregation('test_agg');
        $filter = $this->getMockBuilder('ONGR\ElasticsearchDSL\BuilderInterface')
            ->setMethods(['toArray', 'getType'])
            ->getMockForAbstractClass();
        $filter->expects($this->any())
            ->method('getType')
            ->willReturn('test_filter');
        $filter->expects($this->any())
            ->method('toArray')
            ->willReturn(['test_field' => ['test_value' => 'test']]);

        $aggregation->addFilter($filter, 'first');
        $aggregation->addFilter($filter, 'second');
        $results = $aggregation->toArray();
        $expected = [
            'test_agg' => [
                'filters' => [
                    'filters' => [
                        'first' => [
                            'test_filter' => [
                                'test_field' => [
                                    'test_value' => 'test',
                                ],
                            ],
                        ],
                        'second' => [
                            'test_filter' => [
                                'test_field' => [
                                    'test_value' => 'test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $results);
    }

    /**
     * Tests if filters can be passed to constructor.
     */
    public function testConstructorFilter()
    {
        /** @var BuilderInterface|\PHPUnit_Framework_MockObject_MockObject $builderInterface1 */
        $builderInterface1 = $this->getMockForAbstractClass('ONGR\ElasticsearchDSL\BuilderInterface');
        $builderInterface1->expects($this->any())->method('getType')->willReturn('type1');
        /** @var BuilderInterface|\PHPUnit_Framework_MockObject_MockObject $builderInterface2 */
        $builderInterface2 = $this->getMockForAbstractClass('ONGR\ElasticsearchDSL\BuilderInterface');
        $builderInterface2->expects($this->any())->method('getType')->willReturn('type2');

        $aggregation = new FiltersAggregation(
            'test',
            [
                'filter1' => $builderInterface1,
                'filter2' => $builderInterface2,
            ]
        );

        $this->assertSame(
            [
                'test' => [
                    'filters' => [
                        'filters' => [
                            'filter1' => ['type1' => null],
                            'filter2' => ['type2' => null],
                        ],
                    ],
                ],
            ],
            $aggregation->toArray()
        );

        $aggregation = new FiltersAggregation(
            'test',
            [
                'filter1' => $builderInterface1,
                'filter2' => $builderInterface2,
            ],
            true
        );

        $this->assertSame(
            [
                'test' => [
                    'filters' => [
                        'filters' => [
                            ['type1' => null],
                            ['type2' => null],
                        ],
                    ],
                ],
            ],
            $aggregation->toArray()
        );
    }
}
