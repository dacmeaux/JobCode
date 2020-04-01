<?php

/**
 * Connect to the Database
 *
 * @return PDO|null
 */
function dbConnect()
{
    $connect_string = 'mysql:host=localhost;dbname=Photo_Gallery;charset=utf8';
    $db_obj = null;

    try {
        $db_obj = new PDO($connect_string, 'dacmeaux', 'Pat@hfi@nd36', array(PDO::ATTR_PERSISTENT => true));

        if( !$db_obj )
            throw new PDOException('Could not connect to database Photo_Gallery');
    }
    catch (PDOException $e) {
        echo $e->getMessage();
    }

    return $db_obj;
}

/**
 * Execute a query
 *
 * @param string $query
 * @param array $query_data
 * @return array|PDOStatement
 */
function executeQuery($query, $query_data)
{
    $dbh = dbConnect();

    $prep = $dbh->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $stmt = $prep->execute($query_data);

    if( !$stmt )
        return $prep->errorInfo();

    return $prep;
}