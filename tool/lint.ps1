#!/usr/bin/env pwsh
Set-StrictMode -Version Latest
Set-Location (Split-Path $PSScriptRoot)

php -l bin/coveralls
php -l example/main.php
composer run-script lint
