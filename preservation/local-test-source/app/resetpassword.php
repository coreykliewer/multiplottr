<?php
include 'db/db.class.php';
include 'db/plotter.class.php';
$pltr = new Plotter();
if(isset($_POST['resetPassword']) && $_POST['resetPassword'] == 'Submit') {
    if(!$pltr->validPassToken($_POST['passtoken'])){
        echo "Invalid access or the page has expired";
        exit;
    } else {
      $out = $pltr->resetFPPassword($_POST['passtoken'], $_POST['newpassword']);
      echo $out;
    }
} else {
    if(!isset($_GET['passtoken']) || !$_GET['passtoken']) {
       echo "Unauthorized access";
       exit;
    } else if(!$pltr->validPassToken($_GET['passtoken'])){
        echo "Unauthorized access or the page has expired";
        exit;
    }
?>
    <p>
<form method="post" action="">
Enter password: <input type="password" name="newpassword">
<input type="hidden" name="passtoken" value="<?php echo $_GET['passtoken']?>">
<input type="submit" name="resetPassword" value="Submit">
</form>
<?php
}



