<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\DefinitionFinder\Test;

use type Facebook\DefinitionFinder\LegacyFileParser;
use type Facebook\DefinitionFinder\ScannedClassish;
use namespace HH\Lib\Vec;

class FinalTest extends \PHPUnit_Framework_TestCase {
  private ?vec<ScannedClassish> $classes;

  <<__Override>>
  protected function setUp(): void {
    $parser = LegacyFileParser::FromFile(__DIR__.'/data/finals.php');
    $this->classes = $parser->getClasses();
  }

  public function testClassIsFinal(): void {
    $this->assertEquals(
      vec[true, false],
      Vec\map($this->classes?? vec[], $x ==> $x->isFinal()),
      'isFinal',
    );
  }

  public function testMethodsAreFinal(): void {
    $class = $this->classes ? $this->classes[1] : null;
    $this->assertEquals(
      vec[true, false],
      Vec\map($class?->getMethods()?? vec[], $x ==> $x->isFinal()),
      'isFinal',
    );
  }
}
