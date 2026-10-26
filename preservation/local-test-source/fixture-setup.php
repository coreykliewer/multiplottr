<?php
require '/config/local-membership.php';$service=new LocalMembership();
$tag='local_probe_'.bin2hex(random_bytes(5));$password=bin2hex(random_bytes(18));$id=$service->createUser($tag,$password);
if(!$id){fwrite(STDERR,"Synthetic local account creation failed\n");exit(1);}
echo json_encode(['userId'=>$id,'id'=>$id,'tag'=>$tag,'username'=>$tag,'password'=>$password]);
