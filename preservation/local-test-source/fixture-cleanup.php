<?php
require '/config/local-membership.php';$service=new LocalMembership();
$id=(int)getenv('LOCAL_FIXTURE_USER');if(!$id)exit(1);
if(!$service->removeFixture($id)){fwrite(STDERR,"Fixture cleanup failed\n");exit(1);}
echo "Synthetic local membership account and its maps removed.\n";
