<?php

namespace Verraes\MagicTree\Tests;

use PHPUnit_Framework_TestCase;
use Verraes\MagicTree\Branch;
use Verraes\MagicTree\Leaf;
use Verraes\MagicTree\Node;

final class MagicTreeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Branch
     */
    private $tree;

    protected function setUp()
    {
        $this->tree = new Branch();

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
    public function it_should_be_sortable_by_key()
    {

        $tree = new Branch();

        $tree->things['b'] = 'second';
        $tree->things['c'] = 'third';
        $tree->things['a'] = 'first';

        $tree->things->ksort('strcasecmp');

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
    public function it_should_be_sortable_by_value()
    {
        $tree = new Branch();

        $tree->things['id1']->myvalue('beta');
        $tree->things['id2']->myvalue('alfa');
        $tree->things['id3']->myvalue('gamma');

        $comparator = function ($left, $right) { return strcmp($left->myvalue, $right->myvalue); };
        $tree->things->sort($comparator);

        $expected = <<<TREE
- things
  |- id2
  |  |- myvalue: "alfa"
  |- id1
  |  |- myvalue: "beta"
  |- id3
  |  |- myvalue: "gamma"

TREE;
        $this->assertEquals($expected, $tree->toAscii());
    }

    /**
     * @test
     */
    public function it_should_check_if_keys_exist()
    {
        $tree= new Branch;
        $tree->alfa->beta['gamma'] = 'foo';

        $this->assertTrue($tree->has('alfa'));
        $this->assertTrue($tree->has('alfa', 'beta'));
        $this->assertTrue($tree->has('alfa', 'beta', 'gamma'));

        $this->assertFalse($tree->has('delta'));
        $this->assertFalse($tree->has('delta', 'epsilon'));
        $this->assertFalse($tree->has('alfa', 'beta', 'epsilon'));
    }

    /**
     * @test
     */
    public function it_should_return_the_count_of_branches_children()
    {
        $tree = new Branch();
        $tree->things['b'] = 'second';
        $tree->things['c'] = 'third';
        $tree->things['a'] = 'first';
        $this->assertCount(1, $tree);
        $this->assertCount(3, $tree->things);
    }

    /**
     * @test
     */
    public function it_should_execute_a_method_Where_a_key_is_found()
    {
        $this->tree->where('1850-1899', function(Branch $branch) {
                $discovered = $branch->discovered;
                $branch->discoverdMutated($discovered . '!');
            });

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
  |  |  |  |  |  |  |- discoverdMutated: "on a sunday!"
  |  |  |  |- fishlike
  |  |  |  |  |- intelligent
  |  |  |  |  |  |- 1850-1899
  |  |  |  |  |  |  |- discovered: "by accident"
  |  |  |  |  |  |  |- description: "a bit smelly"
  |  |  |  |  |  |  |- isNice: false
  |  |  |  |  |  |  |- discoverdMutated: "by accident!"
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
    public function it_should_confirm_or_deny_being_empty()
    {
        $emptyBranch = new Branch();
        $leaf = new Leaf(2);

        $this->assertTrue($emptyBranch->isEmpty());
        $this->assertFalse($leaf->isEmpty());

        $nonEmptyBranch = new Branch();
        $nonEmptyBranch
            ->branchA
                ->something(5);

        $this->assertFalse($nonEmptyBranch->isEmpty());
    }

    /**
     * @test
     */
    public function getting_a_leaf_should_return_its_value()
    {
        $branch = new Branch();
        $branch->integer(5);

        $this->assertInternalType('integer', $branch->integer);
        $this->assertInternalType('integer', $branch['integer']);

        $branch->string("MagicTree Rules");

        $this->assertInternalType('string', $branch->string);
        $this->assertInternalType('string', $branch['string']);

    }

    /**
     * @test
     */
    public function it_should_move_an_existing_complete_branch_to_a_different_location()
    {
        $this->tree->move(
            explode('.', 'colors.red.mars.species.fishlike'),
            explode('.', 'colors.blue.pluto.species.fishlike')
        );

        $moved = <<<TREE
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
  |- blue
  |  |- pluto
  |  |  |- species
  |  |  |  |- insects: "gasfly"
  |  |  |  |- fishlike
  |  |  |  |  |- intelligent
  |  |  |  |  |  |- 1850-1899
  |  |  |  |  |  |  |- discovered: "by accident"
  |  |  |  |  |  |  |- description: "a bit smelly"
  |  |  |  |  |  |  |- isNice: false

TREE;

        $this->assertEquals($moved, $this->tree->toAscii());
    }

    /**
     * @test
     */
    public function it_should_do_nothing_when_moving_an_unexisting_branch()
    {
        $this->tree->move('some.unexisting.branch', 'some.random.branch');

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
    public function it_should_move_a_leaf_to_a_different_location()
    {
        $tree = new Branch();
        $tree->colors['blue'] = true;

        $tree->move('colors.blue', 'colors.red');

        $moved = <<<TREE
- colors
  |- red: true

TREE;

        $this->assertEquals($moved, $tree->toAscii());
    }
}

 