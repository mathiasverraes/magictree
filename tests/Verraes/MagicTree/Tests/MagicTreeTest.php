<?php

namespace Verraes\MagicTree\Tests;

use PHPUnit_Framework_TestCase;
use Verraes\MagicTree\Knot;

final class MagicTreeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Knot
     */
    private $report;

    protected function setUp()
    {
        $this->report = new Knot();

        $this->report
            ->subjects['wiskunde']
                ->conclusions->domains['breuken']
                    ->clusteredLearningGoals['cluster15']
                        ->conclusion('ik concludeer dit')
                        ->desciption('beschrijving');
        $this->report
            ->subjects['wiskunde']
                ->conclusions->domains['getallen']
                    ->clusteredLearningGoals['cluster15']
                        ->conclusion('nikske')
                        ->desciption('beschrijving');
        $this->report
            ->subjects['nederlands']
                ->conclusions->domains['lezen'] = 'boeken';


    }


    /**
     * @test
     */
    public function it_should_render_to_ascii()
    {
        $expected = <<<TREE
- subjects
  |- wiskunde
  |  |- conclusions
  |  |  |- domains
  |  |  |  |- breuken
  |  |  |  |  |- clusteredLearningGoals
  |  |  |  |  |  |- cluster15
  |  |  |  |  |  |  |- conclusion: "ik concludeer dit"
  |  |  |  |  |  |  |- desciption: "beschrijving"
  |  |  |  |- getallen
  |  |  |  |  |- clusteredLearningGoals
  |  |  |  |  |  |- cluster15
  |  |  |  |  |  |  |- conclusion: "nikske"
  |  |  |  |  |  |  |- desciption: "beschrijving"
  |- nederlands
  |  |- conclusions
  |  |  |- domains
  |  |  |  |- lezen: "boeken"

TREE;

        $this->assertEquals($expected, $this->report->toAscii());
    }

    /**
     * @test
     */
    public function it_should_serialize_to_json()
    {
        $expected = <<<JSON
{"subjects": {
    "wiskunde": {
        "conclusions": {
            "domains": {
                "breuken": {
                    "clusteredLearningGoals": {
                        "cluster15": {
                            "conclusion": "ik concludeer dit",
                            "desciption": "beschrijving"
                        }
                    }
                },
                "getallen": {
                    "clusteredLearningGoals": {
                        "cluster15": {
                            "conclusion": "nikske",
                            "desciption": "beschrijving"
                        }
                    }
                }
            }
        }
    },
    "nederlands": {
        "conclusions": {
            "domains": {
                "lezen": "boeken"
            }
        }
    }
}}
JSON;

        $this->assertJsonStringEqualsJsonString($expected, json_encode($this->report));
    }

    /**
     * @test
     */
    public function it_should_cast_to_array()
    {
        $expected = [
            'subjects' => [
                'wiskunde' => [
                    'conclusions' => [
                        'domains' => [
                            'breuken' => [
                                'clusteredLearningGoals' => [
                                    'cluster15' => [
                                        'conclusion' => 'ik concludeer dit',
                                        'desciption' => 'beschrijving',
                                    ],
                                ],
                            ],
                            'getallen' => [
                                'clusteredLearningGoals' => [
                                    'cluster15' => [
                                        'conclusion' => 'nikske',
                                        'desciption' => 'beschrijving',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'nederlands' => [
                    'conclusions' => [
                        'domains' => [
                            'lezen' => 'boeken',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $this->report->toArray());
    }


}
 