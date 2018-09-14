<?php
// include the upload exception class
require_once __DIR__ . '/UploadException.php';

/**
 * Helper class for handling file uploads.
 * 
 * @author ThePerfectWedding.nl - Thomas Marinissen
 */
class FileUploader {
    /**
     * constant variable that holds the error code if a file is of the wrong
     * MIME type.
     */
    const FILE_WRONG_MIME = 11;

    /**
     * constant variable that holds the error code if a file is of the wrong
     * extension.
     */
    const FILE_WRONG_EXTENSION = 12;

    /**
     * constant variable that holds the error code in case no files were
     * uploaded
     */
    const NO_FILES_UPLOADED = 13;

    /**
     * constant variable that is used whenever the requested upload directory
     * is invalid
     */
    const INVALID_UPLOAD_DIR = 14;
    
    /**
     * constant variable that is used whenever it is not possible to move the
     * uploaded file using move_uploaded_file().
     */
    const CAN_NOT_MOVE_FILE = 15;

    /**
     * The file name of an upload. This file name either comes from the
     * $_FILES['upload_name'][name] or from input of the user.
     * 
     * @var string
     */
    private $uploadFileName;

    /**
     * Destination directory for the uploaded file. The default destination is 
     * the /tmp/ directory
     * 
     * @var string
     */
    private $uploadDir = "/tmp/";

    /**
     * Original name of the uploaded file.
     * 
     * @var string
     */
    private $name;

    /**
     * Temporary name of the uploaded file
     * 
     * @var string
     */
    private $tmp_name;

    /**
     * The MIME type of the uploaded file
     * 
     * @var string|null
     */
    private $type = null;

    /**
     * The size of the uploaded file
     * 
     * @var int
     */
    private $size;
    
    /**
     * The uploaded file extension
     * 
     * @var string|null
     */
    private $extension = null;

    /**
     * The error code of the uploaded file (0 is no error)
     * 
     * @var int
     */
    private $error;

    /**
     * Array of the allowed file types for an upload.
     * 
     * @var array|null
     */
    private $allowedTypes = null;

    /**
     * Array class attribute that defines what extensions are allowed.
     * 
     * @var array|null
     */
    private $allowedExtensions = null;
    
    /**
     * The instance of the media model object
     * 
     * @var \Application_Model_Media|null
     */
    private $mediaModel = null;

    /**
     * Constructor method of the Tpw_Controller_Helper_Upload_FileUploader class.
     * 
     * @param string        The upload field name
     * @param array|null    The allowed file mime types
     * @param array|null    The allowed file extensions
     * @param string        The upload directory
     */
    public function __construct($fieldName, $allowedTypes = null, $allowedExtension = null, $uploadDir = null) {
        // set the different class variables
        $this->setAllowedTypes($allowedTypes)
            ->setAllowedExtensions($allowedExtension)
            ->setUploadDir($uploadDir)
            ->setUploadFile($fieldName)
            ->setUploadFileName(basename($this->name(), '.' . $this->extension()));
        
        // fix the file name
        $this->setName($this->name);
    }

    /**
     * Function for getting the upload file name.
     * 
     * @return string               The upload file name
     */
    public function uploadFileName() {
        return $this->uploadFileName;
    }

    /**
     * Function for getting the upload directory name.
     * 
     * @return string               The upload file directory path
     */
    public function uploadDir() {
        return $this->uploadDir;
    }

    /**
     * Method for getting the upload destination (directory + path).
     * 
     * @return string               The upload file path including the file name
     */
    public function uploadFilePath() {
        return $this->uploadDir() . $this->name();
    }

    /**
     * Returns the original file name.
     * 
     * @return string               The original file name
     */
    public function name() {
        return $this->name;
    }

    /**
     * Method returns the temporary file name.
     * 
     * @return string               The temp file name
     */
    public function tmpName() {
        return $this->tmp_name;
    }

    /**
     * Method that returns the MIME type of the uploaded file.
     *  
     * @return string               The file MIME type
     */
    public function type() {
        // if the type is known, return it
        if (!is_null($this->type)) {
            return $this->type;
        }
        
        // get the file info
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        
        // get the MIME type of the file 
        $mime= finfo_file($finfo, $this->tmpName());
        
        // clode the file info resource
        finfo_close($finfo);
        
        // store and return the file MIME type
        return $this->type = $mime;
    }

