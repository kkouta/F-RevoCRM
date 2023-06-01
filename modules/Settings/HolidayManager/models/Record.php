<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  F-RevoCRM Open Source
 * The Initial Developer of the Original Code is F-RevoCRM.
 * Portions created by thinkingreed are Copyright (C) F-RevoCRM.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_HolidayManager_Record_Model extends Settings_LanguageConverter_Record_Model {
    public static function getInstanceById($id) {
        global $adb;
        
        $record = new self();
        if(empty($id)) {
            return $record;
        }

        $table = Settings_HolidayManager_Module_Model::$TABLE_NAME;
        $result = $adb->pquery("SELECT id, holidayname, date, holidaystatus FROM $table WHERE id = ?", array($id));
        if($adb->num_rows($result) > 0) {
            $record->set("id", $adb->query_result($result, 0, "id"));
            $record->set("holidayname" ,$adb->query_result($result, 0, "holidayname"));
            $record->set("date", $adb->query_result($result, 0, "date"));
            $record->set("holidaystatus", $adb->query_result($result, 0, "holidaystatus"));
            $record->id = $record->get("id");
        }

        return $record;
    }
    public function save() {
        if(empty($this->id)) {
            $this->insert();
        } else {
            $this->update();
        }
        return $this->getId();
    }

    private function insert() {
        global $adb;
        $table = Settings_HolidayManager_Module_Model::$TABLE_NAME;
        $fdsafas = $this->get("holidayname");
        $fdsa = $this->get("date");
        $fdsfdfdf =  $this->get("holidaystatus");
        $adb->pquery("INSERT INTO $table(holidayname, date, holidaystatus) values (?, ?, ?)",
        array($this->get("holidayname"), $this->get("date"), $this->get("holidaystatus"),));

        $result = $adb->query("SELECT MAX(id) as currentid FROM $table");
        if($adb->num_rows($result)) {
            $this->set("id", $adb->query_result($result, 0, "currentid"));
        }
    }

    private function update() {
        global $adb;
        $table = Settings_HolidayManager_Module_Model::$TABLE_NAME;

        $adb->pquery("UPDATE $table SET holidayname = ?, date = ?, holidaystatus =? WHERE id = ?",
        array($this->get("holidayname"), $this->get("date"), $this->get("holidaystatus"),  $this->getId()));
    }

    public function delete() {
        global $adb;
        $table = Settings_HolidayManager_Module_Model::$TABLE_NAME;

        $id = $this->getId();
        if(empty($id)) {
            throw new Exception("Invalid Request. Cannot delete rule.");
        }

        $adb->pquery("DELETE FROM $table WHERE id = ?", array($this->id));
    }

    public function getEditViewUrl() {
        return 'module=HolidayManager&parent=Settings&view=EditAjax&record='.$this->getId();
    }
    
    public function getRecordLinks() {
        $editLink = array(
            'linkurl' => "javascript:Settings_HolidayManager_Js.triggerEdit(event, '".$this->getId()."')",
            'linklabel' => 'LBL_EDIT',
            'linkicon' => 'icon-pencil'
        );
        $editLinkInstance = Vtiger_Link_Model::getInstanceFromValues($editLink);
        
        $deleteLink = array(
            'linkurl' => "javascript:Settings_HolidayManager_Js.triggerDelete(event,'".$this->getId()."')",
            'linklabel' => 'LBL_DELETE',
            'linkicon' => 'icon-trash'
        );
        $deleteLinkInstance = Vtiger_Link_Model::getInstanceFromValues($deleteLink);
        return array($editLinkInstance,$deleteLinkInstance);
    }
    public function checkholidayfromapi(){
        $thisyear = date('Y');
        
        global $adb;
        if(!Vtiger_Utils::CheckTable('vtiger_holiday_api')){
            return;
        }
        $result = $adb->pquery("SELECT  exist FROM vtiger_holiday_api WHERE year = ?", array($thisyear));
        
        if($result == 1){
            return true;
        }else{
            file_put_contents("sampleresultexist.txt",json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),FILE_APPEND );
            $this->getholidayfromapi($thisyear);
        }


        

        
    }
    public function getholidayfromapi($year){
        $db = PearDatabase::getInstance();
        $apiurl = 'https://holidays-jp.github.io/api/v1/'. $year .'/date.json';
        file_put_contents("sampleurl.txt",json_encode($apiurl, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),FILE_APPEND );

        $jsonholiday = mb_convert_encoding(file_get_contents($apiurl), 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        foreach($jsonholiday as $key => $value){
            $key = DateTimeField::convertToDBTimeZone($key);
            $key = $key->format('Y-m-d H:i:s');
            $query = "INSERT INTO vtiger_holiday (date, holidayname) VALUES(?,?)";
            $db->pquery($query,array($key,$value));
        }
        $db->pquery("INSERT INTO vtiger_holiday_api (exist, year) VALUES(?,?)",array(1,$year));
    }
    public function createholidayapitable(){
        $db = PearDatabase::getInstance();
        $db->query("CREATE TABLE vtiger_holiday_api (
            id int(19) NOT NULL AUTO_INCREMENT,
            exist int(10) NOT NULL,
            year int(100) NOT NULL,
            PRIMARY KEY (id)
            ) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");


    }
}