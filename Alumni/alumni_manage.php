<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Alumni/alumni_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__($guid, getModuleName($_GET['q']))."</a> > </div><div class='trailEnd'>".__($guid, 'Manage Alumni').'</div>';
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $graduatingYear = null;
    if (isset($_GET['graduatingYear'])) {
        $graduatingYear = $_GET['graduatingYear'];
    }

    echo '<h3>';
    echo __($guid, 'Filter');
    echo '</h3>';
    echo "<form method='get' action='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Alumni/alumni_manage.php'>";
    echo "<table class='noIntBorder' cellspacing='0' style='width: 100%'>"; ?>
	<tr>
		<td>
			<b><?php echo __($guid, 'Graduating Year') ?></b><br/>
			<span style="font-size: 90%"><i></i></span>
		</td>
		<td class="right">
			<select name="graduatingYear" id="graduatingYear" style="width: 302px">
				<?php
                echo "<option value=''></option>";
                for ($i = date('Y'); $i > (date('Y') - 200); --$i) {
                    $selected='' ;
                    if ($graduatingYear==$i) {
                        $selected='selected' ;
                    }
                    echo "<option $selected value='$i'>$i</option>";
                }
                ?>
			</select>
		</td>
	</tr>
	<?php

    echo '<tr>';
        echo "<td class='right' colspan=2>";
            echo "<input type='hidden' name='q' value='".$_GET['q']."'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Alumni/alumni_manage.php'>".__($guid, 'Clear Filters').'</a> ';
            echo "<input type='submit' value='".__($guid, 'Go')."'>";
        echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';

    echo '<h3>';
    echo __($guid, 'View Records');
    echo '</h3>';
    //Set pagination variable
    $page = 1;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    if ((!is_numeric($page)) or $page < 1) {
        $page = 1;
    }

    //Search with filters applied
    try {
        $data = array();
        $sqlWhere = 'WHERE ';
        if ($graduatingYear != '') {
            $data['graduatingYear'] = $graduatingYear;
            $sqlWhere .= ' alumniAlumnus.graduatingYear=:graduatingYear AND ';
        }
        if ($sqlWhere == 'WHERE ') {
            $sqlWhere = '';
        } else {
            $sqlWhere = substr($sqlWhere, 0, -5);
        }
        $sql = "SELECT * FROM alumniAlumnus $sqlWhere ORDER BY timestamp DESC";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) { echo "<div class='error'>".$e->getMessage().'</div>';
    }
    $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);

    echo "<div class='linkTop'>";
    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/alumni_manage_add.php&graduatingYear=$graduatingYear'>".__($guid, 'Add')."<img style='margin: 0 0 -4px 5px' title='".__($guid, 'Add')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_new.png'/></a>";
    echo '</div>';

    if ($result->rowCount() < 1) { echo "<div class='error'>";
        echo __($guid, 'There are no records to display.');
        echo '</div>';
    } else {
        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', "graduatingYear=$graduatingYear");
        }

        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __($guid, 'Name');
        echo '</th>';
        echo '<th>';
        echo __($guid, 'Email');
        echo '</th>';
        echo '<th>';
        echo __($guid, 'Graduating Year');
        echo '</th>';
        echo "<th style='min-width: 70px'>";
        echo __($guid, 'Actions');
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
        try {
            $resultPage = $connection2->prepare($sqlPage);
            $resultPage->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        while ($row = $resultPage->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

			//COLOR ROW BY STATUS!
			echo "<tr class=$rowNum>";
			echo '<td>';
			echo formatName($row['title'], $row['firstName'], $row['surname'], 'Parent', false, false).'</b><br/>';
			echo '</td>';
			echo '<td>';
			echo $row['email'];
			echo '</td>';
			echo '<td>';
			echo $row['graduatingYear'];
			echo '</td>';
			echo '<td>';
			echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/alumni_manage_edit.php&alumniAlumnusID='.$row['alumniAlumnusID']."&graduatingYear=$graduatingYear'><img title='".__($guid, 'Edit')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/config.png'/></a> ";
			echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/alumni_manage_delete.php&alumniAlumnusID='.$row['alumniAlumnusID']."&graduatingYear=$graduatingYear'><img title='".__($guid, 'Delete')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a> ";
			echo "<script type='text/javascript'>";
			echo '$(document).ready(function(){';
			echo "\$(\".comment-$count\").hide();";
			echo "\$(\".show_hide-$count\").fadeIn(1000);";
			echo "\$(\".show_hide-$count\").click(function(){";
			echo "\$(\".comment-$count\").fadeToggle(1000);";
			echo '});';
			echo '});';
			echo '</script>';
			echo "<a title='".__($guid, 'View Details')."' class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/page_down.png' alt='".__($guid, 'View Details')."' onclick='return false;' /></a>";
			echo '</td>';
			echo '</tr>';
			echo "<tr class='comment-$count' id='comment-$count'>";
			echo '<td colspan=4>';
			echo '<b>'.__($guid, 'Official Name').': </b>'.$row['officialName'].'<br/>';
			echo '<b>'.__($guid, 'Maiden Name').': </b>'.$row['maidenName'].'<br/>';
			echo '<b>'.__($guid, 'Gender').': </b>'.$row['gender'].'<br/>';
			echo '<b>'.__($guid, 'Username').': </b>'.$row['username'].'<br/>';
			echo '<b>'.__($guid, 'Date Of Birth').': </b>';
			if ($row['dob'] != '') {
				echo dateConvertBack($guid, $row['dob']);
			}
			echo '<br/>';
			echo '<b>'.__($guid, 'Country of Residence').': </b>'.$row['address1Country'].'<br/>';
			echo '<b>'.__($guid, 'Profession').': </b>'.$row['profession'].'<br/>';
			echo '<b>'.__($guid, 'Employer').': </b>'.$row['employer'].'<br/>';
			echo '<b>'.__($guid, 'Job Title').': </b>'.$row['jobTitle'].'<br/>';
			echo '<b>'.__($guid, 'Date Joined').': </b>';
			if ($row['timestamp'] != '') {
				echo dateConvertBack($guid, substr($row['timestamp'], 0, 10));
			}
			echo '<br/>';
			echo '</td>';
			echo '</tr>';
		}
        echo '</table>';

        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom', "graduatingYear=$graduatingYear");
        }
    }
}
?>
