<?php

/*<-------------- Function to ADD a new Port with the plus icon "+" ------------------------>*/

echo '
    <div class="button-flex-container">
      <span class="divider"><!-- divider --></span>
      <form onsubmit="displayLoader()" action="" method="post" title="Please press to create a new Port.">
        <input type="hidden" name="displayPort" value="port">
        <input type="image" style="border-radius: 50%;border:1px solid lightgrey;padding:5px;" src="/png/267-plus.png" width="24" height="24" />
     </form>';

echo '
       <span class="divider"><!-- divider --></span>
     </div>';

if ($_SESSION['displayPort'] != -1 && !is_null($_SESSION['displayPort'])) {

    echo '
    <div class="newOverlay">';
    if ($_SESSION['displayPortError'] != 1) {
        echo '<div class="newOverlayWrapper">';
    } else {
        echo '<div style="background-color:rgb(138, 30, 30, 0.95)!important;" class="newOverlayWrapper">';
    }
    echo '
    
            <div class="newOverlayCloseBtn">
                <form onsubmit="displayLoader()" name="closeOverlay" method="post">
                    <label class="closeOverlay">
                    <img src="/png/close-overlay.png" width="20" height="20" class="pointer"/>
                    <input type="hidden" name="displayPort" value="-1">
                    <input type="submit" name="submit">
                    </label>
                </form>
            </div>
    
  
  
            <div class="newOverlayContent">
                    <form onsubmit="displayLoader()" name="" method="post" style="height:100%;">
  
                <label title="Add a New Port." for="text">add a new port</label>';
    if ($_SESSION['displayPortError'] != 1) {
        echo '<label for="text"></label>';
    } else {
        echo '<label for="text">Something went wrong, please try again.</label>';
    }

    echo '
                      <form action="" method="post">
                        <div class="newOverlayElement Child1">

                             <label for="text">Port:</label>
                             <input type="number" name="addNewPort" value="" required>
                             
                      

                             <label for="text">Description:</label>
                             <input type="text" name="addNewDescription" value="" required>
                      
                      
                             <label for="text">TCP:</label>
                             <input type="checkbox" title="TCP." name="ADDtoggleTcpPorts" value="" class="AlignCheckBoxes">
                      
                       
                             <label for="text">UDP:</label>
                             <input type="checkbox" title="UDP." name="ADDtoggleUdpPorts" value="" class="AlignCheckBoxes">
                      

                             <label for="text">HighRisk:</label>
                             <input type="checkbox" title="HighRisk." name="ADDtogglePortsIDHIGHRISK" value="" class="AlignCheckBoxes">

                        </div> 
                      <input type="submit" title="Please press to add a new Port." name="submit" value="Add" class="newOverlaySubmit">
                     </form>
                   
                    </form>
                
            </div>
  
        </div>
  </div>
        
    ';
};

/*<---- Main Ports Table ----->*/

echo '
        <table>
            <thead>
                <tr>
                    
                    <th align="left" title="Port Number." width="40" scope="col" >Port</th>
                    <th align="left" title="Description." scope="col" >Description</th>
                    <th align="center" title="TCP." width="125" scope="col" >tcp</th>
                    <th align="center" title="UDP." width="100" scope="col" >udp</th>
                    <th align="center" title="HIGHRISK." width="100" scope="col" >highrisk</th>
                    <th align="center" title="Edit." width="100" scope="col" >Edit</th>
                    <th align="center" title="Delete." width="100" scope="col" >delete</th>
                   
                </tr>
            </thead>
        <tbody>
        ';


