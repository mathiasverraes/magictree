<?php

namespace Verraes\MagicTree\Tests;

use PHPUnit_Framework_TestCase;
use Verraes\MagicTree\Knot;
use Verraes\MagicTree\Node;

final class MagicTreeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Knot
     */
    private $tree;

    protected function setUp()
    {
        $this->tree = new Knot();

        $this->tree
            ->colors['red']
            ->mars->species['vertebrae']
            ->intelligent['1850-1899']
            ->discovered('on a sunday')
            ->description('quite likeable')
            ->isNice(true)
        ;
        $this->tree
            ->colors['red']
            ->mars->species['fishlike']
            ->intelligent['1850-1899']
            ->discovered('by accident')
            ->description('a bit smelly')
            ->isNice(false)
        ;
        $this->tree
            ->colors['blue']
            ->pluto->species['insects'] = 'gasfly';



    }


    /**
     * @test
     */
    public function it_should_render_to_ascii()
    {
        $expected = <<<TREE
- colors
  |- red
  |  |- mars
  |  |  |- species
  |  |  |  |- vertebrae
  |  |  |  |  |- intelligent
  |  |  |  |  |  |- 1850-1899
  |  |  |  |  |  |  |- discovered: "on a sunday"
  |  |  |  |  |  |  |- description: "quite likeable"
  |  |  |  |  |  |  |- isNice: true
  |  |  |  |- fishlike
  |  |  |  |  |- intelligent
  |  |  |  |  |  |- 1850-1899
  |  |  |  |  |  |  |- discovered: "by accident"
  |  |  |  |  |  |  |- description: "a bit smelly"
  |  |  |  |  |  |  |- isNice: false
  |- blue
  |  |- pluto
  |  |  |- species
  |  |  |  |- insects: "gasfly"

TREE;
        $this->assertEquals($expected, $this->tree->toAscii());
    }



    /**
     * @test
     */
    public function it_should_remove_keys()
    {
        $this->tree->colors->remove('red');

        $expected = <<<TREE
- colors
  |- blue
  |  |- pluto
  |  |  |- species
  |  |  |  |- insects: "gasfly"

TREE;
        $this->assertEquals($expected, $this->tree->toAscii());
    }
    /**
     * @test
     */
    public function it_should_filter_with_a_decider()
    {
        $this->tree->filter(function(Node $node) {
                $result = $node->has('isNice') && $node->isNice == true;
                return $result;
            });

        $expected = <<<TREE
- colors
  |- red
  |  |- mars
  |  |  |- species
  |  |  |  |- vertebrae
  |  |  |  |  |- intelligent
  |  |  |  |- fishlike
  |  |  |  |  |- intelligent
  |  |  |  |  |  |- 1850-1899
  |  |  |  |  |  |  |- discovered: "by accident"
  |  |  |  |  |  |  |- description: "a bit smelly"
  |  |  |  |  |  |  |- isNice: false
  |- blue
  |  |- pluto
  |  |  |- species
  |  |  |  |- insects: "gasfly"

TREE;
        $actual = $this->tree->toAscii();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_serialize_to_json()
    {
        $expected = <<<JSON
{"colors": {
    "red": {
        "mars": {
            "species": {
                "vertebrae": {
                    "intelligent": {
                        "1850-1899": {
                            "discovered": "on a sunday",
                            "description": "quite likeable",
                             "isNice": true
                        }
                    }
                },
                "fishlike": {
                    "intelligent": {
                        "1850-1899": {
                            "discovered": "by accident",
                            "description": "a bit smelly",
                             "isNice": false
                        }
                    }
                }
            }
        }
    },
    "blue": {
        "pluto": {
            "species": {
                "insects": "gasfly"
            }
        }
    }
}}
JSON;

        $this->assertJsonStringEqualsJsonString($expected, json_encode($this->tree));
    }

    /**
     * @test
     */
    public function it_should_cast_to_array()
    {
        $expected = [
            'colors' => [
                'red' => [
                    'mars' => [
                        'species' => [
                            'vertebrae' => [
                                'intelligent' => [
                                    '1850-1899' => [
                                        'discovered' => 'on a sunday',
                                        'description' => 'quite likeable',
                                        'isNice' => true,
                                    ],
                                ],
                            ],
                            'fishlike' => [
                                'intelligent' => [
                                    '1850-1899' => [
                                        'discovered' => 'by accident',
                                        'description' => 'a bit smelly',
                                        'isNice' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'blue' => [
                    'pluto' => [
                        'species' => [
                            'insects' => 'gasfly',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $this->tree->toArray());
    }

    /**
     * @test
     */
    public function it_should_be_sortable()
    {

        $tree = new Knot();

        $tree->things['b'] = 'second';
        $tree->things['c'] = 'third';
        $tree->things['a'] = 'first';

        $tree->things->sort('strcasecmp');

        $expected = <<<TREE
- things
  |- a: "first"
  |- b: "second"
  |- c: "third"

TREE;
        $this->assertEquals($expected, $tree->toAscii());
    }

    /**
     * @test
     */
    public function it_should_check_if_keys_exist()
    {
        $tree= new Knot;
        $tree->alfa->beta['gamma'] = 'foo';

        $this->assertTrue($tree->has('alfa'));
        $this->assertTrue($tree->has('alfa', 'beta'));
        $this->assertTrue($tree->has('alfa', 'beta', 'gamma'));

        $this->assertFalse($tree->has('delta'));
        $this->assertFalse($tree->has('delta', 'epsilon'));
        $this->assertFalse($tree->has('alfa', 'beta', 'epsilon'));
    }
}
 