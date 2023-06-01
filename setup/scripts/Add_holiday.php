<?php
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
include_once('modules/PickList/DependentPickListUtils.php');
include_once('modules/ModTracker/ModTracker.php');
include_once('include/utils/CommonUtils.php');

global $adb;

$module_name = "HolidayManager";
$table_name = 'vtiger_holiday';
$query = "SELECT * FROM vtiger_settings_field WHERE name = ?";
$result = $adb->pquery($query, array($module_name));
$main_id =  'id';

$module = new Vtiger_Module();
$module->name = $module_name;
$module->save();
$module->initTables($table_name, $main_id);
$tabid = $module->id;

/* 基本情報 */
$blockInstance = new Vtiger_Block();
$blockInstance->label = 'LBL_HOLIDAY_INFORMATION';
$module->addBlock($blockInstance);

// 件名
$field = new Vtiger_Field();
$field->name = 'holiday';
$field->table = $module->basetable;
$field->column = $field->name;
$field->columntype = 'varchar(100)';
$field->uitype = 2;
$field->typeofdata = 'V~M';
$field->masseditable = 1;
$field->quickcreate = 1;
$field->summaryfield = 1;
$field->label = '祝日';
$blockInstance->addField($field);

/*
* モジュール内でキーとなるカラム1つに対して実行
* 複数回は実施しないこと
*/
$module->setEntityIdentifier($field);

//Add link to the module in the Setting Panel
$fieldid = $adb->getUniqueID('vtiger_settings_field');
$blockid = getSettingsBlockId('LBL_MODULE_MANAGER');

if($adb->num_rows($result) == 0){
    $seq_res = $adb->query("SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid=$blockid");
    $seq = 1;
    if ($adb->num_rows($seq_res) > 0)
    {
        $cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
        
        if ($cur_seq != null)
        {
            $seq = $cur_seq + 1;
        }
    }
        
    $adb->pquery
    (
        'INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence,active) VALUES (?,?,?,?,?,?,?,?)',
        array
        (
            $fieldid,
            $blockid,
            $module_name,
            null,
            'LBL_'.strtoupper($module_name).'_DESCRIPTION',
            'index.php?module='.$module_name.'&view=List&parent=Settings',
            $seq,
            0
        )
    );

}
