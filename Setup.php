<?php

namespace CMTV\UsernameChange;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	//
    // INSTALLING
    //

	public function installStep1()
    {
        $this->schemaManager()->createTable('xf_cmtv_uc_username_change', function (Create $table)
        {
            $table->addColumn('change_id', 'int')->autoIncrement()->primaryKey();
            $table->addColumn('user_id', 'int');
            $table->addColumn('old_username', 'varchar', 50);
            $table->addColumn('change_date', 'int')->setDefault(0);
        });
    }

    public function installStep2()
    {
        $this->schemaManager()->alterTable('xf_user_privacy', function (Alter $table)
        {
            $table->addColumn('CMTV_UC_allow_view_username_changes', 'enum')->values(['everyone','members','followed','none'])->setDefault('everyone');
        });
    }

    public function installStep3()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table)
        {
            $table->addColumn('CMTV_UC_username_changes', 'int')->setDefault(0);
            $table->addColumn('CMTV_UC_username_change_date', 'int')->setDefault(0);
        });
    }

    // Setting up default permissions
    public function installStep4()
    {
        $registeredPermissions = [
            'changeUsername',
            'viewUsernameChanges',
            'viewUsernameChangeDates'
        ];

        $moderatorPermissions = [
            'deleteAnyHistory'
        ];

        foreach ($registeredPermissions as $permission)
        {
            $this->applyGlobalPermission('CMTV_UC', $permission, 'forum', 'editOwnPost');
        }

        foreach ($moderatorPermissions as $permission)
        {
            $this->applyGlobalPermission('CMTV_UC', $permission, 'general', 'editBasicProfile');
        }

        $this->applyGlobalPermissionInt('CMTV_UC', 'changeFrequency', 30, 'forum', 'editOwnPost');
    }

    // Adding a default widget to "What's new" page
    public function installStep5()
    {
        $this->db()->insert('xf_widget', [
            'widget_key' => 'whats_new_new_username_changes',
            'definition_id' => 'CMTV_UC_username_changes',
            'options' => '{"limit":5,"style":"full"}',
            'positions' => '{"whats_new_overview":"50"}'
        ]);
    }

    //
    // UNINSTALLING
    //

    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable('xf_cmtv_uc_username_change');
    }

    public function uninstallStep2()
    {
        $this->schemaManager()->alterTable('xf_user_privacy', function (Alter $table)
        {
            $table->dropColumns('CMTV_UC_allow_view_username_changes');
        });
    }

    public function uninstallStep3()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table)
        {
            $table->dropColumns([
                'CMTV_UC_username_changes',
                'CMTV_UC_username_change_date'
            ]);
        });
    }

    // Removing redundant "Latest activity" user feed items
    public function uninstallStep4()
    {
        $this->db()->delete('xf_news_feed', '`content_type` = ?', 'username_change');
    }
}