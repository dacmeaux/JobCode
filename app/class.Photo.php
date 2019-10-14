<?php
    require_once 'app/class.BaseObj.php';

    class Photo extends BaseObj {
//        private $photo_save_path = 'uploads/';

        /**
         * Constructor
         *
         * @access public
         * @return void
         * @since method available since release 1.0
         */
        public function __construct(){
            parent::__construct();
            
            $this->attrs = array(
                'data'=>null
            );
        }

        /**
         * Upload photo
         *
         * @param array $photo_data
         * @access public
         * @return array|Photo
         * @since method available since release 1.0
         */
        public function uploadPhoto($photo_data, $userid){
            $db_handle = $this->dbh;

//            $photo_save_path = $this->photo_save_path;
//            $photo_filename = basename($photo_data["name"]);
            $photo_filename = $photo_data["name"];
//            $photo_dest_path = $photo_save_path . $photo_filename;

//            if( !move_uploaded_file($photo_data["tmp_name"], $photo_dest_path) )
//                return false;

            $query = '
                INSERT INTO
                    Photos 
                (userID, photoTitle, photoFilename)
                VALUES (:userid, :title, :filename)';

            $result = executeQuery($query, array('userid'=>$userid, 'title'=>$photo_data['title'], 'filename'=>$photo_filename));

            if( !$result instanceof PDOStatement )
                return $result;

            return $this->getPhoto($db_handle->lastInsertId());
        }

        /**
         * Get a photo from the DB
         *
         * @param integer $id
         * @access public
         * @return array|Photo
         * @since method available since release 1.0
         */
        public function getPhoto($id){
            $db_handle = $this->dbh;

            $query = '
                Select
                    * 
                FROM 
                    Photos 
                WHERE 
                    photoID = :id
                ORDER BY 
                    photoID DESC';

            $result = executeQuery($query, array('id'=>$id));

            if( !$result instanceof PDOStatement )
                return $result;

            $photo_data = $result->fetchAll(PDO::FETCH_ASSOC);
            // Save state
            $this->setState($photo_data[0]);

            return $this;
        }

        /**
         * Add a photo to re Ref Type (gallery or album)
         *
         * @param string $reftype
         * @param integer $refid
         * @param int $photoid
         * @return Photo|array
         * @access public
         * @since method available since release 1.0
         */
        public function addPhotoTo($reftype, $refid, $photoid = 0) {
            $db_handle = $this->dbh;

            $query = '
                INSERT INTO
                    PhotosJoin 
                (photoID, refID, refType)
                VALUES (:photoid, :refid, :reftype)';

            $result = executeQuery($query, array('photoid'=>($photoid <= 0 ? $this->photoID : $photoid), 'refid'=>$refid, 'reftype'=>$reftype));

            if( !$result instanceof PDOStatement )
                return $result;

            return $this->getPhoto($this->photoID);
        }

        /**
         * Get photo from a ref type (gallery or album)
         *
         * @param string $reftype
         * @param integer $refid
         * @param int $photoid
         * @return array|Photo
         * @access public
         * @since method available since release 1.0
         */
        public function getPhotoFrom($reftype, $refid, $photoid = 0){
            $db_handle = $this->dbh;

            $query = '
                Select
                    photoID, 
                    photoTitle, 
                    photoFilename,
                    GROUP_CONCAT(reftype, refid) as parent
                FROM
                    Photos 
                    JOIN PhotosJoin     
                        ON PhotosJoin.photoID = Photos.photoID
                WHERE 
                    photoID = :photoid ';

            if( $reftype == 'all' )
                $query .= '';

            if( $refid > 0 && in_array($reftype, array('gallery', 'album')) )
                $query .= 'and PhotosJoin.reftype = :reftype and PhotosJoin.refid = :refid ';

            $query .= '
                GROUP BY
                    Photos.photoID';

            $result = executeQuery($query, array('photoid'=>($photoid <= 0 ? $this->photoID: $photoid), 'reftype'=>$reftype, 'refid'=>$refid));

            if( !$result instanceof PDOStatement )
                return $result;

            $photo_data = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->setState($photo_data[0]);

            return $this;
        }

        /**
         * Delete a phot from a ref type (gallery or album)
         *
         * @param string $reftype
         * @param integer $refid
         * @param int $photoid
         * @return array|Photo
         * @access public
         * @since method available since release 1.0
         */
        public function deletePhotoFrom($reftype, $refid, $photoid = 0){
            $db_handle = $this->dbh;
            
            $query = '
                DELETE
                FROM 
                    PhotosJoin 
                WHERE 
                    photoID = :photoid 
                    AND refType = :reftype 
                    AND refid = :refid';

            $result = executeQuery($query, array('photoid'=>($photoid <= 0 ? $this->photoID : $photoid), 'refid'=>$refid, 'reftype'=>$reftype));

            if( !$result instanceof PDOStatement )
                return $result;

            return $this;
        }

        /**
         * Get all photos for a ref type (gallery or album)
         *
         * @param string $reftype
         * @param integer $refid
         * @access public
         * @return array|Photo
         * @since method available since release 1.0
         */
        public function getRefPhotos($reftype, $refid){
            $db_handle = $this->dbh;

            $query = '
                Select
                    * 
                FROM 
                    PhotosJoin 
                WHERE 
                    refType = :reftype 
                    AND refID = :refid';

            $query_data = array('reftype'=>$reftype, 'refid'=>$refid);
            $prep = $db_handle->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $prep->execute($query_data);

            $result = executeQuery($query, array('reftype'=>$reftype, 'refid'=>$refid));

            if( !$result instanceof PDOStatement )
                return $result;

            $photos = $result->fetchAll(PDO::FETCH_OBJ);
            $this->setState(array('data'=>$photos));
            return $photos;
        }
    }


/**
 * Photo factory
 *
 * @param integer $id
 * @access public
 * @return Photo
 * @since method available since release 1.0
 */
function photoFactory($id = 0){
    $photo_obj = new Photo();

    if( $id > 0 )
        return $photo_obj->getPhoto($id);

    return $photo_obj;
}