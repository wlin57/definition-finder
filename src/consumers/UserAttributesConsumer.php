<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\DefinitionFinder;

use type Facebook\DefinitionFinder\Expression\StaticScalarExpression;
use namespace HH\Lib\C;

final class UserAttributesConsumer extends Consumer {
  public function getUserAttributes(): AttributeMap {
    $attrs = dict[];
    while (true) {
      $this->consumeWhitespace();

      list($name, $_) = $this->tq->shift();
      if (!C\contains_key($attrs, $name)) {
        $attrs[$name] = vec[];
      }

      $this->consumeWhitespace();

      list($t, $ttype) = $this->tq->shift();
      if ($ttype === \T_SR) { // this was the last attribute
        return $attrs;
      }
      if ($t === ',') { // there's another
        continue;
      }

      // this attribute has values
      invariant(
        $t === '(',
        "Expected attribute name to be followed by >>, (, or , at line %d; ".
        "got '%s' (%d) for attr '%s'",
        $this->tq->getLine(),
        $t,
        $ttype,
        $name,
      );

      // Possibly multiple values
      while ($this->tq->haveTokens()) {

        $this->consumeWhitespace();

        $expr = StaticScalarExpression::match($this->tq);
        if ($expr === null) {
          list($tn, $_) = $this->tq->peek();
          invariant(
            $tn === ')', // <<Foo()>>
            "Invalid attribute value token at line %d: ('%s') %d",
            $this->tq->getLine(),
            $t,
            $ttype,
          );
          list($t, $type) = $this->tq->shift();
          break;
        }

        $attrs[$name][] = $expr->getValue();
        list($t, $ttype) = $this->tq->shift();

        if ($t === ')') {
          break;
        }

        invariant(
          $t === ',',
          'Expected attribute value to be followed by , or ) at line %d',
          $this->tq->getLine(),
        );
        $this->consumeWhitespace();

        // Handle trailing commas
        list($t, $ttype) = $this->tq->peek();
        if ($t === ')') {
          $this->tq->shift();
          break;
        }
      }

      $this->consumeWhitespace();
      list($t, $ttype) = $this->tq->shift();
      if ($ttype === \T_SR) {
        return $attrs;
      }
      invariant(
        $t === ',',
        'Expected attribute value list to be followed by >> or , at line %d',
        $this->tq->getLine(),
      );
      $this->consumeWhitespace();
    }
    invariant_violation(
      'attribute list did not end at line %d',
      $this->tq->getLine(),
    );
  }
}