$sqlResult = mysqli_query($conn, "SELECT id, port, description, tcp, udp, highrisk FROM `system-ports` ORDER BY port ASC");
if ($sqlResult) {
    while ($getSqlResult = mysqli_fetch_array($sqlResult)) {
        $sqlId = $getSqlResult['id'];
        $sqlPort = $getSqlResult['port'];
        $sqlDescription = $getSqlResult['description'];
        $sqlTcp = $getSqlResult['tcp'];
        $sqlDup = $getSqlResult['udp'];
        $sqlHighrisk = $getSqlResult['highrisk'];



        if ($sqlTcp == 'true') {
            $tcpPortsValue = 1;
        } else {
            $tcpPortsValue = 0;
        }

        if ($sqlDup == 'true') {
            $udpPortsValue = 1;
        } else {
            $udpPortsValue = 0;
        }

        if ($sqlHighrisk == 'true') {
            $highriskPortsValue = 1;
        } else {
            $highriskPortsValue = 0;
        }

        echo '
    
                        </td>
                        <td align="right" scope="row" title="Port Number" data-label="PORT.">' . $sqlPort . '</td>
                        <td align="left" scope="row" data-label="DESCRIPTION">' . $sqlDescription . '</td>
                        
                        <td align="center" scope="row" data-label="TCP">
                        <form onsubmit="displayLoader()" name="toggleTcpPorts" title="TCP." method="post">
                        <input name="togglePortsIDtcp" type="hidden" value="' . $sqlId . '">
                        <input name="toggleTcpPorts" type="hidden" value="' . $tcpPortsValue . '">
                        <input onclick="displayLoader()" type="checkbox" name="" value=""';
        if ($tcpPortsValue == 1) {
            echo 'checked="checked"';
        }
        echo 'onchange="this.form.submit()"> 

                        </form>
                        </td>

                        <td align="center" scope="row" data-label="UDP">
                        <form onsubmit="displayLoader()" name="toggleUdpPorts" title="UDP." method="post">
                        <input name="togglePortsIDudp" type="hidden" value="' . $sqlId . '">
                        <input name="toggleUdpPorts" type="hidden" value="' . $udpPortsValue . '">
                        <input onclick="displayLoader()" type="checkbox" name="" value=""';
        if ($udpPortsValue == 1) {
            echo 'checked="checked"';
        }
        echo 'onchange="this.form.submit()">

                        </form>
                        </td>

                        <td align="center" scope="row" data-label="HIGHRISK">
                        <form onsubmit="displayLoader()" name="togglehighriskPorts" title="HIGHRISK." method="post">
                        <input name="togglePortsIDHIGHRISK" type="hidden" value="' . $sqlId . '">
                        <input name="togglehighriskPorts" type="hidden" value="' . $highriskPortsValue . '">
                        <input onclick="displayLoader()" type="checkbox" name="" value=""';
        if ($highriskPortsValue == 1) {
            echo 'checked="checked"';
        }


        echo 'onchange="this.form.submit()">

        </form>
        </td>
      
      
        <td align="center" scope="row" data-label="EDIT">
        <form title="Edit Description." onsubmit="displayLoader()" action="" method="post">
            <input type="hidden" name="editPortDescription" value="' . $sqlId . '"/>
            <input type="image" src="/svg/edit.png" width="15" height="15" />
        </form>	
    </td>
        
    <td align="center" scope="row" data-label="DELETE">
    <form title="Delete Port." onsubmit="displayLoader()" action="" method="post">
    <input type="hidden" name="deletePortId" value="' . $sqlId . '"/>
    <input type="image" src="/png/272-cross.png" width="15" height="15" />
    </form>
</td>
       
    </td>
             </tr>
                   ';
    }

    echo '    
</tbody>
</table>';
}

/*<---------------- Edit Port function --------------------->*/


