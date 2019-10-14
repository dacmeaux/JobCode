<?php
    require_once 'app/DB.php';
    require_once 'app/class.User.php';
    require_once 'app/class.Gallery.php';
    require_once 'app/class.Album.php';
    require_once 'app/class.Photo.php';

    // Create empty Objects
    $user_obj = new User();
    $gallery_obj = galleryFactory();
    $album_obj = albumFactory();
    $photo_obj = photoFactory();

    // Create a new user
    $user_obj->create('New User '. mt_rand(0,100000));
    // or get an existing user
//    $userid = 1;
//    $user_obj->getUser($userid);

    // Inject this gallery object into this user.
    // This is the current gallery object for all user  
    // actions until it is replaced by another gallery object.
    $user_obj->setGallery($gallery_obj);

    echo '<pre>User Object: '. var_export($user_obj, true) .'</pre>';

    // Create a new gallery for this user
    $gallery_obj = $user_obj->addGallery('My New Gallery: '. mt_rand(0, 100000));

    echo '<pre>Gallery Object: '. var_export($gallery_obj, true) .'</pre>';

    // Get all  galleries for this user
    // This returns an array of gallery IDs
    $user_galleries = $user_obj->getUserGalleries($gallery_obj);

    // Get a gallery with galleryID for this user
    if( $user_galleries && is_array($user_galleries) && sizeof($user_galleries) > 0 ) {
        $gallery_obj = $gallery_obj->getGallery($user_galleries[0]->galleryID);
        // Make this the current user gallery
        $user_obj->setGallery($gallery_obj);
    }

    // This ends up being the same gallery that was created earlier
    echo '<pre>Gallery Object with ID: '. var_export($gallery_obj, true) .'</pre>';

    // Upload a user photo
    $photo_data = ['title'=>'My New Photo: '. mt_rand(0, 10000), 'name'=>'some-photo-path.jpg'];
    $photo_obj = $user_obj->uploadPhoto($photo_obj, $photo_data);

    echo '<pre>Uploaded Photo Object: '. var_export($photo_obj, true) .'</pre>';

    // Inject this album object into this gallery.
    // This is the current album object for all gallery
    // actions until it is replaced by another album object.
    $gallery_obj->setAlbum($album_obj);

    // Create an album for this gallery
    $album_obj = $gallery_obj->addAlbum('My New Album: '. mt_rand(0,10000));

    // Get a photo with ID
    $photo_obj->getPhoto(25);

    echo '<pre>Uploaded Photo Object: '. var_export($photo_obj, true) .'</pre>';

    // Inject photo object with ID into this gallery.
    // This is the current photo object for all gallery
    // actions until it is replaced by another photo object.
    $gallery_obj->setPhoto($photo_obj);

    // Add this photo with ID to this gallery
    $photo_obj = $gallery_obj->addPhoto();

    // Inject this photo object into this album.
    // This is the current photo object for all album
    // action until it is replaced by another photo object.
    $album_obj->setPhoto($photo_obj);
    // Add this photo to this album
    $album_obj->addPhoto();

    // Delete photo with ID from this gallery
    $gallery_obj->deletePhoto();

//    $album_obj->deletePhoto();

    // Delete the current user Gallery
//    $user_obj->deleteGallery();