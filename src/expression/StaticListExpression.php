<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\DefinitionFinder\Expression;

use namespace Facebook\HHAST;
use namespace HH\Lib\Vec;

final class StaticListExpression extends Expression<vec<mixed>> {
  const type TNode = HHAST\EditableList;
  <<__Override>>
  protected static function matchImpl(
    HHAST\EditableList $n,
  ): ?Expression<vec<mixed>> {
    $items = $n->getItemsOfType(HHAST\EditableNode::class)
      |> Vec\map($$, $item ==> StaticExpression::match($item));
    $out = vec[];
    foreach ($items as $item) {
      if ($item === null) {
        return null;
      }
      $out[] = $item->getValue();
    }
    return new self($out);
  }
}
