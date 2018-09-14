<?php
/**
 * Class that can be used to get the dimensions of an image
 * 
 * @author ThePerfectWedding.nl - Thomas Marinissen
 */
class ImageResizer {
    
    /**
     * The image properties
     * 
     * @var array|null
     */
    private $imageProperties = null;
    
    /**
     * The maximum width when resizing
     * 
     * @var int
     */
    private $maxWidth;
    
    /**
     * The maximum height when resizing
     * 
     * @var int
     */
    private $maxHeight;
    
    /**
     * Constructor
     * 
     * @param string                The file path
     * @param int                   The maximum image width (when resizing)
     * @param int                   The maxium image height (when resizing)
     */
    public function __construct($file, $maxWidth = null, $maxHeight = null) {
        $this->setFile($file);
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }
    
    /**
     * Resize the image
     * 
     * @param   string                  The destination where to save the resized image
     * @param   int                     The resized image width
     * @param   int                     The resized image height
     * @param   boolean                 Should the image be cropped
     * @return  boolean                 Whether the resize operation was successful or not
     */
    public function resize($destination, $width, $height = null, $crop = false) {
        // if it is not possible to resize the image based on the width and / or
        // height, return out.
        if (!$this->canResize($width, $height)) {
            return false;
        }
        
        // create a new Imagick file object
        $img = new Imagick($this->file());
        
        // if the resize operation is a crop operation and the height is set,
        // crop the image, otherwhise scale the image
        if ($crop && !is_null($height)) {
            // crop the image
            $img->cropThumbnailImage($width, $height);
        } else {
            // calculate the height
            $height = round($width / $this->ratio());
            
            // scale the image
            $img->scaleImage($width, $height, true);
        }
        
        // remove all the meta data from the image
        $img->stripImage();
        
        // save the resized image
        $img->writeImage($destination);
    }
    
    /**
     * Create a thumbnail of the image
     * 
     * @param   string                  The destination where to save the resized image
     * @param   int                     The resized image width
     * @param   int                     The resized image height
     * @return  boolean                 Whether the thumbnail creation operation was successful or not
     */
    public function thumbnail($destination, $width, $height) {
        // create the thumbnail
        return $this->resize($destination, $width, $height, true);
    }
    
    /**
     * Get the image file path
     * 
     * @return string
     */
    public function file() {
        return $this->file;
    }
    
    /**
     * Get the image width
     * 
     * @return int          The image width
     */
    public function width() {
        // get the image properties
        $imageProperties = $this->imageProperties();
        
        // return the width
        return $imageProperties[0];
    }
    
    /**
     * Get the image height
     * 
     * @return int          The image height
     */
    public function height() {
        // get the image properties
        $imageProperties = $this->imageProperties();
        
        // return the height
        return $imageProperties[1];
    }
    
    /**
     * Calculate the image ration (width / height)
     * 
     * @return float                    The image ratio
     */
    public function ratio() {
        return $this->width() / $this->height();
    }
    
    /**
     * Set the image file
     * 
     * @param   string                                              The image file to set
     * @return  \Tpw_Controller_Helper_Upload_ImageDimensions       The instance of this, to make chaining possible
     */
    private function setFile($file) {
        // set the initial file
        $this->file = $file;
        
        // check if the file is a valid image
        $this->validImage();
        
        // return the instance of this to make chaining possible
        return $this;
    }
    
    /**
     * Function to check if the image is valid
     * 
     * @return boolean                  Whether an image is a valid image
     * @throws Exception                Thrown if the image is not a valid image, or if the image is not supported
     */
    private function validImage() {
        // get the image properties
        $imageProperties = $this->imageProperties();
        
        // get the image type
        $imageType = $imageProperties[2];
        
        // if the image type is not one of the supported image types, throw a
        // new exception
        if (!in_array($imageType , array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
            throw new Exception('Image type is not supprted');
        }

        // done, return whether the file is an image and of the supported type
        return in_array($imageType , array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG));
    }
    
    /**
     * Function to get the image properties
     * 
     * @return  array           The image properties as an array
     * @throws  Exception       Thrown whenever the image is not a valid image
     */
    private function imageProperties() {
        // if the image properties have been set before, return them
        if (!is_null($this->imageProperties)) {
            return $this->imageProperties;
        }
        
        // get the image properties
        $imageProperties = getimagesize($this->file());
        
        // if there are no image properties, throw a new exception
        if ($imageProperties === false) {
            throw new Exception('File is not a valid image');
        }
        
        // done, set and return the image properties
        return $this->imageProperties = $imageProperties;
    }
    
    /**
     * Helper function that checks if it is possible to resize an image based on
     * the image widht and height
     * 
     * @param   int                 The image width to resize the image to
     * @param   int                 The image height to resize the image to
     * @return boolean
     */
    private function canResize($width, $height = null) {
        if ($this->width() >= $width || $this->maxWidth >= $width) {
            return true;
        }
        
        if (is_null($height) || is_null($this->maxHeight)) {
            return true;
        }
        
        
        
        // if the given width is bigger than the source image width, it is not
        // posible to resize the image
        
        
        // if the height is set and the given height is bigger than the source
        // image height, not possible to resize the image
        if (!is_null($height) && !is_null($this->maxHeight) && $this->maxHeight < $height) {
            return false;
        }
        
        // the image can be resized
        return true; 
    }
}