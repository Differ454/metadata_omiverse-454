<?php

echo '
<div class="test">
      <form method="post">
           <select class="zonal_select_name" name="autorunTest" style="width:100%;" >';
$sql = "SELECT * FROM `system-powershell-autorun` ORDER BY autorunfile ASC";
$result = $conn->query($sql);
echo ' <option value="" disabled>SELECT</option>';
while ($row = $result->fetch_assoc()) {
  echo '<option value="' . $row['autorunfile'] . '" ';
  if ($_SESSION['autorunTest'] == $row['autorunfile']) {
    echo 'selected';
  }
  echo '  >' . $row['autorunfile'] . '</option>';
}


$getAllOtherPartnersQuery = mysqli_query($conn, "SELECT * FROM `system-partners` WHERE cvr = '$_SESSION[cvr]'");

if ($getAllOtherPartnersQuery) {
  while ($getAllOtherPartnersResult = mysqli_fetch_array($getAllOtherPartnersQuery)) {
    $name = $getAllOtherPartnersResult['name'];
    $cvr = $getAllOtherPartnersResult['cvr'];
  }
}


echo '</select> <br></br>
      <input type="hidden" name="partner"  value="' . $cvr . '"> 
      <input type="hidden" name="dropdown"  value="Submit">
      <input type="submit" name="submit"  class="button_down" value="Submit">
    </form> <br> <br> <br> <br>


      <div><a>' . $name . '</a> 
           <p>' . $_SESSION['response_name'] . '</p>

       </div>';
?>

<style>
  .test {
    width: 90%;
    margin: auto;
  }

  .zonal_select_name {
    height: 40px;
    margin-top: 20px;

  }

  option {
    font-size: 20px
  }

  .button_down {
    height: 30px;
    text-align: center;
    border: 2px solid gray;
    border-radius: 3px;
    padding-bottom: 36px;
    background-color: #eee;
    font-size: 12px;
    font-weight: bold;
    width: 100px;
    letter-spacing: 3px;
    margin-top: 25px;
    position: absolute;
    left: auto;

  }

  .testA {
    width: 90%;
    margin: auto;
    height: 40px;
    font-size: 21px;
    font-weight: bold;
    padding: 20px;
    text-decoration: none;

  }

  .testA p {
    width: 90%; 
    margin: auto;
    height: 40px;
    font-size: 21px;
    font-weight: bold;
    position: absolute;
    margin-top: 20px;

  }
</style>