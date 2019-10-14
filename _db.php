<?php
    function db_getConnection($db, $usr, $pass = null, $driver = null)
    {
     // Grab a PDO connection using the specified driver...
     $connect_string = $driver_name.':host='.$host.';dbname='.$db.';charset=utf8';
     //error_log("Connecting with connect string: ".$connect_string);
     $limit = 10;
     while ($limit-- > 0)
             {
             try
                     {
                     $DB_obj = new PDO($connect_string, $user, $password, array(PDO::ATTR_PERSISTENT => true));
                     // If no exception is thrown, break to exit the while loop early...
                     break;
                     }
             catch (PDOException $e)
                     {
                     $DB_obj = null;
                     if ($limit == 0)
                             {
                             error_log("db_getConnection Error: " . $e->getMessage());
                             die();
                             }
                     }
             }

     return $DB_obj;
    }
?>