<?php
require '/config/local-membership.php';
$service=new LocalMembership();
$db=new mysqli('db','local_test','local-only-test','[REDACTED_ACCOUNT]');
$planned=json_decode(base64_decode(getenv('LOCAL_MEMBER_FIXTURES_B64')?:''),true);
if(!is_array($planned))throw new RuntimeException('Valid fixture metadata is required for cleanup');
$count=0;
foreach($planned as $fixture){
 $name=$fixture['username']??'';if(!preg_match('/^local_probe_[A-Za-z0-9_]+$/D',$name))throw new RuntimeException('Refusing non-fixture cleanup');
 $stmt=$db->prepare('SELECT id FROM local_membership_users WHERE username=?');$stmt->bind_param('s',$name);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();if($row){$service->removeFixture((int)$row['id']);$count++;}
 $stmt=$db->prepare('SELECT COUNT(*) AS n FROM local_membership_users WHERE username=?');$stmt->bind_param('s',$name);$stmt->execute();$left=(int)$stmt->get_result()->fetch_assoc()['n'];$stmt->close();if($left)throw new RuntimeException('Fixture cleanup failed');
}
echo 'Synthetic local membership accounts removed: '.$count.PHP_EOL;
