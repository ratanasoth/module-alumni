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

include '../../functions.php';
include '../../config.php';

//New PDO DB connection
try {
    $connection2 = new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

@session_start();

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]['timezone']);

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Alumni/publicRegistration.php';

$proceed = false;

if (isset($_SESSION[$guid]['username']) == false) {
    $enablePublicRegistration = getSettingByScope($connection2, 'Alumni', 'showPublicRegistration');
    if ($enablePublicRegistration == 'Y') {
        $proceed = true;
    }
}

if ($proceed == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $title = $_POST['title'];
    $surname = $_POST['surname'];
    $firstName = $_POST['firstName'];
    $officialName = $_POST['officialName'];
    $maidenName = $_POST['maidenName'];
    $gender = $_POST['gender'];
    $username = $_POST['username2'];
    $dob = $_POST['dob'];
    if ($dob == '') {
        $dob = null;
    } else {
        $dob = dateConvert($guid, $dob);
    }
    $email = $_POST['email'];
    $address1Country = $_POST['address1Country'];
    $profession = $_POST['profession'];
    $employer = $_POST['employer'];
    $jobTitle = $_POST['jobTitle'];
    $graduatingYear = $_POST['graduatingYear'];
    $formerRole = $_POST['formerRole'];

    if ($surname == '' or $firstName == '' or $officialName == '' or $gender == '' or $dob == '' or $email == '' or $formerRole == '') {
        //Fail 3
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Check publicRegistrationMinimumAge
        $publicRegistrationMinimumAge = getSettingByScope($connection2, 'User Admin', 'publicRegistrationMinimumAge');

        $ageFail = false;
        if ($publicRegistrationMinimumAge == '') {
            $ageFail = true;
        } elseif ($publicRegistrationMinimumAge > 0 and $publicRegistrationMinimumAge > getAge(dateConvertToTimestamp($dob), true, true)) {
            $ageFail = true;
        }

        if ($ageFail == true) {
            //Fail 5
            $URL .= '&return=error5';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('title' => $title, 'surname' => $surname, 'firstName' => $firstName, 'officialName' => $officialName, 'maidenName' => $maidenName, 'gender' => $gender, 'username' => $username, 'dob' => $dob, 'email' => $email, 'address1Country' => $address1Country, 'profession' => $profession, 'employer' => $employer, 'jobTitle' => $jobTitle, 'graduatingYear' => $graduatingYear, 'formerRole' => $formerRole);
                $sql = 'INSERT INTO alumniAlumnus SET title=:title, surname=:surname, firstName=:firstName, officialName=:officialName, maidenName=:maidenName, gender=:gender, username=:username, dob=:dob, email=:email, address1Country=:address1Country, profession=:profession, employer=:employer, jobTitle=:jobTitle, graduatingYear=:graduatingYear, formerRole=:formerRole';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
                $URL .= 'return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Success 0
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
