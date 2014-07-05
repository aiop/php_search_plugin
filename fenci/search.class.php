<?php
/*
*搜索返回MYSQL语句类
*/
    class search{
        public $table;
        public $akey;
        public $wd;
        public $wdsplit;
        public $result;
        public $offset;
        public $pagesize;
        public $wdarray;
 
        function __construct($table='',$akey='',$pagesize=9){
            $this->table   = $table;
            $this->akey    = $akey;
            $this->pagesize= $pagesize;
        }
 
        function q($wd='',$wdsplit='',$offset=0){
            $this->wd      = $wd;
            $this->wdsplit = $wdsplit;
            $this->offset  = $offset;
            $this->wdsplit = str_replace('/', '', $this->wdsplit);
            $this->wdarray = explode(" ",trim($this->wdsplit));

            $ordersql=" order by (";

            foreach($this->wdarray as $value)
            {
                $strlen=strlen($value);
                if($strlen>2){$points=$strlen;}else{$points=1;}
                $ordersql.="(case when ".$this->akey." like '%".$value."%' then ".$points." else 0 end)+";
            }
            $strlen=strlen($this->wd);
            #$strlen=8;
            $ordersql.="(case when ".$this->akey." like '%".$this->wd."%' then ".$strlen." else 0 end)";
            $ordersql.=") desc,ltime desc";
            $keywordsql=$this->GetKeywordSql("title",$this->wdarray);
            if(empty($keywordsql))
            {
                $keywordsql="title ".$this->akey." '%".$this->wd."%'";
            }
            $sql="select * from ".$this->table." where ".$this->akey."<>'' and ".$keywordsql.$ordersql;
            $sql=$sql." limit {$this->offset},{$this->pagesize}";
            return $sql;
        }
        function GetKeywordSql(){
            $kwsql = '';
            $kwsqls = array();
            foreach($this->wdarray as $k)
            {
                $k = trim($k);
                if(strlen($k)<2)
                {
                    continue;
                }
                if(ord($k[0])>0x80 && strlen($k)<3)
                {
                    continue;
                }
                $k = addslashes($k);
                $kwsqls[] = " CONCAT(".$this->akey.") like '%$k%' ";
            }
            if(!isset($kwsqls[0]))
            {
                return '';
            }
            else
            {
                $kwsql = join(' OR ',$kwsqls);
                return $kwsql;
            } 
        }

        function GetRedKeyWord($result=''){
            $this->result=$result;
            $ks=$this->wdarray;
            if(empty($ks)){return $result;}
            foreach($ks as $k)
            {
                $k = trim($k);
                if($k=='')
                {
                    continue;
                }
                if(ord($k[0])>0x80 && strlen($k)<0)
                {
                    continue;
                }
                $results = str_replace($k,"<b>$k</b>",$result);
            }
            return $results;
        } 
        function __destruct(){
        }
 
    }
?>