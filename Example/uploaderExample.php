<?php

// set the allowed mime types for the allowed images
$allowedMimeTypes = array('image/jpeg', 'image/png', 'image/gif'); 

// set the allowed image extensions
$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

// try uploading the file, if it fails, return error message
try {
    // create the file uploader class
    $uploader = new FileUploader('file', $allowedMimeTypes, $allowedExtensions);

    // upload the file
    $uploader->upload();
} catch (Exception $e) {
    // return the error message as json string
    echo json_encode(array(
        'files' => array(
            array('error' => $e->getMessage())
        )
    ));
    exit;
}

// create a data array, containing all the values that have to be saved
// to the database for the uploaded file
$data = array(
    'name'      => $uploader->uploadFileName(),
    'url'       => $uploader->name(),
    'ext'       => $uploader->extension()
);

// store the file information in the database
$mediaModel = new \MediaModel();
$mediaModel>saveMediaFile($data);

// to pass data through iframe you will need to encode all html tags
echo json_encode(
    array(
        'files' => array(
            $data
        )
    )
);


