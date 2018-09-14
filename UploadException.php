<?php
/**
 * Exception class for handling exceptions while uploading files. The exception
 * class makes use of the error codes that come with the $_FILES constant in
 * FILES['upload_name'][error]. Custom error codes can be found in the FileUploader
 * class.
 *
 * @author ThePefectWedding.nl - Thomas Marinissen
 */
class UploadException extends Exception {

    /**
     * Create a new Upload exception. The message sent is defined in the
     * privat codeToMessage method of this class.
     *
     * @param type $code int
     */
    public function __construct($code) {
        // get the error message
        $message = $this->codeToMessage($code);
        
        // call the parent
        parent::__construct($message, $code);
    }

    /**
     * Method that switches the error code constants till it finds the messages
     * that belongs to the error code and then returns the message.
     *
     * @param   int                 The error code
     * @return  string              The error message
     */
    private function codeToMessage($code) {
        // get the error message belong to the given code
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;
            case Tpw_Controller_Helper_Upload_FileUploader::FILE_WRONG_MIME:
                $message = "File is of the wrong MIME type";
                break;
            case Tpw_Controller_Helper_Upload_FileUploader::FILE_WRONG_EXTENSION:
                $message = "File is of the wrong extension";
                break;
            case Tpw_Controller_Helper_Upload_FileUploader::NO_FILES_UPLOADED:
                $message = "No files were uploaded";
                break;
            case Tpw_Controller_Helper_Upload_FileUploader::INVALID_UPLOAD_DIR:
                $message = "User set upload directory does not exist";
                break;
            case Tpw_Controller_Helper_Upload_FileUploader::CAN_NOT_MOVE_FILE:
                $message = 'Can not move the uploaded file';
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        
        // done, return the error message
        return $message;
    }
}
