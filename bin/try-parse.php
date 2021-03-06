#!/usr/bin/env hhvm
<?hh
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 * *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\DefinitionFinder;

require_once (__DIR__.'/../vendor/hh_autoload.php');

use namespace HH\Lib\{Str, Vec};

async function try_parse_async(string $path): Awaitable<void> {
  $line = Str\format('%s ... ', $path);
  try {
    await FileParser::fromFileAsync($path);
  } catch (\Exception $e) {
    if (!Str\ends_with($path, '.hhi')) {
      $ret_code = -1;
      \system(
        \sprintf(
          '%s -l %s >/dev/null',
          \escapeshellarg(\PHP_BINARY),
          \escapeshellarg($path),
        ),
        &$ret_code,
      );
      if ($ret_code !== 0) {
        print $line."HHVM SYNTAX ERROR\n";
        return;
      }
    }
    $json = \exec(
      'hh_parse --full-fidelity-json '.\escapeshellarg($path).' 2>/dev/null'
    );
    $json = Str\trim($json);
    if (\json_decode($json) === null && \json_last_error() === \JSON_ERROR_DEPTH) {
      print $line."JSON TOO DEEP";
      return;
    }
    print $line;
    throw $e;
  }
  print $line."OK\n";
}

$files = array_slice($argv, 1);

\HH\Asio\join(
  Vec\map_async($files, async $file ==> await try_parse_async($file)),
);
