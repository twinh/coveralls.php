#!/usr/bin/env pwsh
Set-StrictMode -Version Latest
Set-Location (Split-Path $PSScriptRoot)

php -l bin/coveralls
php -l example/main.php
vendor/bin/phpstan analyse --configuration=etc/phpstan.neon
