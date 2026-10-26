<?php
// Local development membership only. Never authenticates imported users or WordPress.
class LocalMembership {
    private $db;
    public function __construct() {
        $this->db = new mysqli('db', 'local_test', 'local-only-test', '[REDACTED_ACCOUNT]');
        $this->db->set_charset('utf8mb4');
        $this->db->query('CREATE TABLE IF NOT EXISTS local_membership_users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(64) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
        $this->db->query('CREATE TABLE IF NOT EXISTS local_membership_maps (id CHAR(32) PRIMARY KEY, owner_id INT UNSIGNED NOT NULL, payload LONGTEXT NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (owner_id) REFERENCES local_membership_users(id) ON DELETE CASCADE, INDEX(owner_id)) ENGINE=InnoDB');
    }
    public static function session() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_strict_mode', '1');
            session_name('MP_LOCAL_MEMBERSHIP');
            session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Strict']);
            session_start();
        }
        if (!isset($_SESSION['local_csrf'])) $_SESSION['local_csrf'] = bin2hex(random_bytes(32));
    }
    public function user() {
        $id = (int)($_SESSION['local_user_id'] ?? 0);
        if (!$id) return null;
        $stmt=$this->db->prepare('SELECT id,username FROM local_membership_users WHERE id=?');
        $stmt->bind_param('i',$id);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();$stmt->close();
        return $user ?: null;
    }
    public function createUser($username,$password) {
        if (!is_string($username) || !preg_match('/^local_[A-Za-z0-9_]{3,40}$/D',$username) || !is_string($password) || strlen($password)<12 || strlen($password)>72) return null;
        $hash=password_hash($password,PASSWORD_DEFAULT);
        $stmt=$this->db->prepare('INSERT INTO local_membership_users (username,password_hash) VALUES (?,?)');
        $stmt->bind_param('ss',$username,$hash);
        try { $ok=$stmt->execute(); } catch (Throwable $e) { $ok=false; }
        $id=$stmt->insert_id;$stmt->close();return $ok ? (int)$id : null;
    }
    public function removeFixture($id) {
        $stmt=$this->db->prepare('DELETE FROM local_membership_users WHERE id=? AND username LIKE ?');
        $prefix='local_probe_%';$stmt->bind_param('is',$id,$prefix);$ok=$stmt->execute();$stmt->close();return $ok;
    }
    public static function respond($status,$value) {
        http_response_code($status);header('Content-Type: application/json');header('Cache-Control: no-store');
        echo json_encode($value,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);exit;
    }
    public function readMap($id,$user) {
        if (!$user || !is_string($id) || !preg_match('/^[a-f0-9]{32}$/D',$id)) return null;
        $stmt=$this->db->prepare('SELECT id,payload FROM local_membership_maps WHERE id=? AND owner_id=?');
        $uid=(int)$user['id'];$stmt->bind_param('si',$id,$uid);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
        return $row ? ['id'=>$row['id'],'data'=>json_decode($row['payload'],true)] : null;
    }
    private function validate($data) {
        if (!is_array($data) || !is_string($data['title']??null) || !strlen(trim($data['title'])) || strlen($data['title'])>100 || !is_array($data['markers']??null) || count($data['markers'])>100) return null;
        $text=function($value,$max){return is_string($value) && strlen($value)<=$max ? strip_tags(html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8')) : null;};
        $title=$text($data['title'],100);$description=$text($data['description']??'',8000);if($description===null)return null;
        $markers=[];
        foreach($data['markers'] as $m){
            if(!is_array($m) || !is_numeric($m['lat']??null) || !is_numeric($m['lng']??null))return null;
            $lat=(float)$m['lat'];$lng=(float)$m['lng'];
            $note=$text($m['description']??'',8000);$group=$text($m['group']??'group0',100);$color=$m['color']??'RED';
            if(!is_finite($lat)||!is_finite($lng)||abs($lat)>90||abs($lng)>180||$note===null||$group===null||!in_array($color,['RED','GREEN','BLUE','ORANGE','YELLOW','GREY','LIME','CYAN','VIOLET','PURPLE','BLACK','WHITE','PINK'],true))return null;
            $markers[]=['lat'=>$lat,'lng'=>$lng,'description'=>$note,'group'=>$group,'color'=>$color];
        }
        $s=$data['settings']??[];$center=$s['center']??['lat'=>0,'lng'=>0];
        if(!is_array($s)||!is_array($center)||!is_numeric($center['lat']??null)||!is_numeric($center['lng']??null))return null;
        $lat=(float)$center['lat'];$lng=(float)$center['lng'];$min=$s['minZoom']??2;$max=$s['maxZoom']??19;$zoom=$s['zoom']??2;
        if(!is_finite($lat)||!is_finite($lng)||abs($lat)>90||abs($lng)>180||!is_int($min)||!is_int($max)||!is_int($zoom)||$min<0||$max>22||$min>$max||$zoom<$min||$zoom>$max)return null;
        foreach(['zoomControl','mapTypeControl','legendVisible'] as $field)if(isset($s[$field])&&!is_bool($s[$field]))return null;
        return ['title'=>$title,'description'=>$description,'markers'=>$markers,'settings'=>['center'=>['lat'=>$lat,'lng'=>$lng],'zoom'=>$zoom,'minZoom'=>$min,'maxZoom'=>$max,'zoomControl'=>$s['zoomControl']??true,'mapTypeControl'=>$s['mapTypeControl']??false,'legendVisible'=>$s['legendVisible']??true,'mapTypeId'=>'roadmap']];
    }
    public function dispatch($path) {
        self::session();$user=$this->user();$method=$_SERVER['REQUEST_METHOD'];
        if($method!=='GET'){
            $origin=$_SERVER['HTTP_ORIGIN']??'';
            if(($origin!=='' && $origin!=='http://'.($_SERVER['HTTP_HOST']??'')) || !hash_equals($_SESSION['local_csrf'],$_SERVER['HTTP_X_LOCAL_CSRF']??''))self::respond(403,['error'=>'Request verification failed']);
        }
        if($path==='/__local_membership/session' && $method==='GET')self::respond(200,['user'=>$user,'csrf'=>$_SESSION['local_csrf']]);
        $body=json_decode(file_get_contents('php://input'),true)??[];if(!is_array($body))self::respond(400,['error'=>'JSON object required']);
        if($path==='/__local_membership/register' && $method==='POST'){
            $id=$this->createUser($body['username']??'', $body['password']??'');
            if(!$id)self::respond(400,['error'=>'Use a unique local_ username and a password of 12 to 72 characters']);
            session_regenerate_id(true);$_SESSION['local_user_id']=$id;$_SESSION['local_csrf']=bin2hex(random_bytes(32));self::respond(201,['user'=>$this->user(),'csrf'=>$_SESSION['local_csrf']]);
        }
        if($path==='/__local_membership/login' && $method==='POST'){
            $name=is_string($body['username']??null)?$body['username']:'';$pass=is_string($body['password']??null)?$body['password']:'';
            $stmt=$this->db->prepare('SELECT id,password_hash FROM local_membership_users WHERE username=?');$stmt->bind_param('s',$name);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
            if(!$row||!password_verify($pass,$row['password_hash']))self::respond(401,['error'=>'Invalid local username or password']);
            session_regenerate_id(true);$_SESSION['local_user_id']=(int)$row['id'];$_SESSION['local_csrf']=bin2hex(random_bytes(32));self::respond(200,['user'=>$this->user(),'csrf'=>$_SESSION['local_csrf']]);
        }
        if($path==='/__local_membership/logout' && $method==='POST'){
            $_SESSION=[];session_regenerate_id(true);$_SESSION['local_csrf']=bin2hex(random_bytes(32));self::respond(200,['user'=>null,'csrf'=>$_SESSION['local_csrf']]);
        }
        if(!$user)self::respond(401,['error'=>'Local sign-in required']);
        if($path==='/__local_membership/maps' && $method==='GET'){
            $stmt=$this->db->prepare('SELECT id,payload FROM local_membership_maps WHERE owner_id=? ORDER BY updated_at DESC,id');$uid=(int)$user['id'];$stmt->bind_param('i',$uid);$stmt->execute();$rows=$stmt->get_result();$maps=[];while($row=$rows->fetch_assoc()){$d=json_decode($row['payload'],true);$maps[]=['id'=>$row['id'],'title'=>$d['title']];}$stmt->close();self::respond(200,['maps'=>$maps]);
        }
        $id=null;if(preg_match('#^/__local_membership/maps/([a-f0-9]{32})$#D',$path,$m))$id=$m[1];
        if($id && !$this->readMap($id,$user))self::respond(404,['error'=>'Map not found']);
        if($id && $method==='GET')self::respond(200,$this->readMap($id,$user));
        if($id && $method==='DELETE'){
            $stmt=$this->db->prepare('DELETE FROM local_membership_maps WHERE id=? AND owner_id=?');$uid=(int)$user['id'];$stmt->bind_param('si',$id,$uid);$stmt->execute();$stmt->close();self::respond(200,['deleted'=>true]);
        }
        if(($path==='/__local_membership/maps' && $method==='POST') || ($id && $method==='PUT')){
            $data=$this->validate($body);if(!$data)self::respond(400,['error'=>'Invalid map data or settings']);
            $payload=json_encode($data);$uid=(int)$user['id'];
            if(!$id){$id=bin2hex(random_bytes(16));$stmt=$this->db->prepare('INSERT INTO local_membership_maps (id,owner_id,payload) VALUES (?,?,?)');$stmt->bind_param('sis',$id,$uid,$payload);$status=201;}
            else {$stmt=$this->db->prepare('UPDATE local_membership_maps SET payload=? WHERE id=? AND owner_id=?');$stmt->bind_param('ssi',$payload,$id,$uid);$status=200;}
            if(!$stmt->execute())self::respond(500,['error'=>'Local save failed']);$stmt->close();self::respond($status,['id'=>$id,'data'=>$data]);
        }
        self::respond(404,['error'=>'Local endpoint not found']);
    }
}
