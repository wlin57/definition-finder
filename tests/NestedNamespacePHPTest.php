<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use function Facebook\FBExpect\expect;

class NestedNamespacePHPTest extends AbstractPHPTest {
  <<__Override>>
  protected function getFilename(): string {
    return 'nested_namespace_php.php';
  }

  <<__Override>>
  protected function getPrefix(): string {
    return 'Namespaces\\AreNestedNow\\';
  }
}
