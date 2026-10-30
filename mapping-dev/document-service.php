<?php
// Local synthetic document store only. No imported map-table reads or writes.
class MappingDocuments {
    private $db;
    public function __construct($db) {
        $this->db=$db;
        $db->query('CREATE TABLE IF NOT EXISTS local_document_maps (id CHAR(32) PRIMARY KEY, owner_id INT UNSIGNED NOT NULL, revision INT UNSIGNED NOT NULL, payload LONGTEXT NOT NULL, create_hash CHAR(64) NOT NULL, FOREIGN KEY(owner_id) REFERENCES local_membership_users(id) ON DELETE CASCADE) ENGINE=InnoDB');
    }
    public static function inspect($doc) {
        $d=json_decode(json_encode($doc),true);$errors=[];$ro=[];
        if(!is_array($d))return [['Document object required'],[]];
        if(($d['schemaVersion']??null)!==1)$ro[]='Unsupported document version';
        if(!preg_match('/^[a-f0-9]{32}$/D',$d['id']??'')||!is_string($d['title']??null)||!strlen(trim($d['title']))||strlen($d['title'])>1000)$errors[]='Invalid identity or title';
        foreach(['locations','groups','geometry'] as $k)if(!is_array($d[$k]??null))$errors[]='Document arrays required';
        if($errors)return [$errors,$ro];
        if(count($d['locations'])>500)$errors[]='Local document limit: 500 locations';
        $number=function($v){return (is_int($v)||is_float($v))&&is_finite((float)$v);};
        $coordinate=function($p)use($number){return is_array($p)&&isset($p['lat'],$p['lng'])&&$number($p['lat'])&&$number($p['lng'])&&abs($p['lat'])<=90&&abs($p['lng'])<=180;};
        $content=function($c)use(&$ro){
            if(!is_array($c)||!in_array($c['format']??null,['text','html'],true)||!is_string($c['value']??null)){$ro[]='Unsupported description representation';return;}
            if($c['format']==='html'&&(preg_match('/<\s*\/?\s*(?!h[1-3]\b|p\b|br\b|strong\b|b\b|em\b|i\b|ul\b|ol\b|li\b|a\b|img\b)[a-z][^>]*>/i',$c['value'])||preg_match('/<[^>]*\s(?:on\w+|style|srcset|formaction)\s*=|(?:javascript|data|vbscript)\s*:|(?:href|src)\s*=\s*["\']?(?:https?:|\/\/)|<img\b(?![^>]*\bsrc\s*=\s*["\']\/mapping\/assets\/[^"\']+["\'])/i',$c['value'])))$ro[]='Unsupported markup or external media retained';
        };
        $content($d['description']??null);$groups=[];$ids=[];
        foreach($d['groups'] as $g){if(!is_array($g)||!is_string($g['id']??null)){$errors[]='Invalid group';continue;}if(isset($groups[$g['id']])||!is_string($g['name']??null)||!is_bool($g['visible']??null))$errors[]='Invalid group';$groups[$g['id']]=true;}
        foreach($d['locations'] as $m){
            if(!is_array($m)||!is_string($m['id']??null)||!is_string($m['groupId']??null)){$errors[]='Invalid location';continue;}
            if(!is_string($m['id']??null)||!strlen($m['id'])||isset($ids[$m['id']])||!is_string($m['name']??null)||!(array_key_exists('originalAddress',$m)&&($m['originalAddress']===null||is_string($m['originalAddress'])))||!$coordinate($m['coordinates']??null)||!isset($groups[$m['groupId']??'']))$errors[]='Invalid location';$ids[$m['id']??'']=true;$content($m['description']??null);$a=$m['appearance']??[];
            if(!in_array($a['kind']??null,['pin','number','image'],true)||!in_array($a['size']??null,['normal','small'],true)||(!in_array($a['color']??null,['RED','GREEN','BLUE','ORANGE','YELLOW','GREY','LIME','CYAN','VIOLET','PURPLE','BLACK','WHITE','PINK'],true)&&!preg_match('/^#[a-f0-9]{6}$/i',$a['color']??'')))$ro[]='Unsupported marker appearance';
            if(($a['kind']??'')==='image'&&!preg_match('#^/mapping/assets/[a-z0-9-]+\.svg$#i',$a['icon']??''))$ro[]='Unsupported custom asset retained';
        }
        $s=$d['settings']??[];
        if(!$coordinate($s['center']??null)||!is_int($s['zoom']??null)||!is_int($s['minZoom']??null)||!is_int($s['maxZoom']??null)||$s['minZoom']<0||$s['maxZoom']>22||$s['minZoom']>$s['zoom']||$s['zoom']>$s['maxZoom'])$errors[]='Invalid camera settings';
        if(array_key_exists('shareWithLink',$s)&&!is_bool($s['shareWithLink']))$errors[]='Invalid sharing setting';
        foreach(['zoomControl','mapTypeControl','legendVisible'] as $k)if(!is_bool($s[$k]??null))$errors[]='Invalid control settings';
        if(($s['mapType']??null)!=='roadmap')$ro[]='Unsupported map type retained';
        if(array_key_exists('plotDefaults',$s)){
            $p=$s['plotDefaults'];$a=$p['appearance']??[];
            if(!is_array($p)||!is_string($p['groupId']??null)||!isset($groups[$p['groupId']]))$errors[]='Invalid new-marker default group';
            if(!in_array($a['kind']??null,['pin','number','image'],true)||!in_array($a['size']??null,['normal','small'],true)||(!in_array($a['color']??null,['RED','GREEN','BLUE','ORANGE','YELLOW','GREY','LIME','CYAN','VIOLET','PURPLE','BLACK','WHITE','PINK'],true)&&!preg_match('/^#[a-f0-9]{6}$/i',$a['color']??''))||(($a['kind']??'')==='image'&&!preg_match('#^/mapping/assets/[a-z0-9-]+\.svg$#i',$a['icon']??'')))$ro[]='Unsupported new-marker default appearance retained';
        }
        foreach($d['geometry'] as $g){
            if(!in_array($g['type']??null,['LineString','Polygon','Circle'],true)){$ro[]='Unsupported geometry retained';continue;}
            if($g['type']==='Circle'){if(!$coordinate($g['center']??null)||!$number($g['radiusMeters']??null)||$g['radiusMeters']<=0)$errors[]='Invalid circle';}
            else {$rings=$g['type']==='Polygon'?($g['coordinates']??[]):[$g['coordinates']??[]];if(!is_array($rings)||!count($rings))$errors[]='Invalid geometry';else foreach($rings as $ring){if(!is_array($ring)||count($ring)<2){$errors[]='Invalid geometry';continue;}foreach($ring as $p)if(!is_array($p)||count($p)!==2||!$number($p[0])||!$number($p[1])||abs($p[0])>180||abs($p[1])>90)$errors[]='Invalid geometry coordinate';}}
        }
        if(!empty($d['compatibility']['unsupported']))$ro[]='Unsupported legacy content retained';
        return [array_values(array_unique($errors)),array_values(array_unique($ro))];
    }
    private function read($id,$uid){$s=$this->db->prepare('SELECT revision,payload,create_hash FROM local_document_maps WHERE id=? AND owner_id=?');$s->bind_param('si',$id,$uid);$s->execute();$r=$s->get_result()->fetch_assoc();$s->close();return $r;}
    private function result($row,$canEdit=true){$doc=json_decode($row['payload']);list($e,$ro)=self::inspect($doc);return ['document'=>$doc,'revision'=>(int)$row['revision'],'readonly'=>$ro,'canEdit'=>$canEdit];}
    private static function extras($old,$new){
        if(is_object($old)){
            if(!is_object($new))return false;
            $known=explode(' ','schemaVersion id title description locations groups geometry settings plotDefaults extensions compatibility name originalAddress coordinates appearance groupId format value kind color size label icon visible center lat lng zoom minZoom maxZoom mapType zoomControl mapTypeControl legendVisible shareWithLink type radiusMeters');
            foreach(get_object_vars($old) as $k=>$v){if($k==='extensions'||$k==='compatibility'||!in_array($k,$known,true)){if(!property_exists($new,$k)||json_encode($new->$k)!==json_encode($v))return false;}elseif(property_exists($new,$k)&&!self::extras($v,$new->$k))return false;}
        }elseif(is_array($old)&&is_array($new)){foreach($old as $i=>$v){if(is_object($v)&&isset($v->id)){foreach($new as $candidate)if(is_object($candidate)&&($candidate->id??null)===$v->id&&!self::extras($v,$candidate))return false;}elseif(isset($new[$i])&&!self::extras($v,$new[$i]))return false;}}
        return true;
    }
    public function dispatch($path,$member){
        LocalMembership::session();$user=$member->user();$method=$_SERVER['REQUEST_METHOD'];
        if($method==='GET'&&preg_match('#^/__local_documents/maps/([a-f0-9]{32})$#D',$path,$match)){
            $stmt=$this->db->prepare('SELECT owner_id,revision,payload,create_hash FROM local_document_maps WHERE id=?');$stmt->bind_param('s',$match[1]);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();
            $owner=$row&&$user&&(int)$row['owner_id']===(int)$user['id'];$doc=$row?json_decode($row['payload']):null;
            if(!$row||(!$owner&&($doc->settings->shareWithLink??false)!==true))LocalMembership::respond(404,['error'=>'Map not found or not shared']);
            LocalMembership::respond(200,$this->result($row,(bool)$owner));
        }
        if(!$user)LocalMembership::respond(401,['error'=>'Local synthetic sign-in required']);$uid=(int)$user['id'];
        if($method!=='GET'){
            if((isset($_SERVER['HTTP_ORIGIN'])&&$_SERVER['HTTP_ORIGIN']!=='http://'.($_SERVER['HTTP_HOST']??''))||!hash_equals($_SESSION['local_csrf'],$_SERVER['HTTP_X_LOCAL_CSRF']??''))LocalMembership::respond(403,['error'=>'Request verification failed']);
        }
        if($path==='/__local_documents/maps'&&$method==='GET'){
            $s=$this->db->prepare('SELECT id,revision,payload FROM local_document_maps WHERE owner_id=? ORDER BY id');$s->bind_param('i',$uid);$s->execute();$r=$s->get_result();$maps=[];while($row=$r->fetch_assoc())$maps[]=['id'=>$row['id'],'revision'=>(int)$row['revision'],'title'=>json_decode($row['payload'])->title];LocalMembership::respond(200,['maps'=>$maps]);
        }
        $id=null;if(preg_match('#^/__local_documents/maps/([a-f0-9]{32})$#D',$path,$m))$id=$m[1];
        $row=$id?$this->read($id,$uid):null;if($id&&!$row)LocalMembership::respond(404,['error'=>'Map not found']);
        if($id&&$method==='GET')LocalMembership::respond(200,$this->result($row));
        $raw=file_get_contents('php://input');if(strlen($raw)>1048576)LocalMembership::respond(413,['error'=>'Document exceeds local size limit']);$body=json_decode($raw);
        if(!is_object($body))LocalMembership::respond(400,['error'=>'JSON object required']);
        if($id&&$method==='DELETE'){$s=$this->db->prepare('DELETE FROM local_document_maps WHERE id=? AND owner_id=? AND revision=?');$rev=$body->revision??-1;$s->bind_param('sii',$id,$uid,$rev);$s->execute();if(!$s->affected_rows)LocalMembership::respond(409,['error'=>'Revision conflict; draft retained']);LocalMembership::respond(200,['deleted'=>true]);}
        if(($path==='/__local_documents/maps'&&$method==='POST')||($id&&$method==='PUT')){
            $doc=$body->document??null;list($errors,$ro)=self::inspect($doc);if($errors)LocalMembership::respond(422,['error'=>implode('; ',$errors)]);
            $payload=json_encode($doc,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);$hash=hash('sha256',$payload);
            if($method==='POST'){
                $id=$doc->id;$existing=$this->read($id,$uid);if($existing){if($existing['create_hash']!==$hash)LocalMembership::respond(409,['error'=>'Creation retry differs from saved map; draft retained']);LocalMembership::respond(200,$this->result($existing));}
                $s=$this->db->prepare('INSERT INTO local_document_maps (id,owner_id,revision,payload,create_hash) VALUES (?,?,1,?,?)');$s->bind_param('siss',$id,$uid,$payload,$hash);try{$ok=$s->execute();}catch(Throwable $e){$ok=false;}if(!$ok)LocalMembership::respond(409,['error'=>'Map identity conflict; draft retained']);$row=$this->read($id,$uid);LocalMembership::respond(201,$this->result($row));
            }
            list($oldErrors,$oldReadonly)=self::inspect(json_decode($row['payload']));
            if($ro||$oldReadonly)LocalMembership::respond(422,['error'=>'Unsupported content is read-only; original retained']);
            if($doc->id!==$id||!self::extras(json_decode($row['payload']),$doc))LocalMembership::respond(422,['error'=>'Original identity or unknown content must be preserved']);
            $rev=$body->revision??-1;if(!is_int($rev))LocalMembership::respond(400,['error'=>'Expected revision required']);
            $s=$this->db->prepare('UPDATE local_document_maps SET payload=?,revision=revision+1 WHERE id=? AND owner_id=? AND revision=?');$s->bind_param('ssii',$payload,$id,$uid,$rev);$s->execute();if(!$s->affected_rows)LocalMembership::respond(409,['error'=>'Revision conflict; your draft is retained. Reopen separately to compare.']);LocalMembership::respond(200,$this->result($this->read($id,$uid)));
        }
        LocalMembership::respond(404,['error'=>'Local document endpoint not found']);
    }
}