    /**
     * Method that returns the size of the uploaded file.
     * 
     * @return int                  The file size
     */
    public function size() {
        return $this->size;
    }
    
    /**
     * Get the uploaded file extension
     * 
     * @return  string              The file extension
     */
    public function extension() {
        // if an extension is already known, return it
        if (!is_null($this->extension)) {
            return $this->extension;
        }
        
        // get the path info
        $pathInfo = pathinfo($this->name());
        
        // get the extension, set it and return it
        return $this->extension = $pathInfo['extension'];
    }

    /**
     * Returns the error code (0 if there is no error)
     * 
     * @return int                  The error code
     */
    public function error() {
        return $this->error;
    }

    /**
     * Method that returns an array with the allowed MIME types. Will return an
     * empty array if there are no restrictions for the MIME type.
     * 
     * @return array                The allowed MIME types
     */
    public function allowedTypes() {
        return $this->allowedTypes;
    }

    /**
     * Method that returns the array with the allowed file extensions for the
     * uploaded files.
     * 
     * @return array                The allowed upload file extensions
     */
    public function allowedExtensions() {
        return $this->allowedExtensions;
    }
    
    /**
     * Get the instance of the media model
     * 
     * @return Application_Model_Media                  The instance of the media model object
     */
    public function mediaModel() {
        // if the media model has been set before, return it
        if (!is_null($this->mediaModel)) {
            return $this->mediaModel;
        }
        
        // create a new media model and return it
        return $this->mediaModel = new Application_Model_Media;
    }
    
    /**
     * Set the uploaded file parameters
     * 
     * @param   string                                              The upload parameter
     * @return  \Ivaldi_Controller_Helper_Upload_FileUploader       The instance of this, to make chaining possible
     * @throws  UploadException                       Thrown if there are no files uploaded
     */
    public function setUploadFile($fieldName) {
        // if no files are uploaded, throw and UploadException
        if (count($_FILES) == 0 || !isset($_FILES[$fieldName])) {
            throw new UploadException(Tpw_Controller_Helper_Upload_FileUploader::NO_FILES_UPLOADED);
        }

        // iterate over the uploaded files and set the value for every file value
        foreach ($_FILES[$fieldName] as $key => $value) {
            // continue to the next iteration if the key is named 'type'
            if ($key == 'type') {
                continue;
            }
            
            // get the value if it is an array
            if (is_array($value)) {
                $value = $value[0];
            }
            
            // set the value of the current iteration
            $this->{$key} = $value;
        }
        
        // return the instance of this, to make chaining possible
        return $this;
    }

    /**
     * Set a custom name for the uploaded file.
     * 
     * @param   string                                                  The upload file name
     * @return  Ivaldi_Controller_Helper_Upload_FileUploader            The instance of this, to make chaining possible
     */
    public function setUploadFileName($name) {
        // set the upload file name
        $this->uploadFileName = $name;
        
        // done, return the instance of this, to make chaining possible
        return $this;
    }
    
    /**
     * 
     * @param   string                                              The base name of the file
     * @return  \Tpw_Controller_Helper_Upload_FileUploader          The instance of this to make chaining possible
     */
    public function setName($name) {
        // get the path info of the given name
        $pathinfo = pathinfo($name);
        
        // get the base file name
        $filename = Ivaldi_Controller_Router_Route_Database::sanitize($pathinfo['filename']);
        
        // set the file name
        $this->name = $filename . '.' . $this->extension;
        
        // if the file already exists, fix it by adding the microtime to the
        // file name
        if ($this->fileExists()) {
            $this->name = $filename . str_replace('.', '', microtime(true)) . '.' . $this->extension();
        }
        
        // done, return the instance of this, to make chaining possible
        return $this;
    }

