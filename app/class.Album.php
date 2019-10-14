<?php
    require_once 'app/class.BaseObj.php';

    class Album extends BaseObj {

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
                'current_photo'=>null,
                'album_photos'=>array()
            );
        }

        /**
         * Create a new Album
         *
         * @param integer $galleryid
         * @param string $title
         * @access public
         * @return array|bool|Album
         * @since method available since release 1.0
         */
        public function create($title, $galleryid = 0){
            $db_handle = $this->dbh;

            $query = '
                INSERT INTO
                    Albums
                (galleryID, albumTitle)
                VALUES (:galleryid, :title)';

            $result = executeQuery($query, array('galleryid'=>$galleryid, 'title'=>$title));

            if( !$result instanceof PDOStatement )
                return $result;
            
            return $this->getAlbum($db_handle->lastInsertId());
        }

        /**
         * Get this album from the DB
         *
         * @param integer $id
         * @access public
         * @return array|Album
         * @since method available since release 1.0
         */
        public function getAlbum($id){
            $db_handle = $this->dbh;

            $query = '
                Select
                    * 
                FROM 
                    Albums 
                WHERE 
                   albumID = :id 
                ORDER BY 
                    albumID DESC';

            $result = executeQuery($query, array('id'=>$id));

            if( !$result instanceof PDOStatement )
                return $result;

            $album_data = $result->fetchAll(PDO::FETCH_ASSOC);
            // Save state
            $this->setState($album_data[0]);

            return $this;
        }

        /**
         * Add a photo to this album
         *
         * @access public
         * @return Photo
         * @since method available since release 1.0
         */
        public function addPhoto(){
            // FIXME: every photo added to an album should be added to its gallery if not already.
            return $this->current_photo->addPhotoTo('album', $this->albumID);
        }

        /**
         * Get a photo from the DB for this album
         *
         * @access public
         * @return Photo
         * @since method available since release 1.0
         */
        public function retrievePhoto(){
            return $this->current_photo->getPhotoFrom('album', $this->albumID);
        }

        /**
         * Set the current working photo for this album
         *
         * @param Photo $photo_obj
         * @access public
         * @return void
         * @since method available since release 1.0
         */
        public function setPhoto(Photo $photo_obj){
            $this->current_photo = $photo_obj;
        }

        /**
         * Get the current working photo
         *
         * @access public
         * @return void
         * @since method available since release 1.0
         */
        public function getPhoto(){
            return $this->current_photo;
        }

        /**
         * Get all photos for this album
         *
         * @param Photo $photo_obj
         * @access public
         * @return array
         * @since method available since release 1.0
         */
        public function getAlbumPhotos(Photo $photo_obj = null){
            if( !is_null($photo_obj) )
                $this->album_photos = $photo_obj->getRefPhotos('album', $this->albumID);
            else
                $this->album_photos = $this->current_photo->getRefPhotos('album', $this->albumID);

            return $this->album_photos;
        }

        /**
         * Delete a photo from this album
         *
         * @access public
         * @return Photo
         * @since method available since release 1.0
         */
        public function deletePhoto()
        {
            return $this->current_photo->deletePhotoFrom('album', $this->albumID);
        }

        /**
         * Get a list of all albums for a gallery
         *
         * @param integer $galleryid
         * @access public
         * @return array
         * @since method available since release 1.0
         */
        public function getGalleryAlbums($galleryid){
            $db_handle = $this->dbh;

            $query = '
            Select
                *  
            FROM 
                Albums 
            WHERE 
                galleryID = :galleryid';

            $query_data = array('galleryid'=>$galleryid);
            $prep = $db_handle->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $prep->execute($query_data);
            $albums = $prep->fetchAll(PDO::FETCH_OBJ);
            return $albums;
        }

        /**
         * Delete an album
         *
         * @param int $id
         * @access public
         * @return $this
         * @since method available since release 1.0
         */
        public function delete($id = 0){
            $db_handle = $this->dbh;

            $query = '
            Delete
            FROM
                Albums
            WHERE
                albumID = :id';

            $query_data = array('id'=>($id <= 0 ? $this->albumID : $id));
            $prep = $db_handle->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $stmt = $prep->execute($query_data);

            // Delete dependencies
            if( $stmt ){
                // Delete all album photos
                $this->album_photos = $this->getAlbumPhotos();

                foreach( $this->album_photos as $photo_data_obj ) {
                    $this->current_photo->deletePhotoFrom('album', $this->albumID, $photo_data_obj->photoID);
                }
            }
            return $this;
        }
    }

/**
 * Album Factory
 *
 * @param integer $albumid
 * @access public
 * @return Album
 * @since method available since release 1.0
 */
function albumFactory($albumid = 0){
    $album_obj = new Album();

    if( $albumid > 0 )
        return $album_obj->getAlbum($albumid);

    return $album_obj;
}