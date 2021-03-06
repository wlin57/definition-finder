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
use namespace Facebook\TypeAssert;
use namespace HH\Lib\{C, Dict, Str, Vec};

function scope_from_ast_and_ns(
  ConsumerContext $context,
  ?HHAST\EditableList $ast,
  ?string $ns,
): ScannedScope {
  if ($ast === null) {
    $ast = new HHAST\EditableList(vec[]);
  }

  $context['namespace'] = $ns;
  $context = $context
    |> context_with_use_declarations(
      $$,
      _Private\items_of_type($ast, HHAST\NamespaceUseDeclaration::class),
    )
    |> context_with_group_use_declarations(
      $$,
      _Private\items_of_type($ast, HHAST\NamespaceGroupUseDeclaration::class),
    );

  $classish = _Private\items_of_type($ast, HHAST\ClassishDeclaration::class);
  return new ScannedScope(
    $ast,
    $context['definitionContext'],
    /* classes = */ Vec\filter_nulls(Vec\map(
      $classish,
      $node ==> classish_from_ast($context, ScannedClass::class, $node),
    )),
    /* interfaces = */ Vec\filter_nulls(Vec\map(
      $classish,
      $node ==> classish_from_ast($context, ScannedInterface::class, $node),
    )),
    /* traits = */ Vec\filter_nulls(Vec\map(
      $classish,
      $node ==> classish_from_ast($context, ScannedTrait::class, $node),
    )),
    /* functions = */ Vec\map(
      _Private\items_of_type($ast, HHAST\FunctionDeclaration::class),
      $node ==> function_from_ast($context, $node),
    ),
    /* methods = */ Vec\map(
      _Private\items_of_type($ast, HHAST\MethodishDeclaration::class),
      $node ==> method_from_ast($context, $node),
    ),
    /* trait use statements = */ Vec\concat(
      Vec\map(
        _Private\items_of_type($ast, HHAST\TraitUse::class),
        $node ==> $node->getNames()->getItemsOfType(HHAST\EditableNode::class),
      ),
      Vec\map(
        _Private\items_of_type($ast, HHAST\TraitUseConflictResolution::class),
        $node ==> $node->getNames()->getItemsOfType(HHAST\EditableNode::class),
      ),
    )
    |> Vec\flatten($$)
    |> Vec\map($$, $node ==> typehint_from_ast($context, $node))
    |> Vec\filter_nulls($$),
    /* properties = */ Vec\map(
      _Private\items_of_type($ast, HHAST\PropertyDeclaration::class),
      $node ==> properties_from_ast($context, $node),
    )
    |> Vec\flatten($$),
    /* constants = */ Vec\concat(
      Vec\map(
        _Private\items_of_type($ast, HHAST\ConstDeclaration::class),
        $node ==> constants_from_ast($context, $node),
      )
      |> Vec\flatten($$),
      _Private\items_of_type($ast, HHAST\ExpressionStatement::class)
        |> Vec\map($$, $s ==> $s->getExpression())
        |> Vec\filter($$, $e ==> $e instanceof HHAST\DefineExpression)
        |> Vec\map(
          $$,
          $e ==> TypeAssert\instance_of(HHAST\DefineExpression::class, $e),
        )
        |> Vec\map($$, $e ==> constant_from_define_ast($context, $e))
        |> Vec\filter_nulls($$),
    ),
    /* type constants = */ Vec\map(
      _Private\items_of_type($ast, HHAST\TypeConstDeclaration::class),
      $node ==> type_constant_from_ast($context, $node),
    ),
    /* enums = */ Vec\map(
      _Private\items_of_type($ast, HHAST\EnumDeclaration::class),
      $node ==> enum_from_ast($context, $node),
    ),
    /* types = */ Vec\map(
      _Private\items_of_type($ast, HHAST\AliasDeclaration::class),
      $node ==> typeish_from_ast($context, ScannedType::class, $node),
    )
    |> Vec\filter_nulls($$),
    /* newtypes = */ Vec\map(
      _Private\items_of_type($ast, HHAST\AliasDeclaration::class),
      $node ==> typeish_from_ast($context, ScannedNewtype::class, $node),
    )
      |> Vec\filter_nulls($$),
  );
}