    /**
     * Method that sets the destination upload directory. It checks if the requested
     * directory is a valid directory, if not, it throws a new UploadException.
     * 
     * @param   string                                                  The directory to upload the file to
     * @return  Ivaldi_Controller_Helper_Upload_FileUploader            The instance of this, to make chaining possible
     * @throws  UploadException                           Thrown if the the directory is not a valid directory.
     */
    public function setUploadDir($dir) {
        // if no upload dir is given, return
        if (is_null($dir)) {
            return $this;
        }
        
        // if the upload directory is not a valid directory, throw an exception
        if (!is_dir($dir)) {
            throw new UploadException(Tpw_Controller_Helper_Upload_FileUploader::INVALID_UPLOAD_DIR);
            
        }
        
        // set the upload directory
        $this->uploadDir = $dir;
        
        // done, return the instance of this, to make chaining possible
        return $this;
    }

    /**
     * Method that sets the allowed MIME types.
     * 
     * @param   array                                                   The allowed mime types for upload
     * @return  Ivaldi_Controller_Helper_Upload_FileUploader            The instance of this, to make chaining possible
     */
    public function setAllowedTypes($allowedTypes) {
        $this->allowedTypes = $allowedTypes;
        
        // done, return the instance of this, to make chaining possible
        return $this;
    }

    /**
     * Method that sets the allowed file extensions
     * 
     * @param   array                                                   The allowed file extensions for upload
     * @return  Ivaldi_Controller_Helper_Upload_FileUploader            The instance of this, to make chaining possible
     */
    public function setAllowedExtensions($allowedExtensions) {
        $this->allowedExtensions = $allowedExtensions;
        
        // done, return the instance of this, to make chaining possible
        return $this;
    }
    
    /**
     * Helper function to check if a media item already exists
     * 
     * @return boolean                  Whether the media item already exists
     */
    public function fileExists() {
        // return the whether the media item with the slug already exists
        return file_exists($this->uploadFilePath());
    }

    /**
     * Method that checks if the uploaded file is of the allowed MIME type.
     * 
     * @return boolean                          Whether the upload file MIME type is allowed for upload
     * @throws UploadException    Thrown if the MIME type of the uploaded file is not allowed
     */
    public function validateFileType() {
        // if the allowed types is null, all file types are allowed
        if (is_null($this->allowedTypes())) {
            return true;
        }
        
        // if the upload file type is not in the list with allowed file types,
        // or if the allowed mime types list is not an array, throw a new exception
        if (!is_array($this->allowedTypes()) || !in_array($this->type(), $this->allowedTypes())) {
            throw new UploadException(Tpw_Controller_Helper_Upload_FileUploader::FILE_WRONG_MIME);
        }
        
        // everything ok
        return true;
    }

    /**
     * Method that checks if the uploaded file is of the allowed extension.
     * 
     * @return boolean                          Whether the uploaded file extension is an allowed extension
     * @throws UploadException    Thrown if the extension of the uploaded file is not allowed
     */
    public function validateExtension() {
        // if the allowed extension valie is null, all file extensions are allowed
        if (is_null($this->allowedExtensions())) {
            return true;
        }
        
        // if the allowed extensions list is not an array or if the uploaded file
        // extension is not in the list of allowed extensions, throw an exception
        if (!is_array($this->allowedExtensions()) || !in_array($this->extension(), $this->allowedExtensions())) {
            throw new UploadException(Tpw_Controller_Helper_Upload_FileUploader::FILE_WRONG_EXTENSION);
        }
        
        // everything ok
        return true;
    }

    /**
     * Method that checks if an upload is allowed, by checking the extension and
     * MIME type of the uploaded file compared to the allowed extensions and
     * MIME types.
     * 
     * @return boolean              Whether the upload is allowed
     */
    public function uploadAllowed() {
        return $this->validateFileType() && $this->validateExtension();
    }

    /**
     * Method that moves the uploaded file to the requested location and renames
     * the file.
     * If there is an error while uploading the file, throw a new UploadException.
     * 
     * @return boolean                          Whether the upload was successful
     * @throws UploadException    Thrown if an error occures while uploading the file
     */
    public function upload() {
        // check if the upload is allowed
        $this->uploadAllowed();
        
        // check if there is an erro
        if ($this->error() != 0) {
            throw new UploadException($this->error());
        }
        
        // try to move the uploaded file to the requested directory and renames
        // the file.
        if (!move_uploaded_file($this->tmpName(), $this->uploadFilePath())) {
            // failed the move the file
            throw new UploadException(Tpw);
        } 

        // everything ok
        return true;
    }

}
