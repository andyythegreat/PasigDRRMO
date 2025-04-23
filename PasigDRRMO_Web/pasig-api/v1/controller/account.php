<?php
require_once "../helper/conn.php";
require_once "../helper/date.php";
require_once "../helper/message.php";
require_once "../helper/utils.php";
require_once "../model/response.php";

try {
    $writeDB = DB::connectionWriteDB();
    $readDB = DB::connectionReadDB();
} catch (PDOException $ex) {
    error_log("Connection Error - " . $ex, 0);
    sendResponse(500, false, "Database Connection Error");
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    if (
        !isset($_SERVER["CONTENT_TYPE"]) ||
        $_SERVER["CONTENT_TYPE"] !== "application/json"
    ) {
        sendResponse(400, false, "Content type header is not JSON");
    }

    $rawPOSTData = file_get_contents("php://input");

    if (!($jsonData = json_decode($rawPOSTData))) {
        sendResponse(400, false, "Request body is not JSON");
    }

    if (!isset($jsonData->mode)) {
        sendResponse(400, false, "Method not found");
    } else {
        $mode = $jsonData->mode;
        
        
        include('smtp/PHPMailerAutoload.php');

        function smtp_mailer($to, $subject, $msg)
        {
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 587;
            $mail->IsHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Username = "pasigdrrmo1@gmail.com";
            $mail->Password = "ifdu tgef zrqp oqzc";
            $mail->SetFrom("pasigdrrmo1@gmail.com", "Pasig City DRRMO Support");
            $mail->Subject = $subject;
            $mail->Body = $msg;
            $mail->AddAddress($to);
            $mail->SMTPOptions = array('ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => false
            ));
        
            return $mail->Send();
        }

        if ($mode == "register_account") {
            if (
                !isset($jsonData->barangay) ||
                !isset($jsonData->email) ||
                !isset($jsonData->fullName) ||
                !isset($jsonData->username) ||
                !isset($jsonData->contactNumber) ||
                !isset($jsonData->birthday) ||
                !isset($jsonData->password) ||
                !isset($jsonData->repeatPassword)
            ) {
                sendResponse(400, false, "Incomplete Request");
            }

            $barangay = $jsonData->barangay;
            $email = $jsonData->email;
            $fullName = $jsonData->fullName;
            $username = $jsonData->username;
            $contactNumber = $jsonData->contactNumber;
            $birthday = $jsonData->birthday;
            $password = $jsonData->password;
            $repeatPassword = $jsonData->repeatPassword;

            if ($barangay == "") {
                sendResponse(400, false, "Barangay is required");
            }

            if ($email == "") {
                sendResponse(400, false, "Email address is required");
            }

            if ($fullName == "") {
                sendResponse(400, false, "Full name is required");
            }

            if ($username == "") {
                sendResponse(400, false, "Username is required");
            }

            if ($contactNumber == "") {
                sendResponse(400, false, "Contact Number is required");
            }

            if ($birthday == "") {
                sendResponse(400, false, "Birthday is required");
            }
            
            $birthDate = DateTime::createFromFormat('m/d/Y', $birthday);
            
            if (!$birthDate || $birthDate->format('m/d/Y') !== $birthday) {
                sendResponse(400, false, "Invalid date format for birthday. Expected format is MM/DD/YYYY.");
            }
            
            $currentYear = (int)date("Y");
            $birthYear = (int)$birthDate->format("Y");
            
            if ($birthYear === $currentYear) {
                sendResponse(400, false, "Birth year cannot be the current year");
            }


            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            
            if ($age < 13) {
                sendResponse(400, false, "You must be at least 13 years old to create an account");
            }

            if ($password == "") {
                sendResponse(400, false, "Password is required");
            }

            if (!validateEmail($email)) {
                sendResponse(400, false, "Invalid Email Address");
            }

            if (count(explode(" ", $username)) > 1) {
                sendResponse(400, false, "Username must not have spaces");
            }

            if ($password != $repeatPassword) {
                sendResponse(
                    400,
                    false,
                    "Password and Confirm Password doesnt match"
                );
            }

            $has_eightchar = strlen($password) >= 8;
            $has_uppercase = preg_match("@[A-Z]@", $password);
            $has_lowercase = preg_match("@[a-z]@", $password);
            $has_number = preg_match("@[0-9]@", $password);
            $has_specialChars = preg_match("/[^a-zA-Z0-9]/", $password);
            $arrErrors = [];

            if (!$has_eightchar) {
                array_push(
                    $arrErrors,
                    "Password must be at least 8 characters"
                );
            }

            if (!$has_uppercase) {
                array_push($arrErrors, "Password must have a capital letter");
            }

            if (!$has_number) {
                array_push($arrErrors, "Password must have a number");
            }

            if (!$has_specialChars) {
                array_push(
                    $arrErrors,
                    "Password must have a special character"
                );
            }

            if (count($arrErrors) >= 1) {
                sendResponse(
                    400,
                    false,
                    "There are errors in your password",
                    $arrErrors,
                    false
                );
            }

            if (
                !$has_eightchar ||
                !$has_uppercase ||
                !$has_lowercase ||
                !$has_number ||
                !$has_specialChars
            ) {
                sendResponse(400, false, "Password doesnt meet the criteria");
            }

            $query_email = $writeDB->prepare(
                "SELECT * FROM pasigresident WHERE Email = :email"
            );
            $query_email->bindParam(":email", $email, PDO::PARAM_STR);
            $query_email->execute();

            $rowCount = $query_email->rowCount();

            if ($rowCount != 0) {
                sendResponse(400, false, "Email address already used");
            }

            $query_username = $writeDB->prepare(
                "SELECT * FROM pasigresident WHERE Username = :username"
            );
            $query_username->bindParam(":username", $username, PDO::PARAM_STR);
            $query_username->execute();

            $rowCount = $query_username->rowCount();

            if ($rowCount != 0) {
                sendResponse(400, false, "Username already used");
            }

            $verificationToken = bin2hex(random_bytes(16));
        
            $query = $writeDB->prepare(
                "INSERT INTO pasigresident (Barangay, Email, FullName, Username, ContactNumber, Birthday, `Password`, is_verified, verification_token)
                 VALUES (:barangay, :email, :fullName, :username, :contactNumber, :birthday, :password, 0, :verificationToken)"
            );
            $query->bindParam(":barangay", $barangay, PDO::PARAM_STR);
            $query->bindParam(":email", $email, PDO::PARAM_STR);
            $query->bindParam(":fullName", $fullName, PDO::PARAM_STR);
            $query->bindParam(":username", $username, PDO::PARAM_STR);
            $query->bindParam(":contactNumber", $contactNumber, PDO::PARAM_STR);
            $query->bindParam(":birthday", $birthday, PDO::PARAM_STR);
            $query->bindParam(":password", $password, PDO::PARAM_STR);
            $query->bindParam(":verificationToken", $verificationToken, PDO::PARAM_STR);
            $query->execute();
        
            $rowCount = $query->rowCount();
        
            if ($rowCount === 0) {
                sendResponse(500, false, "There was an error registering account.");
            }
        
            $verificationLink = "http://pasigdrrmo.site/verify_email.php?token=" . $verificationToken;
            $subject = "Verify Your Email Address";
            
            $message = "
            <html>
            <head>
              <title>Verify Your Email Address</title>
              <style>
                body {
                  font-family: Arial, sans-serif;
                  background-color: #f4f4f4;
                  margin: 0;
                  padding: 0;
                  color: #333;
                }
                .email-container {
                  width: 100%;
                  background-color: #ffffff;
                  padding: 20px;
                  box-sizing: border-box;
                  margin: 0 auto;
                  max-width: 600px;
                }
                h2 {
                  color: #333;
                  font-size: 24px;
                  text-align: center;
                }
                .content {
                  text-align: center;
                  font-size: 16px;
                  margin-bottom: 20px;
                }
                .footer {
                  text-align: center;
                  font-size: 14px;
                  color: #777;
                  margin-top: 20px;
                }
              </style>
            </head>
            <body>
              <div class='email-container'>
                <h2>Thank you for registering with Pasig City DRRMO!</h2>
                <div class='content'>
                  <p>To complete your registration, please verify your email address by clicking the button below:</p>
                  <a href='$verificationLink' style='display: inline-block; padding: 10px 20px; font-size: 16px; color: #ffffff; background-color: #007BFF; text-decoration: none; border-radius: 5px;'>Verify Email</a>
                </div>
                <div class='footer'>
                  <p>If you did not register for an account, please ignore this email.</p>
                  <p>&copy; " . date("Y") . " Pasig City DRRMO. All rights reserved.</p>
                </div>
              </div>
            </body>
            </html>
            ";

            if (!smtp_mailer($email, $subject, $message)) {
                sendResponse(500, false, "Account created, but verification email could not be sent. Please contact support.");
            }
            sendResponse(201, true, "Account created successfully. Please verify your email before logging in.");
    } elseif ($mode == "login") {
        $emailUsername = $jsonData->emailUsername ?? null;
        $password = $jsonData->password ?? null;
        $isResponder = false;
        $isBarangayPosition = false;
    
        if ($emailUsername == "") {
            sendResponse(400, false, "Please input your Email or Username");
        }
    
        if ($password == "") {
            sendResponse(400, false, "Please input your Password");
        }
    
        $query = $writeDB->prepare('
        SELECT 
            Name AS Fullname,
            EmailAddress AS Email, 
            Username,
            Address,
            Role AS Position,
            Profile AS Logo
        FROM firerespondersaccount 
        WHERE EmailAddress = :emailAddress AND `Password` = :password;
        ');
        $query->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
        $query->bindParam(":password", $password, PDO::PARAM_STR);
        $query->execute();
    
        $isResponder = $query->rowCount() != 0;
    
        if (!$isResponder) {
            $query = $writeDB->prepare('
            SELECT * 
            FROM pasigresident 
            WHERE (Email = :emailAddress OR Username = :username) AND `Password` = :password;
            ');
            $query->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
            $query->bindParam(":username", $emailUsername, PDO::PARAM_STR);
            $query->bindParam(":password", $password, PDO::PARAM_STR);
            $query->execute();
        }
    
        if ($query->rowCount() === 0) {
            $query = $writeDB->prepare('
            SELECT 
                ID,
                Logo,
                Username,
                Email AS Email,
                Address,
                Position,
                is_verified
            FROM c3_addaccount 
            WHERE (Email = :emailAddress OR Username = :username) AND `Password` = :password AND Position = "Barangay";
            ');
            $query->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
            $query->bindParam(":username", $emailUsername, PDO::PARAM_STR);
            $query->bindParam(":password", $password, PDO::PARAM_STR);
            $query->execute();
    
            if ($query->rowCount() != 0) {
                $isResponder = true;
                $isBarangayPosition = true;
            }
        }
    
        $rowCount = $query->rowCount();
    
        if ($rowCount === 0) {
            sendResponse(401, false, "Username/Email Address or Password does not exist");
        }
    
        $userData = $query->fetch(PDO::FETCH_ASSOC);
    
        if (isset($userData['is_verified']) && $userData['is_verified'] == 0) {
            sendResponse(403, false, "Your email is not verified. Please verify your email before logging in.");
        }
    
        $loginArray = [];
    
        $currentRowData = [
            "email" => $userData["Email"],
            "fullName" => $userData["Fullname"] ?? null,
            "username" => $userData["Username"],
            "isResponder" => $isResponder,
            "isBarangayPosition" => $isBarangayPosition,
            "position" => $userData["Position"] ?? null,
            "address" => $userData["Address"] ?? null,
            "logo" => $userData["Logo"] ?? null,
        ];
    
        $loginArray[] = $currentRowData;
    
        $returnData = [];
        $returnData["rows_returned"] = $rowCount;
        $returnData["login"] = $loginArray;
        sendResponse(200, true, "Account successfully logged-in", $returnData, false);

        } elseif ($mode == "fetch_announcements") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";

            $domain = $_SERVER["HTTP_HOST"];

            $query = $readDB->prepare(
                "SELECT ID, Date, Subject, Message, Photo FROM c3_announcement"
            );
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $announcementsArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $link = $protocol . $domain . "/" . $row["Photo"];

                $announcementData = [
                    "id" => $row["ID"],
                    "date" => $row["Date"],
                    "subject" => $row["Subject"],
                    "message" => $row["Message"],
                    "photo" => $link,
                ];

                $announcementsArray[] = $announcementData;
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["announcements"] = $announcementsArray;
            sendResponse(
                200,
                true,
                "Announcements fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetch_ongoing") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";
            $domain = $_SERVER["HTTP_HOST"];

            date_default_timezone_set("Asia/Manila");
            $currentDate = date("Y-m-d");

            if (isset($jsonData->id)) {
                $query = $readDB->prepare("SELECT ID, Date, Caller, Location, Barangay, Involve, Status, resolved_time
                               FROM c3_locate
                               WHERE ID = :id 
                               AND Status != 'Resolved'");
                $query->bindParam(":id", $jsonData->id, PDO::PARAM_INT);
            } else {
                $query = $readDB->prepare("SELECT ID, Date, Caller, Location, Barangay, Involve, Status, resolved_time
                               FROM c3_locate
                               WHERE Status != 'Resolved' 
                               AND DATE(Date) = :currentDate");
                $query->bindParam(":currentDate", $currentDate, PDO::PARAM_STR);
            }

            $query->execute();
            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $ongoingArray = [];

            while ($c3Row = $query->fetch(PDO::FETCH_ASSOC)) {
                $ongoingID = $c3Row["ID"];

                $respondQuery = $readDB->prepare("
            SELECT 
                mr.TruckID, 
                bp.UnitName AS TruckUnitName, 
                mr.RespondersBarangay, 
                mr.TimeRespond, 
                mr.RespondStatus, 
                mr.TotalResponseTime, 
                mr.TimeArrived
            FROM 
                mobile_respond mr
            LEFT JOIN 
                brgy_profile bp 
            ON 
                mr.TruckID = bp.ID
            WHERE 
                mr.OngoingID = :ongoingID
        ");
                $respondQuery->bindParam(
                    ":ongoingID",
                    $ongoingID,
                    PDO::PARAM_INT
                );
                $respondQuery->execute();

                $respondingUnits = [];
                while ($respondRow = $respondQuery->fetch(PDO::FETCH_ASSOC)) {
                    if (
                        $respondRow["RespondStatus"] === "Arrived" &&
                        empty($respondRow["TimeArrived"])
                    ) {
                        $respondRow["TimeArrived"] = date("Y-m-d H:i:s");
                        $updateArrivedTimeQuery = $readDB->prepare("
                    UPDATE mobile_respond 
                    SET TimeArrived = :timeArrived 
                    WHERE TruckID = :truckID AND OngoingID = :ongoingID
                ");
                        $updateArrivedTimeQuery->bindParam(
                            ":timeArrived",
                            $respondRow["TimeArrived"],
                            PDO::PARAM_STR
                        );
                        $updateArrivedTimeQuery->bindParam(
                            ":truckID",
                            $respondRow["TruckID"],
                            PDO::PARAM_INT
                        );
                        $updateArrivedTimeQuery->bindParam(
                            ":ongoingID",
                            $ongoingID,
                            PDO::PARAM_INT
                        );
                        $updateArrivedTimeQuery->execute();
                    }

                    $respondingUnits[] = [
                        "truckID" => $respondRow["TruckID"],
                        "truck_unit_name" => $respondRow["TruckUnitName"],
                        "responders_barangay" =>
                            $respondRow["RespondersBarangay"],
                        "time_respond" => $respondRow["TimeRespond"],
                        "respond_status" => $respondRow["RespondStatus"],
                        "total_response_time" =>
                            $respondRow["TotalResponseTime"],
                        "time_arrived" => $respondRow["TimeArrived"],
                    ];
                }

                $ongoingArray[] = [
                    "id" => $c3Row["ID"],
                    "date" => $c3Row["Date"],
                    "caller" => $c3Row["Caller"],
                    "location" => $c3Row["Location"],
                    "barangay" => $c3Row["Barangay"],
                    "involve" => $c3Row["Involve"],
                    "status" => $c3Row["Status"],
                    "resolved_time" => $c3Row["resolved_time"],
                    "respondingUnit" => $respondingUnits,
                ];
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["ongoings"] = $ongoingArray;

            sendResponse(
                200,
                true,
                "C3 Locate fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetch_contact") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";

            $domain = $_SERVER["HTTP_HOST"];

            $query = $readDB->prepare(
                "SELECT ID, Contact_name, Contact_number FROM c3_contactinfo"
            );
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $contactArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $contactArray = [
                    "id" => $row["ID"],
                    "contact_name" => $row["Contact_name"],
                    "contact_number" => $row["Contact_number"],
                ];

                $contacttArray[] = $contactArray;
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["contacts"] = $contacttArray;
            sendResponse(
                200,
                true,
                "C3 Contact fetched successfully",
                $returnData,
                false
            );
} elseif ($mode == "fetch_account") {
    $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";

    $domain = $_SERVER["HTTP_HOST"];

    if (isset($jsonData->username)) {
        $query = $readDB->prepare(
            "SELECT ID, Barangay, Email, Fullname, Username, Password, Profile FROM pasigresident WHERE Username = :username"
        );
        $query->bindParam(":username", $jsonData->username, PDO::PARAM_STR);
    } else {
        $query = $readDB->prepare(
            "SELECT ID, Barangay, Email, Fullname, Username, Password, Profile FROM pasigresident"
        );
    }

    $query->execute();
    $rowCount = $query->rowCount();

    if ($rowCount === 0) {
        $responseMessage = isset($jsonData->username)
            ? "No account found for the given username"
            : "No accounts found";
        sendResponse(404, false, $responseMessage);
    }

    $contactArray = [];

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $profileData = null;

        if (!empty($row["Profile"])) {
            if (strpos($row["Profile"], 'data:image/') === 0) {
                $profileData = $row["Profile"];
            } else {
                $profilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $row["Profile"];

                if (file_exists($profilePath)) {
                    $profileData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($profilePath));
                } else {
                    error_log("Profile file not found at: " . $profilePath);
                }
            }
        }

        $contactArray[] = [
            "id" => $row["ID"],
            "barangay" => $row["Barangay"],
            "email" => $row["Email"],
            "fullname" => $row["Fullname"],
            "username" => $row["Username"],
            "password" => $row["Password"],
            "profile" => $profileData,
        ];
    }

    $returnData = [];
    $returnData["rows_returned"] = $rowCount;
    $returnData["contacts"] = $contactArray;
    sendResponse(
        200,
        true,
        "Account fetched successfully",
        $returnData,
        false
    );

        } elseif ($mode == "fetch_fireresponderacc") {
            $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                        $_SERVER["SERVER_PORT"] == 443
                            ? "https://"
                            : "http://";
        
            $domain = $_SERVER["HTTP_HOST"];
        
            if (isset($jsonData->username)) {
                $query = $readDB->prepare(
                    "SELECT ID, Barangay, Name, Username, EmailAddress, Password, ContactNumber, Birthday, Address, Profile FROM firerespondersaccount WHERE Username = :username"
                );
                $query->bindParam(":username", $jsonData->username, PDO::PARAM_STR);
            } else {
                $query = $readDB->prepare(
                    "SELECT ID, Barangay, Name, Username, EmailAddress, Password, ContactNumber, Birthday, Address, Profile FROM firerespondersaccount"
                );
            }
        
            $query->execute();
            $rowCount = $query->rowCount();
        
            if ($rowCount === 0) {
                $responseMessage = isset($jsonData->username)
                    ? "No account found for the given username"
                    : "No accounts found";
                sendResponse(404, false, $responseMessage);
            }
        
            $responderArray = [];
        
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $profileData = null;
        
                if (!empty($row["Profile"]) && strpos($row["Profile"], 'data:image/') === 0) {
                    $profileData = $row["Profile"];
                } else {
                    $profilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $row["Profile"];
                    if (file_exists($profilePath)) {
                        $profileData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($profilePath));
                    }
                }
        
                $responderArray[] = [
                    "id" => $row["ID"],
                    "barangay" => $row["Barangay"],
                    "name" => $row["Name"],
                    "username" => $row["Username"],
                    "emailAddress" => $row["EmailAddress"],
                    "password" => $row["Password"],
                    "contactNumber" => $row["ContactNumber"],
                    "birthday" => $row["Birthday"],
                    "address" => $row["Address"],
                    "profile" => $profileData,
                ];
            }
        
            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["responders"] = $responderArray;
            sendResponse(
                200,
                true,
                "Account fetched successfully",
                $returnData,
                false
            );
        



        } elseif ($mode == "update_fireresponderacc") {
            if (isset($jsonData->username) && isset($jsonData->updates)) {
        
                $sql = "UPDATE firerespondersaccount SET ";
                $params = [];
        
                // Update Barangay
                if (isset($jsonData->updates->barangay)) {
                    $sql .= "Barangay = COALESCE(:barangay, Barangay), ";
                    $params[':barangay'] = $jsonData->updates->barangay;
                }
        
                // Update Name
                if (isset($jsonData->updates->name)) {
                    $sql .= "Name = COALESCE(:name, Name), ";
                    $params[':name'] = $jsonData->updates->name;
                }
        
                // Update Email Address
                if (isset($jsonData->updates->emailAddress)) {
                    $sql .= "EmailAddress = COALESCE(:emailAddress, EmailAddress), ";
                    $params[':emailAddress'] = $jsonData->updates->emailAddress;
                }
        
                // Update Password
                if (isset($jsonData->updates->password)) {
                    $sql .= "Password = COALESCE(:password, Password), ";
                    $params[':password'] = $jsonData->updates->password;
                }
        
                // Update Contact Number
                if (isset($jsonData->updates->contactNumber)) {
                    $sql .= "ContactNumber = COALESCE(:contactNumber, ContactNumber), ";
                    $params[':contactNumber'] = $jsonData->updates->contactNumber;
                }
        
                // Update Birthday
                if (isset($jsonData->updates->birthday)) {
                    $sql .= "Birthday = COALESCE(:birthday, Birthday), ";
                    $params[':birthday'] = $jsonData->updates->birthday;
                }
        
                // Update Address
                if (isset($jsonData->updates->address)) {
                    $sql .= "Address = COALESCE(:address, Address), ";
                    $params[':address'] = $jsonData->updates->address;
                }
        
                // Update Profile (base64 encoded)
                if (isset($jsonData->updates->profile)) {
                    $sql .= "Profile = :profile, ";
                    $params[':profile'] = $jsonData->updates->profile;
                    // Debug the base64 string
                    error_log("Base64 Profile Data: " . $jsonData->updates->profile);
                }
        
                // Remove trailing comma from the SQL query
                $sql = rtrim($sql, ", ");
        
                // Add the WHERE condition
                $sql .= " WHERE Username = :username";
                $params[':username'] = $jsonData->username;
        
                // Prepare the query
                $query = $readDB->prepare($sql);
        
                // Bind parameters
                foreach ($params as $key => $value) {
                    $query->bindValue($key, $value, PDO::PARAM_STR);
                }
        
                // Execute the query and handle response
                if ($query->execute()) {
                    if ($query->rowCount() > 0) {
                        sendResponse(200, true, "Account updated successfully");
                    } else {
                        sendResponse(200, true, "No changes detected");
                    }
                } else {
                    sendResponse(500, false, "Error updating account: " . implode(", ", $query->errorInfo()));
                }
            } else {
                sendResponse(400, false, "Invalid request: Missing parameters");
            }

} elseif ($mode == "update_pasigresident") {
    if (isset($jsonData->username) && isset($jsonData->updates)) {
        // Step 1: Fetch existing data
        $selectSQL = "SELECT * FROM pasigresident WHERE Username = :username";
        $selectQuery = $readDB->prepare($selectSQL);
        $selectQuery->bindValue(':username', $jsonData->username, PDO::PARAM_STR);
        $selectQuery->execute();

        // Check if the user exists
        if ($selectQuery->rowCount() === 0) {
            sendResponse(404, false, "No account found with the given username");
            exit();
        }

        $existingData = $selectQuery->fetch(PDO::FETCH_ASSOC);

        // Step 2: Initialize the SQL and parameters
        $sql = "UPDATE pasigresident SET ";
        $params = [];
        $fieldsToUpdate = 0;

        // Step 3: Check and compare each field
        if (isset($jsonData->updates->barangay) && $jsonData->updates->barangay !== $existingData['Barangay']) {
            $sql .= "Barangay = :barangay, ";
            $params[':barangay'] = $jsonData->updates->barangay;
            $fieldsToUpdate++;
        }

        if (isset($jsonData->updates->fullname) && $jsonData->updates->fullname !== $existingData['Fullname']) {
            $sql .= "Fullname = :fullname, ";
            $params[':fullname'] = $jsonData->updates->fullname;
            $fieldsToUpdate++;
        }

        if (isset($jsonData->updates->email) && $jsonData->updates->email !== $existingData['Email']) {
            $sql .= "Email = :email, ";
            $params[':email'] = $jsonData->updates->email;
            $fieldsToUpdate++;
        }

        if (isset($jsonData->updates->password) && $jsonData->updates->password !== $existingData['Password']) {
            $sql .= "Password = :password, ";
            $params[':password'] = $jsonData->updates->password;
            $fieldsToUpdate++;
        }

        if (isset($jsonData->updates->contactNumber) && $jsonData->updates->contactNumber !== $existingData['ContactNumber']) {
            $sql .= "ContactNumber = :contactNumber, ";
            $params[':contactNumber'] = $jsonData->updates->contactNumber;
            $fieldsToUpdate++;
        }

        if (isset($jsonData->updates->birthday) && $jsonData->updates->birthday !== $existingData['Birthday']) {
            $sql .= "Birthday = :birthday, ";
            $params[':birthday'] = $jsonData->updates->birthday;
            $fieldsToUpdate++;
        }

        if (isset($jsonData->updates->profile) && $jsonData->updates->profile !== $existingData['Profile']) {
            if (preg_match('/^data:image\/[a-z]+;base64,/', $jsonData->updates->profile)) {
                $sql .= "Profile = :profile, ";
                $params[':profile'] = $jsonData->updates->profile;
                $fieldsToUpdate++;
            } else {
                sendResponse(400, false, "Invalid profile format");
                exit();
            }
        }

        // Step 4: Check if no fields need updating
        if ($fieldsToUpdate === 0) {
            sendResponse(200, true, "No changes made");
            exit();
        }

        // Step 5: Finalize the SQL statement
        $sql = rtrim($sql, ", ") . " WHERE Username = :username";
        $params[':username'] = $jsonData->username;

        // Step 6: Prepare and execute the query
        $query = $readDB->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === ':profile') {
                $query->bindValue($key, $value, PDO::PARAM_LOB); // Use LOB for large profile data
            } else {
                $query->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        if ($query->execute()) {
            if ($query->rowCount() > 0) {
                sendResponse(200, true, "Account updated successfully");
            } else {
                sendResponse(200, true, "No changes made");
            }
        } else {
            sendResponse(500, false, "Error updating account: " . $query->errorInfo()[2]);
        }
    } else {
        sendResponse(400, false, "Invalid request: Missing parameters");
    }




            date_default_timezone_set("Asia/Manila");
} elseif ($mode == "mobile_locate") {
    if (
        !isset($jsonData->date) ||
        !isset($jsonData->caller) ||
        !isset($jsonData->location) ||
        !isset($jsonData->longitude) ||
        !isset($jsonData->latitude) ||
        !isset($jsonData->barangay) ||
        !isset($jsonData->involve) ||
        !isset($jsonData->status)
    ) {
        sendResponse(400, false, "Incomplete Request");
        return;
    }

    $date = $jsonData->date;
    $caller = $jsonData->caller;
    $location = $jsonData->location;
    $longitude = $jsonData->longitude;
    $latitude = $jsonData->latitude;
    $barangay = $jsonData->barangay;
    $involve = $jsonData->involve;
    $status = $jsonData->status;
    $photo = $jsonData->photo;

    if ($date == "" || $caller == "" || $location == "" || $longitude == "" || $latitude == "" || $barangay == "" || $involve == "" || $status == "") {
        sendResponse(400, false, "All fields are required.");
        return;
    }

    $utcDateTime = new DateTime($date, new DateTimeZone("UTC"));
    $utcDateTime->setTimezone(new DateTimeZone("Asia/Manila"));
    $formattedDateTime = $utcDateTime->format("Y-m-d H:i:s");
    $formattedDate = $utcDateTime->format("Y-m-d");

    $checkQueryMobileLocate = $writeDB->prepare(
        "SELECT COUNT(*) as count FROM mobilelocate WHERE Location = :location AND DATE(Date) = :date"
    );
    $checkQueryMobileLocate->bindParam(":location", $location, PDO::PARAM_STR);
    $checkQueryMobileLocate->bindParam(":date", $formattedDate, PDO::PARAM_STR);
    $checkQueryMobileLocate->execute();
    
    $existingCountMobileLocate = $checkQueryMobileLocate->fetch(PDO::FETCH_ASSOC)['count'];
    
    $checkQueryC3Locate = $writeDB->prepare(
        "SELECT COUNT(*) as count FROM c3_locate WHERE Location = :location AND DATE(Date) = :date"
    );
    $checkQueryC3Locate->bindParam(":location", $location, PDO::PARAM_STR);
    $checkQueryC3Locate->bindParam(":date", $formattedDate, PDO::PARAM_STR);
    $checkQueryC3Locate->execute();
    
    $existingCountC3Locate = $checkQueryC3Locate->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($existingCountMobileLocate > 0 || $existingCountC3Locate > 0) {
        sendResponse(409, false, "This location has already been reported today.");
        return;
    }
    
    $insertQuery = $writeDB->prepare(
        "INSERT INTO mobilelocate (Date, Caller, Location, Longitude, Latitude, Barangay, Photo, Involve, Status) VALUES (:date, :caller, :location, :longitude, :latitude, :barangay, :photo, :involve, :status)"
    );
    $insertQuery->bindParam(":date", $formattedDateTime, PDO::PARAM_STR);
    $insertQuery->bindParam(":caller", $caller, PDO::PARAM_STR);
    $insertQuery->bindParam(":location", $location, PDO::PARAM_STR);
    $insertQuery->bindParam(":longitude", $longitude, PDO::PARAM_STR);
    $insertQuery->bindParam(":latitude", $latitude, PDO::PARAM_STR);
    $insertQuery->bindParam(":barangay", $barangay, PDO::PARAM_STR);
    $insertQuery->bindParam(":involve", $involve, PDO::PARAM_STR);
    $insertQuery->bindParam(":status", $status, PDO::PARAM_STR);
    $insertQuery->bindParam(":photo", $photo, PDO::PARAM_STR);
    
    $insertQuery->execute();
    
    $rowCount = $insertQuery->rowCount();
    
    if ($rowCount === 0) {
        sendResponse(500, false, "There was an error registering the report.");
        return;
    }
    
    sendResponse(201, true, "Your report has been successfully sent.");
        } elseif ($mode == "mobile_respond") {
            if (
                !isset($jsonData->username) ||
                !isset($jsonData->location) ||
                !isset($jsonData->longitude) ||
                !isset($jsonData->latitude) ||
                !isset($jsonData->timeRespond) ||
                !isset($jsonData->respondStatus) ||
                !isset($jsonData->ongoingID) ||
                !isset($jsonData->truckID)
            ) {
                sendResponse(400, false, "Incomplete Request");
            }

            $username = $jsonData->username;
            $location = $jsonData->location;
            $longitude = $jsonData->longitude;
            $latitude = $jsonData->latitude;
            $timeRespond = $jsonData->timeRespond;
            $respondStatus = $jsonData->respondStatus;
            $ongoingID = $jsonData->ongoingID;
            $truckID = $jsonData->truckID;

            if (isset($jsonData->timeArrived) && $respondStatus === "Arrived") {
                $timeArrived = $jsonData->timeArrived;

                $utcDateTimeArrived = new DateTime(
                    $timeArrived,
                    new DateTimeZone("UTC")
                );
                $utcDateTimeArrived->setTimezone(
                    new DateTimeZone("Asia/Manila")
                );
                $formattedArrivedDate = $utcDateTimeArrived->format(
                    "Y-m-d H:i:s"
                );
            } else {
                $formattedArrivedDate = "0000-00-00 00:00:00";
            }

            $barangayQuery = $writeDB->prepare(
                "SELECT Barangay FROM firerespondersaccount WHERE Username = :username"
            );
            $barangayQuery->bindParam(":username", $username, PDO::PARAM_STR);
            $barangayQuery->execute();

            if ($barangayQuery->rowCount() > 0) {
                $barangayResult = $barangayQuery->fetch(PDO::FETCH_ASSOC);
                $respondersBarangay = $barangayResult["Barangay"];
            } else {
                sendResponse(200, true);
            }

            $pht = new DateTimeZone("Asia/Manila");
            $utcDateTime = new DateTime($timeRespond, new DateTimeZone("UTC"));
            $utcDateTime->setTimezone($pht);
            $formattedDate = $utcDateTime->format("Y-m-d H:i:s");

            if ($formattedArrivedDate !== "0000-00-00 00:00:00") {
                $utcDateTimeArrived = new DateTime(
                    $formattedArrivedDate,
                    new DateTimeZone("Asia/Manila")
                );
                $interval = $utcDateTime->diff($utcDateTimeArrived);
                $totalMinutes =
                    $interval->days * 24 * 60 +
                    $interval->h * 60 +
                    $interval->i;
                $totalSeconds = $interval->s;
                $totalResponseTime = sprintf(
                    "%d:%02d",
                    $totalMinutes,
                    $totalSeconds
                );
            } else {
                $totalResponseTime = "0:00";
            }

            if ($username == "") {
                sendResponse(400, false, "Username is required");
            }
            if ($location == "") {
                sendResponse(400, false, "Location is required");
            }

            $checkQuery = $writeDB->prepare(
                "SELECT * FROM mobile_respond WHERE OngoingID = :ongoingID AND Username = :username"
            );
            $checkQuery->bindParam(":ongoingID", $ongoingID, PDO::PARAM_STR);
            $checkQuery->bindParam(":username", $username, PDO::PARAM_STR);
            $checkQuery->execute();

            if ($checkQuery->rowCount() > 0) {
                $updateQuery = $writeDB->prepare("
            UPDATE mobile_respond 
            SET Location = :location, Longitude = :longitude, Latitude = :latitude, 
                 RespondStatus = :respondStatus, 
                TimeArrived = :timeArrived, RespondersBarangay = :respondersBarangay, 
                TruckID = :truckID, TotalResponseTime = :totalResponseTime
            WHERE OngoingID = :ongoingID AND Username = :username
        ");
                $updateQuery->bindParam(":location", $location, PDO::PARAM_STR);
                $updateQuery->bindParam(
                    ":longitude",
                    $longitude,
                    PDO::PARAM_STR
                );
                $updateQuery->bindParam(":latitude", $latitude, PDO::PARAM_STR);
                $updateQuery->bindParam(
                    ":respondStatus",
                    $respondStatus,
                    PDO::PARAM_STR
                );
                $updateQuery->bindParam(
                    ":timeArrived",
                    $formattedArrivedDate,
                    PDO::PARAM_STR
                );
                $updateQuery->bindParam(
                    ":ongoingID",
                    $ongoingID,
                    PDO::PARAM_STR
                );
                $updateQuery->bindParam(":username", $username, PDO::PARAM_STR);
                $updateQuery->bindParam(
                    ":respondersBarangay",
                    $respondersBarangay,
                    PDO::PARAM_STR
                );
                $updateQuery->bindParam(":truckID", $truckID, PDO::PARAM_STR);
                $updateQuery->bindParam(
                    ":totalResponseTime",
                    $totalResponseTime,
                    PDO::PARAM_STR
                );
                $updateQuery->execute();

                if ($updateQuery->rowCount() === 0) {
                    sendResponse(500, false, "Update failed.");
                }

                sendResponse(200, true, "Update successful.");
            } else {
                $insertQuery = $writeDB->prepare("
            INSERT INTO mobile_respond (Username, Location, Longitude, Latitude, TimeRespond, RespondStatus, TimeArrived, OngoingID, RespondersBarangay, TruckID, TotalResponseTime) 
            VALUES (:username, :location, :longitude, :latitude, :timeRespond, :respondStatus, :timeArrived, :ongoingID, :respondersBarangay, :truckID, :totalResponseTime)
        ");

                $insertQuery->bindParam(":username", $username, PDO::PARAM_STR);
                $insertQuery->bindParam(":location", $location, PDO::PARAM_STR);
                $insertQuery->bindParam(
                    ":longitude",
                    $longitude,
                    PDO::PARAM_STR
                );
                $insertQuery->bindParam(":latitude", $latitude, PDO::PARAM_STR);
                $insertQuery->bindParam(
                    ":timeRespond",
                    $formattedDate,
                    PDO::PARAM_STR
                );
                $insertQuery->bindParam(
                    ":respondStatus",
                    $respondStatus,
                    PDO::PARAM_STR
                );
                $insertQuery->bindParam(
                    ":timeArrived",
                    $formattedArrivedDate,
                    PDO::PARAM_STR
                );
                $insertQuery->bindParam(
                    ":ongoingID",
                    $ongoingID,
                    PDO::PARAM_STR
                );
                $insertQuery->bindParam(
                    ":respondersBarangay",
                    $respondersBarangay,
                    PDO::PARAM_STR
                );
                $insertQuery->bindParam(":truckID", $truckID, PDO::PARAM_STR);
                $insertQuery->bindParam(
                    ":totalResponseTime",
                    $totalResponseTime,
                    PDO::PARAM_STR
                );
                $insertQuery->execute();

                if ($insertQuery->rowCount() === 0) {
                    sendResponse(500, false, "There was an error.");
                }

                sendResponse(201, true, "Insert successful.");
            }
        } elseif ($mode == "fetch_mobile_respond") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";
            $domain = $_SERVER["HTTP_HOST"];

            if (isset($jsonData->ongoingID)) {
                $query = $readDB->prepare(
                    "SELECT ID, Username, Location, Longitude, Latitude, TimeRespond, RespondStatus, TimeArrived, OngoingID, TruckID, TotalResponseTime FROM mobile_respond WHERE OngoingID = :ongoingID"
                );
                $query->bindParam(
                    ":ongoingID",
                    $jsonData->ongoingID,
                    PDO::PARAM_INT
                );
            } else {
                $query = $readDB->prepare(
                    "SELECT ID, Username, Location, Longitude, Latitude, TimeRespond, RespondStatus, TimeArrived, OngoingID, TruckID, TotalResponseTime FROM mobile_respond"
                );
            }

            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $responseMessage = isset($jsonData->ongoingID)
                    ? "No Contact found for the given ongoingID"
                    : "No Contacts found";
                sendResponse(404, false, $responseMessage);
            }

            $mobileResArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $totalResponseTime = $row["TotalResponseTime"];

                if (
                    empty($totalResponseTime) &&
                    !empty($row["TimeRespond"]) &&
                    !empty($row["TimeArrived"])
                ) {
                    $timeRespond = new DateTime(
                        $row["TimeRespond"],
                        new DateTimeZone("Asia/Manila")
                    );
                    $timeArrived = new DateTime(
                        $row["TimeArrived"],
                        new DateTimeZone("Asia/Manila")
                    );

                    $interval = $timeRespond->diff($timeArrived);
                    $totalMinutes =
                        $interval->days * 24 * 60 +
                        $interval->h * 60 +
                        $interval->i;
                    $totalSeconds = $interval->s;
                    $totalResponseTime = sprintf(
                        "%d:%02d",
                        $totalMinutes,
                        $totalSeconds
                    );
                }

                $mobileResArray[] = [
                    "ID" => $row["ID"],
                    "username" => $row["Username"],
                    "location" => $row["Location"],
                    "longitude" => $row["Longitude"],
                    "latitude" => $row["Latitude"],
                    "timeRespond" => $row["TimeRespond"],
                    "respondStatus" => $row["RespondStatus"],
                    "timeArrived" => $row["TimeArrived"],
                    "ongoingID" => $row["OngoingID"],
                    "truckID" => $row["TruckID"],
                    "totalResponseTime" => $totalResponseTime,
                ];
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["mobileresponds"] = $mobileResArray;
            sendResponse(
                200,
                true,
                "Mobile Respond fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetch_ongoing_completed") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";

            $domain = $_SERVER["HTTP_HOST"];

            $query = $readDB->prepare(
                "SELECT ID, Date, Caller, Location, Barangay, Involve, Status, resolved_time FROM c3_locate WHERE Status = 'Resolved'"
            );
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $ongoingArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $ongoingArray = [
                    "id" => $row["ID"],
                    "date" => $row["Date"],
                    "caller" => $row["Caller"],
                    "location" => $row["Location"],
                    "barangay" => $row["Barangay"],
                    "involve" => $row["Involve"],
                    "status" => $row["Status"],
                    "resolved_time" => $row["resolved_time"],
                ];

                $ongoinggArray[] = $ongoingArray;
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["ongoings"] = $ongoinggArray;
            sendResponse(
                200,
                true,
                "C3 Locate fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "check_mobile_respond_ongoing") {
            if (!isset($jsonData->username)) {
                sendResponse(
                    400,
                    false,
                    "Incomplete Request: Username is required"
                );
            }

            $username = $jsonData->username;

            if ($username == "") {
                sendResponse(400, false, "Username is required");
            }

            $query = $readDB->prepare("
                        SELECT 
                            mobile_respond.Username, 
                            mobile_respond.TimeRespond, 
                            mobile_respond.RespondStatus, 
                            mobile_respond.TimeArrived, 
                            mobile_respond.OngoingID,
                            c3_locate.Status
                        FROM mobile_respond
                        
                        JOIN c3_locate ON mobile_respond.OngoingID = c3_locate.ID
                            WHERE mobile_respond.Username = :username 
                            AND c3_locate.Status != 'Resolved'
                        ");

            $query->bindParam(":username", $username, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $ongoingArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $ongoingArray = [
                    "username" => $row["Username"],
                    "ongoingID" => $row["OngoingID"],
                    "status" => $row["Status"],
                ];

                $ongoingsArray[] = $ongoingArray;
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["ongoings"] = $ongoingsArray;

            sendResponse(
                200,
                true,
                "Ongoing responses fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetch_status") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";

            $domain = $_SERVER["HTTP_HOST"];

            $query = $readDB->prepare("SELECT ID, Color, Name FROM c3_status");
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $statusArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $statusArray = [
                    "id" => $row["ID"],
                    "name" => $row["Name"],
                    "color" => $row["Color"],
                ];

                $statussArray[] = $statusArray;
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["statuss"] = $statussArray;
            sendResponse(
                200,
                true,
                "Status  fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetch_involve") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";
            $domain = $_SERVER["HTTP_HOST"];

            $query = $readDB->prepare("SELECT ID, Involve FROM c3_involve");
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $involveArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $involveArray[] = [
                    "id" => $row["ID"],
                    "involve" => $row["Involve"],
                ];
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["involvements"] = $involveArray;
            sendResponse(
                200,
                true,
                "Involve records fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetch_truck") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";
            $domain = $_SERVER["HTTP_HOST"];

            if (!isset($jsonData->barangay)) {
                sendResponse(400, false, "Barangay is required");
            }

            $barangay = $jsonData->barangay;

            $query = $readDB->prepare("
                    SELECT bp.ID, bp.Barangay, bp.Photo, bp.UnitName, bp.PlateNumber, bp.TypeOfTruck, bp.Status, bp.Availability
                    FROM brgy_profile bp
                    LEFT JOIN mobile_respond mr ON bp.ID = mr.TruckID
                    LEFT JOIN c3_locate cl ON mr.OngoingID = cl.ID
                    WHERE bp.Barangay = :barangay 
                    AND bp.Status = 'Standby' 
                    AND bp.Availability = 'Serviceable'
                    AND (mr.TruckID IS NULL OR (cl.Status = 'Resolved' AND mr.TruckID IS NOT NULL))
                ");
            $query->bindParam(":barangay", $barangay, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $truckArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $displayName = $row["Barangay"] . ": " . $row["UnitName"];

                $truckArray[] = [
                    "id" => $row["ID"],
                    "barangay" => $row["Barangay"],
                    "photo" => $row["Photo"],
                    "unitName" => $row["UnitName"],
                    "plateNumber" => $row["PlateNumber"],
                    "typeOfTruck" => $row["TypeOfTruck"],
                    "status" => $row["Status"],
                    "displayName" => $displayName,
                ];
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["trucks"] = $truckArray;
            sendResponse(
                200,
                true,
                "Truck fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "Fire_Out") {
            if (
                !isset($jsonData->OngoingID) ||
                !isset($jsonData->username) ||
                !isset($jsonData->date)
            ) {
                sendResponse(400, false, "Incomplete Request");
            }

            $OngoingID = $jsonData->OngoingID;
            $username = $jsonData->username;
            $date = $jsonData->date;

            if ($OngoingID == "") {
                sendResponse(400, false, "OngoingID is required");
            }

            if ($username == "") {
                sendResponse(400, false, "Username is required");
            }

            if ($date == "") {
                sendResponse(400, false, "Date is required");
            }

            $query = $writeDB->prepare(
                "INSERT INTO brgy_fireout (OngoingID, Username, Date) VALUES (:OngoingID, :username, :date)"
            );
            $query->bindParam(":OngoingID", $OngoingID, PDO::PARAM_INT);
            $query->bindParam(":username", $username, PDO::PARAM_STR);
            $query->bindParam(":date", $date, PDO::PARAM_STR);

            date_default_timezone_set("Asia/Manila");
            $currentDateTime = date("Y-m-d H:i:s");

            $updateStatusQuery = $writeDB->prepare("UPDATE mobile_respond 
                                                       SET RespondStatus = 'Request for Fire Out', 
                                                           DateForRequest = :currentDateTime 
                                                       WHERE OngoingID = :OngoingID AND Username = :username");
            $updateStatusQuery->bindParam(
                ":currentDateTime",
                $currentDateTime,
                PDO::PARAM_STR
            );
            $updateStatusQuery->bindParam(
                ":OngoingID",
                $OngoingID,
                PDO::PARAM_INT
            );
            $updateStatusQuery->bindParam(
                ":username",
                $username,
                PDO::PARAM_STR
            );

            $updateStatusQuery->execute();
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(
                    500,
                    false,
                    "There was an error processing the request."
                );
            }

            sendResponse(201, true, "Your request has been successfully sent.");
        } elseif ($mode == "request") {
            if (
                !isset($jsonData->barangay) ||
                !isset($jsonData->username) ||
                !isset($jsonData->request) ||
                !isset($jsonData->ongoingID)
            ) {
                sendResponse(400, false, "Incomplete Request");
            }
        
            $barangay = $jsonData->barangay;
            $username = $jsonData->username;
            $request = $jsonData->request;
            $ongoingID = $jsonData->ongoingID;
            $date = date('Y-m-d H:i:s');
        
            if ($barangay == "") {
                sendResponse(400, false, "Barangay is required");
            }
        
            if ($username == "") {
                sendResponse(400, false, "Username is required");
            }
        
            if ($request == "") {
                sendResponse(400, false, "Request is required");
            }
        
            $query = $writeDB->prepare("INSERT INTO c3_request (ID, Barangay, Responder, Request, OngoingID, Date) 
                                           VALUES (:id, :barangay, :username, :request, :ongoingID, :date)");
            $query->bindParam(":id", $id, PDO::PARAM_INT);
            $query->bindParam(":barangay", $barangay, PDO::PARAM_STR);
            $query->bindParam(":username", $username, PDO::PARAM_STR);
            $query->bindParam(":request", $request, PDO::PARAM_STR);
            $query->bindParam(":ongoingID", $ongoingID, PDO::PARAM_STR);
            $query->bindParam(":date", $date, PDO::PARAM_STR);
        
            $query->execute();
        
            $rowCount = $query->rowCount();
        
            if ($rowCount === 0) {
                sendResponse(
                    500,
                    false,
                    "There was an error processing the request."
                );
            }
        
            sendResponse(201, true, "Your request has been successfully sent.");
        } elseif ($mode == "ReturnToBase") {
            if (!isset($jsonData->ongoingID) || !isset($jsonData->username)) {
                sendResponse(400, false, "Incomplete Request");
            }

            $ongoingID = $jsonData->ongoingID;
            $username = $jsonData->username;

            if ($ongoingID == "") {
                sendResponse(400, false, "Ongoing ID is required");
            }

            if ($username == "") {
                sendResponse(400, false, "Username is required");
            }

            $query = $writeDB->prepare(
                "DELETE FROM mobile_respond WHERE OngoingID = :ongoingID AND Username = :username"
            );
            $query->bindParam(":ongoingID", $ongoingID, PDO::PARAM_STR);
            $query->bindParam(":username", $username, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            sendResponse(200, true, "Responder data successfully deleted.");
        } elseif ($mode == "Cancel") {
            if (!isset($jsonData->ongoingID) || !isset($jsonData->username)) {
                sendResponse(400, false, "Incomplete Request");
            }

            $ongoingID = $jsonData->ongoingID;
            $username = $jsonData->username;

            if ($ongoingID == "" || $username == "") {
                sendResponse(
                    400,
                    false,
                    "Ongoing ID and Username are required"
                );
            }

            $query = $writeDB->prepare("SELECT Username, Location, Longitude, Latitude, TimeRespond, RespondStatus, 
                                       TimeArrived, OngoingID, RespondersBarangay, TruckID, TotalResponseTime
                                FROM mobile_respond WHERE OngoingID = :ongoingID AND Username = :username");
            $query->bindParam(":ongoingID", $ongoingID, PDO::PARAM_STR);
            $query->bindParam(":username", $username, PDO::PARAM_STR);
            $query->execute();

            $responseData = $query->fetch(PDO::FETCH_ASSOC);

            if (!$responseData) {
                sendResponse(400, false, "No data found for cancellation.");
            }

            $cancelQuery = $writeDB->prepare("INSERT INTO c3_cancel (Username, Location, Longitude, Latitude, TimeRespond, 
                                      RespondStatus, TimeArrived, OngoingID, RespondersBarangay, TruckID, TotalResponseTime)
                                      VALUES (:username, :location, :longitude, :latitude, :timeRespond, :respondStatus, 
                                              :timeArrived, :ongoingID, :respondersBarangay, :truckID, :totalResponseTime)");

            $cancelQuery->bindParam(
                ":username",
                $responseData["Username"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":location",
                $responseData["Location"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":longitude",
                $responseData["Longitude"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":latitude",
                $responseData["Latitude"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":timeRespond",
                $responseData["TimeRespond"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":respondStatus",
                $responseData["RespondStatus"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":timeArrived",
                $responseData["TimeArrived"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":ongoingID",
                $responseData["OngoingID"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":respondersBarangay",
                $responseData["RespondersBarangay"],
                PDO::PARAM_STR
            );
            $cancelQuery->bindParam(
                ":truckID",
                $responseData["TruckID"],
                PDO::PARAM_INT
            );
            $cancelQuery->bindParam(
                ":totalResponseTime",
                $responseData["TotalResponseTime"],
                PDO::PARAM_STR
            );

            $cancelQuery->execute();

            $deleteQuery = $writeDB->prepare(
                "DELETE FROM mobile_respond WHERE OngoingID = :ongoingID AND Username = :username"
            );
            $deleteQuery->bindParam(":ongoingID", $ongoingID, PDO::PARAM_STR);
            $deleteQuery->bindParam(":username", $username, PDO::PARAM_STR);
            $deleteQuery->execute();

            sendResponse(
                200,
                true,
                "Responder data successfully deleted and transferred to c3_cancel."
            );
        } elseif ($mode == "update_status") {
            if (!isset($jsonData->OngoingID) || !isset($jsonData->status)) {
                sendResponse(400, false, "Incomplete Request");
            }

            $ongoingID = $jsonData->OngoingID;
            $newStatus = $jsonData->status;

            date_default_timezone_set("Asia/Manila");
            $currentDateTime = date("Y-m-d H:i:s");

            $checkMatchingIDQuery = $writeDB->prepare("
                        SELECT c3_locate.ID 
                        FROM c3_locate
                        INNER JOIN mobile_respond ON mobile_respond.OngoingID = c3_locate.ID
                        WHERE c3_locate.ID = :ongoingID
                    ");
            $checkMatchingIDQuery->bindParam(
                ":ongoingID",
                $ongoingID,
                PDO::PARAM_INT
            );
            $checkMatchingIDQuery->execute();

            if ($checkMatchingIDQuery->rowCount() > 0) {
                $updateLocateQuery = $writeDB->prepare(
                    "UPDATE c3_locate SET Status = :status, DateStatus = :dateStatus WHERE ID = :ongoingID"
                );
                $updateLocateQuery->bindParam(
                    ":status",
                    $newStatus,
                    PDO::PARAM_STR
                );
                $updateLocateQuery->bindParam(
                    ":dateStatus",
                    $currentDateTime,
                    PDO::PARAM_STR
                );
                $updateLocateQuery->bindParam(
                    ":ongoingID",
                    $ongoingID,
                    PDO::PARAM_INT
                );
                $updateLocateQuery->execute();

                if ($updateLocateQuery->rowCount() > 0) {
                    sendResponse(
                        200,
                        true,
                        "Status updated successfully in c3_locate."
                    );
                } else {
                    sendResponse(
                        500,
                        false,
                        "Failed to update status in c3_locate."
                    );
                }
            } else {
                sendResponse(200, true);
            }
        } elseif ($mode == "fetch_brgyaccount") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";
            $domain = $_SERVER["HTTP_HOST"];

            $query = $readDB->prepare(
                "SELECT ID, Logo, Username, Email, Address, Position, Password FROM c3_addaccount"
            );
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $brgyArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $brgyArray[] = [
                    "id" => $row["ID"],
                    "logo" => $row["Logo"],
                    "username" => $row["Username"],
                    "email" => $row["Email"],
                    "address" => $row["Address"],
                    "position" => $row["Position"],
                    "password" => $row["Password"],
                ];
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["barangay"] = $brgyArray;
            sendResponse(
                200,
                true,
                "Barangay records fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetch_pasigresident") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";
            $domain = $_SERVER["HTTP_HOST"];

            $query = $readDB->prepare(
                "SELECT ID, Barangay, Profile, Email, FullName, Username, ContactNumber, Birthday FROM pasigresident"
            );
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $residentArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $residentArray[] = [
                    "id" => $row["ID"],
                    "barangay" => $row["Barangay"],
                    "profile" => $row["Profile"],
                    "email" => $row["Email"],
                    "fullName" => $row["FullName"],
                    "username" => $row["Username"],
                    "contactNumber" => $row["ContactNumber"],
                    "birthday" => $row["Birthday"],
                ];
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["residents"] = $residentArray;
            sendResponse(
                200,
                true,
                "Residents fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetchresponder") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";
            $domain = $_SERVER["HTTP_HOST"];

            if (!isset($jsonData->barangay)) {
                sendResponse(400, false, "Barangay is required");
            }

            $barangay = $jsonData->barangay;

            $query = $readDB->prepare(
                "SELECT ID, Barangay, Profile, Name, Username, EmailAddress, Password, ContactNumber, Birthday, Address, Role FROM firerespondersaccount WHERE Barangay = :barangay"
            );
            $query->bindParam(":barangay", $barangay, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $responderArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $checkQuery = $readDB->prepare(
                    "SELECT ResponderID FROM mobile_respond WHERE FIND_IN_SET(:responderID, ResponderID) > 0"
                );
                $checkQuery->bindParam(":responderID", $row["ID"]);
                $checkQuery->execute();
                $idExists = $checkQuery->fetchColumn();

                if (!$idExists) {
                    $responderArray[] = [
                        "id" => $row["ID"],
                        "barangay" => $row["Barangay"],
                        "profile" => $row["Profile"],
                        "name" => $row["Name"],
                        "username" => $row["Username"],
                        "emailAddress" => $row["EmailAddress"],
                        "contactNumber" => $row["ContactNumber"],
                        "birthday" => $row["Birthday"],
                        "address" => $row["Address"],
                        "role" => $row["Role"],
                    ];
                }
            }

            $returnData = [];
            $returnData["rows_returned"] = count($responderArray);
            $returnData["responders"] = $responderArray;
            sendResponse(
                200,
                true,
                "Responders fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "fetch_helpout") {
            $protocol =
                (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
                $_SERVER["SERVER_PORT"] == 443
                    ? "https://"
                    : "http://";
            $domain = $_SERVER["HTTP_HOST"];

            if (!isset($jsonData->barangay)) {
                sendResponse(400, false, "Barangay is required");
            }

            $barangay = $jsonData->barangay;

            $query = $readDB->prepare(
                "SELECT ID, Date, Location, Barangay, Involve, TypeOfTruck, Status FROM brgy_helpout WHERE Barangay = :barangay"
            );
            $query->bindParam(":barangay", $barangay, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }

            $helpOutArray = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $displayName = $row["Barangay"] . ": " . $row["TypeOfTruck"];

                $helpOutArray[] = [
                    "id" => $row["ID"],
                    "date" => $row["Date"],
                    "location" => $row["Location"],
                    "barangay" => $row["Barangay"],
                    "involve" => $row["Involve"],
                    "status" => $row["Status"],
                    "displayName" => $displayName,
                ];
            }

            $returnData = [];
            $returnData["rows_returned"] = $rowCount;
            $returnData["helpOuts"] = $helpOutArray;
            sendResponse(
                200,
                true,
                "HelpOut fetched successfully",
                $returnData,
                false
            );
        } elseif ($mode == "HelpOut_ReturnToBase") {
            if (!isset($jsonData->id)) {
                sendResponse(400, false, "ID is required");
            }

            $id = $jsonData->id;

            if ($id == "") {
                sendResponse(400, false, "ID cannot be empty");
            }

            $query = $writeDB->prepare(
                "DELETE FROM brgy_helpout WHERE ID = :id"
            );
            $query->bindParam(":id", $id, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                sendResponse(200, true);
            }
            sendResponse(200, true, "Responder data successfully deleted.");
        } elseif ($mode == "deleteAccount") {
            $emailUsername = $jsonData->emailUsername ?? null;
            $password = $jsonData->password ?? null;
        
            if (empty($emailUsername)) {
                sendResponse(400, false, "Please provide your Email or Username");
            }
        
            if (empty($password)) {
                sendResponse(400, false, "Please provide your Password");
            }
        
            $isResponder = false;
            $isBarangayPosition = false;
            $accountDeleted = false;
        
            $query = $writeDB->prepare('
                SELECT 
                    Name AS Fullname,
                    EmailAddress AS Email, 
                    Username
                FROM firerespondersaccount 
                WHERE EmailAddress = :emailAddress AND `Password` = :password;
            ');
            $query->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
            $query->bindParam(":password", $password, PDO::PARAM_STR);
            $query->execute();
        
            if ($query->rowCount() > 0) {
                $deleteQuery = $writeDB->prepare('
                    DELETE FROM firerespondersaccount 
                    WHERE EmailAddress = :emailAddress AND `Password` = :password;
                ');
                $deleteQuery->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
                $deleteQuery->bindParam(":password", $password, PDO::PARAM_STR);
                $deleteQuery->execute();
        
                if ($deleteQuery->rowCount() > 0) {
                    $accountDeleted = true;
                }
            }
        
            if (!$accountDeleted) {
                $query = $writeDB->prepare('
                    SELECT *
                    FROM pasigresident
                    WHERE (Email = :emailAddress OR Username = :username) AND `Password` = :password;
                ');
                $query->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
                $query->bindParam(":username", $emailUsername, PDO::PARAM_STR);
                $query->bindParam(":password", $password, PDO::PARAM_STR);
                $query->execute();
        
                if ($query->rowCount() > 0) {
                    $deleteQuery = $writeDB->prepare('
                        DELETE FROM pasigresident 
                        WHERE (Email = :emailAddress OR Username = :username) AND `Password` = :password;
                    ');
                    $deleteQuery->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
                    $deleteQuery->bindParam(":username", $emailUsername, PDO::PARAM_STR);
                    $deleteQuery->bindParam(":password", $password, PDO::PARAM_STR);
                    $deleteQuery->execute();
        
                    if ($deleteQuery->rowCount() > 0) {
                        $accountDeleted = true;
                    }
                }
            }
        
            if (!$accountDeleted) {
                $query = $writeDB->prepare('
                    SELECT 
                        ID,
                        Username,
                        Email
                    FROM c3_addaccount 
                    WHERE (Email = :emailAddress OR Username = :username) AND `Password` = :password AND Position = "Barangay";
                ');
                $query->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
                $query->bindParam(":username", $emailUsername, PDO::PARAM_STR);
                $query->bindParam(":password", $password, PDO::PARAM_STR);
                $query->execute();
        
                if ($query->rowCount() > 0) {
                    // Delete from `c3_addaccount`
                    $deleteQuery = $writeDB->prepare('
                        DELETE FROM c3_addaccount 
                        WHERE (Email = :emailAddress OR Username = :username) AND `Password` = :password AND Position = "Barangay";
                    ');
                    $deleteQuery->bindParam(":emailAddress", $emailUsername, PDO::PARAM_STR);
                    $deleteQuery->bindParam(":username", $emailUsername, PDO::PARAM_STR);
                    $deleteQuery->bindParam(":password", $password, PDO::PARAM_STR);
                    $deleteQuery->execute();
        
                    if ($deleteQuery->rowCount() > 0) {
                        $accountDeleted = true;
                    }
                }
            }
        
            if ($accountDeleted) {
                sendResponse(200, true, "Account successfully deleted");
        }
        
        
        } elseif ($mode == "addNotificationToken") {
                $username = $jsonData->username ?? null;
                $token = $jsonData->token ?? null;
            
                if (empty($username)) {
                    sendResponse(400, false, "Please provide a Username");
                }
            
                if (empty($token)) {
                    sendResponse(400, false, "Please provide a Token");
                }
            
                $query = $writeDB->prepare('SELECT ID FROM notif WHERE Token = :token');
                $query->bindParam(":token", $token, PDO::PARAM_STR);
                $query->execute();
            
                if ($query->rowCount() > 0) {
                    sendResponse(409, false, "This token is already registered.");
                }
            
                $insertQuery = $writeDB->prepare('
                    INSERT INTO notif (Username, Token) 
                    VALUES (:username, :token);
                ');
                $insertQuery->bindParam(":username", $username, PDO::PARAM_STR);
                $insertQuery->bindParam(":token", $token, PDO::PARAM_STR);
                $insertQuery->execute();
            
                $rowCount = $insertQuery->rowCount();
            
                if ($rowCount > 0) {
                    sendResponse(201, true, "Notification token added successfully.");
                } else {
                    sendResponse(500, false, "Failed to add notification token. Please try again.");
                }
        } elseif ($mode == "deleteNotificationToken") {
                $username = $jsonData->username ?? null;
                $token = $jsonData->token ?? null;
            
                if (empty($username)) {
                    sendResponse(400, false, "Please provide a Username");
                }
            
                if (empty($token)) {
                    sendResponse(400, false, "Please provide a Token");
                }
            
                $query = $writeDB->prepare('SELECT ID FROM notif WHERE Username = :username AND Token = :token');
                $query->bindParam(":username", $username, PDO::PARAM_STR);
                $query->bindParam(":token", $token, PDO::PARAM_STR);
                $query->execute();
            
                if ($query->rowCount() === 0) {
                    sendResponse(404, false, "Token not found for the specified user.");
                }
            
                $deleteQuery = $writeDB->prepare('DELETE FROM notif WHERE Username = :username AND Token = :token');
                $deleteQuery->bindParam(":username", $username, PDO::PARAM_STR);
                $deleteQuery->bindParam(":token", $token, PDO::PARAM_STR);
                $deleteQuery->execute();
            
                $rowCount = $deleteQuery->rowCount();
            
                if ($rowCount > 0) {
                    sendResponse(200, true, "Notification token deleted successfully.");
                } else {
                    sendResponse(500, false, "Failed to delete notification token. Please try again.");
                }

        
        } else {
            sendResponse(400, false, "Mode not found");
        }
    }
} else {
    sendResponse(404, false, "Endpoint not found");
}
?>