if ($_SESSION['editPortDescription'] != -1 && !is_null($_SESSION['editPortDescription'])) {

    $getPortsDetailsQuery = mysqli_query($conn, "SELECT * FROM `system-ports` WHERE id = $_SESSION[editPortDescription]");
    if ($getPortsDetailsQuery) {
        $getPortsDetailsResult = mysqli_fetch_array($getPortsDetailsQuery);

        echo '
                <div class="newOverlay">';
        if ($_SESSION['editPortDescriptionError'] != 1) {
            echo '<div class="newOverlayWrapper">';
        } else {
            echo '<div style="background-color:rgb(138, 30, 30, 0.95)!important;" class="newOverlayWrapper">';
        }
        echo '
            
                    <div class="newOverlayCloseBtn">
                        <form onsubmit="displayLoader()" name="closeOverlay" method="post">
                            <label class="closeOverlay">
                            <img src="/png/close-overlay.png" width="20" height="20" class="pointer" />
                            <input type="hidden" name="editPortDescription" value="-1">
                            <input type="submit" name="submit">
                            </label>
                        </form>
                    </div>
            
        
        
                    <div class="newOverlayContent">
                        <form onsubmit="displayLoader()" name="" method="post" style="height:100%;">
        
                        <label title="Description of the Port number to be changed." for="text">Edit Port Description\'' . $getPortsDetailsResult['port'] . '\' </label>';
        if ($_SESSION['editPortDescriptionError'] != 1) {
            echo '<label for="text"></label>';
        } else {
            echo '<label for="text">Something went wrong please try again</label>';
        }


        echo '
                <div class="newOverlayElement Child1">

                    <label for="text">Description:</label>
                    <input type="text" title="Please fill out to edit the port description." name="editPortDescriptionlabel" value="' . $getPortsDetailsResult['description'] . '">

                    

                </div>
                 <input type="submit" title="Please press to save the new port description." name="submit" value="Save" class="newOverlaySubmit">
                
             </form>
          </div>

          

        
    </div>
    </div>
    ';
    }
}


/*<---------------- Delete Port function --------------------->*/

if ($_SESSION['deletePortId'] != -1 && !is_null($_SESSION['deletePortId'])) {

    $getPortsDetailsQuery = mysqli_query($conn, "SELECT * FROM `system-ports` WHERE id = $_SESSION[deletePortId]");
    if ($getPortsDetailsQuery) {
        $getPortsDetailsResult = mysqli_fetch_array($getPortsDetailsQuery);
        $sqlIdPort = $_SESSION['deletePortId'];

        echo '
                <div class="newOverlay">';
        if ($_SESSION['deletePortError'] != 1) {
            echo '<div class="newOverlayWrapper">';
        } else {
            echo '<div style="background-color:rgb(138, 30, 30, 0.95)!important;" class="newOverlayWrapper">';
        }
        echo '
            
                    <div class="newOverlayCloseBtn">
                        <form onsubmit="displayLoader()" name="closeOverlay" method="post">
                            <label class="closeOverlay">
                            <img src="/png/close-overlay.png" width="20" height="20" class="pointer" />
                            <input type="hidden" name="deletePortId" value="-1">
                            <input type="submit" name="submit">
                            </label>
                        </form>
                    </div>
            
        
        
                    <div class="newOverlayContent">
                        <form onsubmit="displayLoader()" name="" method="post" style="height:100%;">
        
                        <label for="text">Delete Port</label>';
        if ($_SESSION['deletePortError'] != 1) {
            echo '<label for="text"></label>';
        } else {
            echo '<label for="text">Something went wrong please try again</label>';
        }


        echo '
                <div class="newOverlayElement Child1">


                    <label for="text">PORT NUMBER:</label>
                    <label for="text">\'' . $getPortsDetailsResult['port'] . '\'</label>

                    <label for="text">Description:</label>
                    <label for="text">\'' . $getPortsDetailsResult['description'] . '\'</label>

                    <input type="hidden" name="DeletePortLabel" value="' . $sqlIdPort . '">

                    <div class="newOverlayRadioElementWrapper"><input class="newOverlayRadioInput" type="checkbox" name="" value="true" required><label class"newOverlayRadioLabel"> PLEASE CHECK IF YOU ARE SURE TO DELETE THIS PORT.</label></div>
                    
                   

                </div>
                 <input type="submit" title="Please press to delete this Port." name="submit" value="Delete" class="newOverlaySubmit">
             </form>
          </div>

          

        
    </div>
    </div>
    ';
    }
}


?>

<style>
    .AlignCheckBoxes {
        margin-left: 32px;
    }
</style>