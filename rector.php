<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

try {
    return RectorConfig::configure()
                       ->withSkip([
                           AddOverrideAttributeToOverriddenMethodsRector::class,
                       ])
                       ->withPaths([
                           __DIR__ . '/src',
                       ])
                       ->withPreparedSets(
                           deadCode        : true,
                           codeQuality     : true,
                           typeDeclarations: true,
                           privatization   : true,
                           earlyReturn     : true,
                           strictBooleans  : true,
                       )
                       ->withPhpSets();
} catch (InvalidConfigurationException $e) {
    // ..
}
