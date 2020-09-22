<?PHP
class Cms{

public function loadcms($id,$lang){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS("
        SELECT cl.content FROM "._DB_PREFIX_."cms_lang AS cl
        WHERE cl.id_lang='$lang' AND cl.id_cms='$id'        
        ");
        return $db;
}
   
}
?>