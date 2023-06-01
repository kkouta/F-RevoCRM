<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  F-RevoCRM Open Source
 * The Initial Developer of the Original Code is F-RevoCRM.
 * Portions created by thinkingreed are Copyright (C) F-RevoCRM.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_HolidayManager_Module_Model extends Settings_LanguageConverter_Module_Model{
    var $baseTable = 'vtiger_holiday';
	var $baseIndex = 'id';
	var $listFields = array('id' => 'ID', 'holidayname' => 'HolidayName', 'date' => 'HolidayDate', 'holidaystatus' => 'HolidayStatus',);
	var $nameFields = array('');
	var $name = 'HolidayManager';

    public static $TABLE_NAME = "vtiger_holiday";
    public function getEditableFieldsList() {
        return array('holidayname', 'date', 'holidaystatus');
    }

    public static function pullEvent($start,$end){
        
        global $adb;
        $table = self::$TABLE_NAME;

        $result = $adb->query("SELECT id,holidayname,date,holidaystatus FROM $table WHERE date>='$start' AND date<='$end'");
        for($i=0; $i<$adb->num_rows($result); $i++) {
            $id = $adb->query_result($result, $i, 'id');
            $holidayname = $adb->query_result($result, $i, 'holidayname');
            $holidaydate = $adb->query_result($result, $i, 'date');
            $holidaystatus = $adb->query_result($result, $i, 'holidaystatus');

            $item[] = array(
                'id' => $id,
                'title' => $holidayname,
                'start' => $holidaydate,
                'status' => $holidaystatus,
            );
        }
        return $item;


    }

    public static function loadAll() {
        global $adb;
        $table = self::$TABLE_NAME;

        // Setup Wizard
        if(!file_exists('config.inc.php')) {
            return;
        }


        $result = $adb->query("SELECT id, holidayname, date, holidaystatus FROM $table");

        for($i=0; $i<$adb->num_rows($result); $i++) {
            $id = $adb->query_result($result, $i, 'id');
            $holidayname = $adb->query_result($result, $i, 'holidayname');
            $holidaydate = $adb->query_result($result, $i, 'date');
            $holidaystatus = $adb->query_result($result, $i, 'holidaystatus');

            $item[] = array(
                'id' => $id,
                'title' => $holidayname,
                'start' => $holidaydate,
                'status' => $holidaystatus,
            );
        }
        return $item;

    }
    public function getCreateRecordUrl() {
        return "javascript:Settings_HolidayManager_Js.triggerAdd(event)";
    }

}


