<?php

namespace Corals\Modules\Utility\Comment\Providers;

use Corals\Modules\Utility\Comment\database\migrations\CreateCommentsTable;
use Corals\Modules\Utility\Comment\database\seeds\UtilityCommentDatabaseSeeder;
use Corals\Foundation\Providers\BaseUninstallModuleServiceProvider;

class UninstallModuleServiceProvider extends BaseUninstallModuleServiceProvider
{
    protected $migrations = [
        CreateCommentsTable::class,
    ];

    protected function providerBooted()
    {
        $this->dropSchema();

        $utilityCommentDatabaseSeeder = new UtilityCommentDatabaseSeeder();

        $utilityCommentDatabaseSeeder->rollback();
    }
}
