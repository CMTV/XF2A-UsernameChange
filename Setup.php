<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

use CMTV\UsernameChange\Constants as C;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	// ************** INSTALL STEPS **************

	public function installStep1()
    {
        $this->schemaManager()->createTable(C::dbTable('username_change'), function (Create $table)
        {
            $table->addColumn('change_id', 'int')->autoIncrement()->primaryKey();
            $table->addColumn('user_id', 'int');
            $table->addColumn('old_username', 'varchar', 50);
            $table->addColumn('change_date', 'int')->setDefault(0);
            $table->addColumn('from_acp', 'tinyint', 3)->setDefault(0);
        });
    }

    public function installStep2()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table)
        {
            $table->addColumn(C::dbColumn('username_changes'), 'int')->setDefault(0);
            $table->addColumn(C::dbColumn('username_change_date'), 'int')->setDefault(0);
        });
    }

    public function installStep3()
    {
        $this->schemaManager()->alterTable('xf_user_privacy', function (Alter $table)
        {
            $table->addColumn(C::dbColumn('allow_view_username_changes'), 'enum')->values([
                'everyone',
                'members',
                'followed',
                'none'
            ])->setDefault('everyone');
        });
    }

    public function installStep4()
    {
        $registeredPermissions = [
            'changeUsername',
            'viewHistory'
        ];

        $moderatorPermissions = [];

        foreach ($registeredPermissions as $permission)
        {
            $this->applyGlobalPermission(
                C::ADDON_ID_SHORT,
                $permission,
                'forum',
                'editOwnPost'
            );
        }

        foreach ($moderatorPermissions as $permission)
        {
            $this->applyGlobalPermission(
                C::ADDON_ID_SHORT,
                $permission,
                'general',
                'editBasicProfile'
            );
        }

        $this->applyGlobalPermissionInt(
            C::ADDON_ID_SHORT,
            'changeFrequency',
            30,
            'forum',
            'editOwnPost'
        );
    }

    // ************** UNINSTALL STEPS **************

    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable(C::dbTable('username_change'));
    }

    public function uninstallStep2()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table)
        {
            $table->dropColumns([
                C::dbColumn('username_changes'),
                C::dbColumn('username_change_date')
            ]);
        });
    }

    public function uninstallStep3()
    {
        $this->schemaManager()->alterTable('xf_user_privacy', function (Alter $table)
        {
            $table->dropColumns(C::dbColumn('allow_view_username_changes'));
        });
    }

    // ************** UPDATE STEPS **************

    public function upgrade(array $stepParams = [])
    {
        // Upgrading from 1.0.0
        if ($this->addOn->version_id < 2010070)
        {
            // Adding 'from_acp' column to 'xf_cmtv_uc_username_change'
            $this->schemaManager()->alterTable('xf_cmtv_uc_username_change', function (Alter $table)
            {
                $table->addColumn('from_acp', 'tinyint', 3)->setDefault(0);
            });

            // Lower-casing custom columns in `xf_user`
            $this->schemaManager()->alterTable('xf_user', function (Alter $table)
            {
                $table->renameColumn('CMTV_UC_username_changes', 'cmtv_uc_username_changes');
                $table->renameColumn('CMTV_UC_username_change_date', 'cmtv_uc_username_change_date');
            });

            // Lower-casing custom column in `xf_user_privacy`
            $this->schemaManager()->alterTable('xf_user_privacy', function (Alter $table)
            {
                $table->renameColumn('CMTV_UC_allow_view_username_changes', 'cmtv_uc_allow_view_username_changes');
            });
        }
    }
}