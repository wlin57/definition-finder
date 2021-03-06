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

use namespace Facebook\HHAST;
use namespace HH\Lib\{C, Str, Dict};

function context_with_group_use_declarations(
  ConsumerContext $context,
  vec<HHAST\NamespaceGroupUseDeclaration> $uses,
): ConsumerContext {
  foreach ($uses as $use) {
    $kind = $use->getKind();
    if ($kind instanceof HHAST\ConstToken) {
      continue;
    }
    if ($kind instanceof HHAST\FunctionToken) {
      continue;
    }

    $clauses = _Private\items_of_type(
      $use->getClauses(),
      HHAST\NamespaceUseClause::class,
    );
    $prefix = name_from_ast($use->getPrefix());
    foreach ($clauses as $clause) {
      if ($clause->getClauseKind()) {
        // only 'const' and 'function' are permitted here, so if we have one,
        // it doesn't affect typing
        continue;
      }
      $name = $prefix."\\".name_from_ast($clause->getName());

      $as = name_from_ast($clause->getAlias() ?? $clause->getName())
        |> Str\split($$, "\\")
        |> C\lastx($$);

      if ($kind instanceof HHAST\TypeToken) {
        $context['usedTypes'][$as] = $name;
        continue;
      } else if ($kind instanceof HHAST\NamespaceToken) {
        $context['usedNamespaces'][$as] = $name;
        continue;
      }

      invariant($kind === null, 'Unexpected kind: %s', \get_class($kind));
      $context['usedTypes'][$as] = $context['usedTypes'][$as] ?? $name;
      $context['usedNamespaces'][$as] =
        $context['usedNamespaces'][$as] ?? $name;
    }
  }
  return $context;
}
