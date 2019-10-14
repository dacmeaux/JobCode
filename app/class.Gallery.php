<?php
    require_once 'app/class.BaseObj.php';

class Gallery extends BaseObj {
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
            'current_album'=>null,
            'gallery_albums'=>array(),
            'current_photo'=>null,
            'gallery_photos'=>array()
        );
    }

    /**
     * Create a gallery
     *
     * @param string $title
     * @param integer $userid
     * @access public
     * @return Gallery|array
     * @since method available since release 1.0
     */
    public function create($title, $userid = 0){
        $db_handle = $this->dbh;

        $query = '
            INSERT INTO
                Galleries
            (userID, galleryTitle)
            VALUES (:userid, :title)';

        $result = executeQuery($query, array('title'=>$title, 'userid'=>$userid));

        if( !$result instanceof PDOStatement )
            return $result;

        return $this->getGallery($db_handle->lastInsertId());
    }

    /**
     * Get this gallery from the DB
     *
     * @param integer $id
     * @access public
     * @return array|Gallery
     * @since method available since release 1.0
     */
    public function getGallery($id){
        $db_handle = $this->dbh;

        $query = '
            Select
                * 
            FROM 
                Galleries 
            WHERE 
                galleryID = :id';

        $result = executeQuery($query, array('id'=>$id));

        if( !$result instanceof PDOStatement )
            return $result;

        $gallery_data = $result->fetchAll(PDO::FETCH_ASSOC);
        // Save state
        $this->setState($gallery_data[0]);

        return $this;
    }

    /**
     * Delete a gallery
     *
     * @param int $id
     * @access public
     * @return array|Gallery
     * @since method available since release 1.0
     */
    public function delete($id = 0){
        $db_handle = $this->dbh;

        $query = '
            Delete
            FROM
                Galleries
            WHERE
                galleryID = :id';

//        $query_data = array('id'=>($id <= 0 ? $this->galleryID : $id));
//        $prep = $db_handle->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
//        $stmt = $prep->execute($query_data);

        $result = executeQuery($query, array('id'=>($id <= 0 ? $this->galleryID : $id)));

        if( !$result instanceof PDOStatement )
            return $result;

        // Delete dependencies
        echo 'Gallery with ID '. $this->galleryID .' deleted';

        // Delete all Gallery Albums
        $gallery_albums = $this->current_album->getGalleryAlbums($this->galleryID);

        foreach( $gallery_albums as $album_data_obj ) {
            $this->current_album->deleteAlbum($album_data_obj->albumID);
        }

        // Delete all gallery photos
        $gallery_photos = $this->current_photo->getRefPhotos('gallery', $this->galleryID);

        foreach( $gallery_photos as $photo_data ) {
            $this->current_photo->deletePhotoFrom('album', $this->albumID, $photo_data->photoID);
        }

        return $this;
    }

    /**
     * Get a list of all user galleries
     *
     * @param integer $userid
     * @access public
     * @return array
     * @since method available since release 1.0
     */
    public function getUserGalleries($userid){
        $db_handle = $this->dbh;

        $query = '
            Select
                galleryID  
            FROM 
                Galleries 
            WHERE 
                userID = :userid 
            ORDER BY 
                galleryID DESC';

//        $query_data = array('userid'=>$userid);
//        $prep = $db_handle->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
//        $prep->execute($query_data);

        $result = executeQuery($query, array('userid'=>$userid));

        if( !$result instanceof PDOStatement )
            return $result;

        $galleries = $result->fetchAll(PDO::FETCH_OBJ);
        return $galleries;
    }

    /**
     * Add a photo to this gallery
     *
     * @access public
     * @return Photo
     * @since method available since release 1.0
     */
    public function addPhoto(){
        return $this->current_photo->addPhotoTo('gallery', $this->galleryID);
    }

    /**
     * Get gallery photo from DB
     *
     * @access public
     * @return Photo
     * @since method available since release 1.0
     */
    public function retrievePhoto(){
        return $this->current_photo->getPhotoFrom('gallery', $this->galleryID);
    }

    /**
     * Add an album to this gallery
     *
     * @param string $title
     * @access public
     * @return Album
     * @since method available since release 1.0
     */
    public function addAlbum($title){
        $this->current_album = $this->current_album->create($title, $this->galleryID);
        return $this->current_album;
    }

    /**
     * Set the current working album for this gallery
     *
     * @param Album $album_obj
     * @access public
     * @return Void
     * @since method available since release 1.0
     */
    public function setAlbum(Album $album_obj){
        $this->current_album = $album_obj;
    }

    /**
     * Get the current album for this gallery
     *
     * @access public
     * @return Album
     * @since method available since release 1.0
     */
    public function getAlbum(){
        return $this->current_album;
    }

    /**
     * Set current photo for this gallery
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
     * Get the current photo for this gallery
     *
     * @access public
     * @return Photo
     * @since method available since release 1.0
     */
    public function getPhoto(){
        return $this->current_photo;
    }

    /**
     * Get all albums for this gallery
     *
     * @param Album $album_obj
     * @access public
     * @return array
     * @since method available since release 1.0
     */
    public function getGalleryAlbums(Album $album_obj){
        $this->gallery_albums = $album_obj->getGalleryAlbums($this->galleryID);

        return $this->gallery_albums;
    }

    /**
     * Get all photos for this gallery
     *
     * @param Photo $photo_obj
     * @access public
     * @return array
     * @since method available since release 1.0
     */
    public function getGalleryPhotos(Photo $photo_obj){
        $this->gallery_photos = $photo_obj->getRefPhotos('gallery', $this->galleryID);

        return $this->gallery_photos;
    }

    /**
     * Delete a photo from this gallery
     *
     * @access public
     * @return Photo
     * @since method available since release 1.0
     */
    public function deletePhoto(){
        return $this->current_photo->deletePhotoFrom('gallery', $this->galleryID);
    }

    /**
     * Delete an album from this gallery
     *
     * @access public
     * @return Album
     * @since method available since release 1.0
     */
    public function deleteAlbum(){
        return $this->current_album->delete($this->galleryID);
    }
}

/**
 * Gallery Factory
 *
 * @param integer $galleryid
 * @access public
 * @return Gallery
 * @since method available since release 1.0
 */
function galleryFactory($galleryid = 0){
    $gallery_obj = new Gallery();

    if( $galleryid > 0 )
        return $gallery_obj->getGallery($galleryid);

    return $gallery_obj;
}