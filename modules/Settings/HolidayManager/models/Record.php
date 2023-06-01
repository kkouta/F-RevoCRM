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
        $result = $adb->pquery("SELECT id, holidayname, holidaydate, holidaystatus FROM $table WHERE id = ?", array($id));
        if($adb->num_rows($result) > 0) {
            $record->set("id", $adb->query_result($result, 0, "id"));
            $record->set("holidayname" ,$adb->query_result($result, 0, "holidayname"));
            $record->set("holidaydate", $adb->query_result($result, 0, "holidaydate"));
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
        $fdsa = $this->get("holidaydate");
        $fdsfdfdf =  $this->get("holidaystatus");
        $adb->pquery("INSERT INTO $table(holidayname, date, holidaystatus) values (?, ?, ?)",
        array($this->get("holidayname"), $this->get("holidaydate"), $this->get("holidaystatus"),));

        $result = $adb->query("SELECT MAX(id) as currentid FROM $table");
        if($adb->num_rows($result)) {
            $this->set("id", $adb->query_result($result, 0, "currentid"));
        }
    }

    private function update() {
        global $adb;
        $table = Settings_HolidayManager_Module_Model::$TABLE_NAME;

        $adb->pquery("UPDATE $table SET holidayname = ?, date = ?, holidaystatus =? WHERE id = ?",
        array($this->get("holidayname"), $this->get("holidaydate"), $this->get("holidaystatus"),  $this->getId()));
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
    
    public function getRecordLinks() {//これもいらない
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

    public function checkholidaydbexist() {

        $db = PearDatabase::getInstance();

        $query = "SHOW TABLES LIKE 'vtiger_holiday';";
        $result = $db->pquery($query);
    
        if(!empty($db->fetchByAssoc($result))){

            return $this->pullholidays();
        }
        else{
            //holidayのテーブルがない場合にテーブルを作成する。
            $query = "CREATE TABLE vtiger_holiday(id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, holidayname VARCHAR(100) NOT NULL , holidaydate DATE,holidaystatus VARCHAR(100))";
            $db->pquery($query);
            $this->insertholiday();
            return;
        }
        return $result;
    }

    public function pullholidays(){
        $db = PearDatabase::getInstance();
        $query = "SELECT * FROM vtiger_holiday";
        $fsadfa = $db->pquery($query);
        while($record = $db->fetchByAssoc($db->pquery($query))){
            $item = array();
            $item['id'] = $record['id'];
            $item['holidaystatus'] = $record['holidaystatus'];
            $item['holidaystart'] = $record['holidaydate'];
            $item['holidaytitle'] = $record['holidayname'];
            $result[] = $item;

        }

        return $result;

    }

    public function createholidaydb(){
            $query = "CREATE TABLE vtiger_holiday(id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, holidayname VARCHAR(100) NOT NULL)";

            $holiday = $this->getholidayfromapi();

            

        }

    public function getholidayfromapi(){
        $apiurl = 'https://holidays-jp.github.io/api/v1/2023/date.json';
        $jsonholiday = mb_convert_encoding(file_get_contents($apiurl), 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        
        // file_put_contents("sampleholidayapi.txt",json_encode($arrayaaa["2023-01-01"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),FILE_APPEND );
        return $jsonholiday;//うまく連想配列に入るように修正する。
    }
    
    public function insertholiday(){ 
        $db = PearDatabase::getInstance();
        $jsonholiday =  $this->getholidayfromapi();
       

        foreach($jsonholiday as $key => $value){
            $key = DateTimeField::convertToDBTimeZone($key);
            $key = $key->format('Y-m-d H:i:s');
            $query = "INSERT INTO vtiger_holiday (holidaydate, holidayname) VALUES(?,?)";
            $db->pquery($query,array($key,$value));
        }
        return;

    }

}