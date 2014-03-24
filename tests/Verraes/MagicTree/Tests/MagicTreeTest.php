<?php

namespace Verraes\MagicTree\Tests;

use PHPUnit_Framework_TestCase;
use Verraes\MagicTree\Knot;

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
                        ->description('quite likeable');
        $this->tree
            ->colors['red']
                ->mars->species['fishlike']
                    ->intelligent['1850-1899']
                        ->discovered('by accident')
                        ->description('a bit smelly');
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
  |  |  |  |- fishlike
  |  |  |  |  |- intelligent
  |  |  |  |  |  |- 1850-1899
  |  |  |  |  |  |  |- discovered: "by accident"
  |  |  |  |  |  |  |- description: "a bit smelly"
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
                            "description": "quite likeable"
                        }
                    }
                },
                "fishlike": {
                    "intelligent": {
                        "1850-1899": {
                            "discovered": "by accident",
                            "description": "a bit smelly"
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
                                    ],
                                ],
                            ],
                            'fishlike' => [
                                'intelligent' => [
                                    '1850-1899' => [
                                        'discovered' => 'by accident',
                                        'description' => 'a bit smelly',
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


}
 