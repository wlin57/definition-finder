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

use Facebook\DefinitionFinder\TokenQueue;

final class StaticVecExpression extends StaticArrayExpression
implements StaticVecLikeArrayExpression {
  public static function convertVec(vec<mixed> $values): mixed {
    return $values;
  }

  <<__Override>>
  protected static function consumeStart(TokenQueue $tq): ?string {
    list($_, $ttype) = $tq->shift();
    if ($ttype !== \T_VEC) {
      return null;
    }

    list($t, $_) = $tq->shift();
    if ($t !== '[') {
      return null;
    }

    return ']';
  }
}