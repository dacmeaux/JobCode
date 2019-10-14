<?php
    require_once 'app/class.BaseObj.php';

    class User extends BaseObj{
//        private $userID;
//        private $userDisplayName;
//        private $current_gallery;
//        private $galleries;

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
                'data'=>null,
                'current_gallery'=>null,
                'galleries'=>array()
            );
        }

    /**
     * Create a gallery
     *
     * @param string $displayname
     * @param int $appid
     * @return object|array
     * @access public
     * @since method available since release 1.0
     */
        public function create($displayname, $appid = 0){
            $db_handle = $this->dbh;

            $query = '
                INSERT INTO
                    Users
                (userDisplayName)
                VALUES (:displayname)';

            $result = executeQuery($query, array('displayname'=>$displayname));

            if( !$result instanceof PDOStatement )
                return $result;

            return $this->getUser($db_handle->lastInsertId());
        }

    /**
     * Delete a user
     *
     * @param int $id
     * @access public
     * @return array|User
     * @since method available since release 1.0
     */
    public function delete($id = 0){
        $db_handle = $this->dbh;

        $query = '
            Delete
            FROM
                Users
            WHERE
                userID = :id';

        $result = executeQuery($query, array('id'=>($id <= 0 ? $this->userID : $id)));

        if( !$result instanceof PDOStatement )
            return $result;

        // Delete dependencies
        echo 'User with ID '. $this->userID .' deleted';

        // Delete all User Galleries
        $user_galleries = $this->getUserGalleries();

        foreach( $user_galleries as $gallery_data_obj ) {
            $this->current_gallery->deleteGallery($gallery_data_obj->galleryID);
        }

        return $this;
    }

        /**
         * Get the user with ID
         *
         * @param integer id
         * @access public
         * @return array|User
         * @since method available since release 1.0
         */
        public function getUser($id = 0){
            $db_handle = $this->dbh;

            // This should very rarely if at all get used
            // Create a generic user
            if( $id === 0 ){
                $query = '
                    INSERT INTO
                        Users
                    (userDisplayName)
                    VALUES(:name)';

                $result = executeQuery($query, array('name'=>'new_user_'. mt_rand(0, 1000000)));

                if( !$result instanceof PDOStatement )
                    return $result;

                $id = $db_handle->lastInsertId();
            }

            $query = '
                Select
                    * 
                FROM 
                    Users 
                WHERE 
                    userID = :id';

            $result = executeQuery($query, array('id'=>$id));

            if( !$result instanceof PDOStatement )
                return $result;

            $user_data = $result->fetchAll(PDO::FETCH_ASSOC);
            // Save state
            $this->setState($user_data[0]);
            return $this;
        }

        /**
         * Add a gallery to this user
         *
         * @param string title
         * @access public
         * @return object
         * @since method available since release 1.0
         */
        public function addGallery($title){
            echo 'UserID at addGallery: '. $this->userID .':'. var_export($this->attrs, true);
            return $this->current_gallery->create($title, $this->userID);
        }

        /**
         * Set the current gallery for this user
         *
         * @param Gallery gallery_obj
         * @access public
         * @return void
         * @since method available since release 1.0
         */
        public function setGallery(Gallery $gallery_obj){
            $this->current_gallery = $gallery_obj;
        }

        /**
         * Get the current gallery for this user
         *
         * @access public
         * @return Gallery
         * @since method available since release 1.0
         */
        public function getGallery(){
            return $this->current_gallery;
        }

        public function deleteGallery()
        {
            return $this->current_gallery->delete();
        }

        /**
         * Upload a photo to this user
         *
         * @param Photo $photo_obj
         * @param array $data
         * @access public
         * @return Photo
         * @since method available since release 1.0
         */
        public function uploadPhoto($photo_obj, $data){
            return $photo_obj->uploadPhoto($data, $this->userID);
        }

        /**
         * Get all galleries for a user
         *
         * @param Gallery $gallery_obj
         * @access public
         * @return array
         * @since method available since release 1.0
         */
        public function getUserGalleries(){
            $this->galleries = $this->current_gallery->getUserGalleries($this->userID);

            return $this->galleries;
        }
    }