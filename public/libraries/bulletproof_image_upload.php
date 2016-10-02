<?php
/**
 * BulletProof
 *
 * A single class PHP-library for secure image uploading.
 *
 * PHP support 5.3+
 *
 * @package     BulletProof
 * @version     2.0.0
 * @author      Samayo  /@sama_io
 * @link        https://github.com/samayo/bulletproof
 * @license     MIT
 */
namespace BulletProof;
class Image implements \ArrayAccess
{
    /**
     * @var string The new image name, to be provided or will be generated.
     */
    protected $name;
    /**
     * @var int The image width in pixels
     */
    protected $width;
    /**
     * @var int The image height in pixels
     */
    protected $height;
    /**
     * @var string The image mime type (extension)
     */
    protected $mime;
    /**
     * @var string The full image path (dir + image + mime)
     */
    protected $fullPath;
    /**
     * @var string The folder or image storage location
     */
    protected $location;
    /**
     * @var array A json format of all information about an image
     */
    protected $serialize = array();
    /**
     * @var array The min and max image size allowed for upload (in bytes)
     */
    protected $size = array(100, 50000);
    /**
     * @var array The max height and width image allowed
     */
    protected $dimensions = array(500, 5000);
    /**
     * @var array The mime types allowed for upload
     */
    protected $mimeTypes = array("jpeg", "png", "gif");
    /**
     * @var array list of known image types
     */
    protected $imageMimes = array(
        1 => "gif", "jpeg", "png", "swf", "psd",
        "bmp", "tiff", "tiff", "jpc", "jp2", "jpx",
        "jb2", "swc", "iff", "wbmp", "xbm", "ico"
    );
    /**
     * @var array storage for the $_FILES global array
     */
    private $_files = array();
    /**
     * @var string storage for any errors
     */
    private $error = "";
    /**
     * @param array $_files represents the $_FILES array passed as dependancy
     */
    public function __construct(array $_files = [])
    {
        $this->_files = $_files;
    }
    /**
     * Gets the real image mime type
     *
     * @param $tmp_name string The upload tmp directory
     *
     * @return bool|string
     */
    protected function getImageMime($tmp_name)
    {
        if (isset($this->imageMimes[exif_imagetype($tmp_name)])) {
            return $this->imageMimes[exif_imagetype($tmp_name)];
        }
        return false;
    }
    /**
     * array offset \ArrayAccess
     * unused
     */
    public function offsetSet($offset, $value){}
    public function offsetExists($offset){}
    public function offsetUnset($offset){}
    /**
     * Gets array value \ArrayAccess
     *
     * @param mixed $offset
     *
     * @return bool|mixed
     */
    public function offsetGet($offset)
    {
        if ($offset == "error") {
            return $this->error;
        }
        if (isset($this->_files[$offset]['value']) && file_exists($this->_files[$offset]["tmp_name"]['value'])) {
            $this->_files = $this->_files[$offset]['value'];
            return true;
        }
        
        return false;
    }
    /**
     * Renames image
     *
     * @param null $isNameGiven if null, image will be auto-generated
     *
     * @return $this
     */
    public function setName($isNameProvided = null)
    {
        if ($isNameProvided) {
            $this->name = filter_var($isNameProvided, FILTER_SANITIZE_STRING);
        }
        
        return $this;
    }
    /**
     * Define a mime type for uploading
     *
     * @param array $fileTypes
     *
     * @return $this
     */
    public function setMime(array $fileTypes)
    {
        $this->mimeTypes = $fileTypes;
        return $this;
    }
    /**
     * Define a min and max image size for uploading
     *
     * @param $min int minimum value in bytes
     * @param $max int maximum value in bytes
     *
     * @return $this
     */
    public function setSize($min, $max)
    {
        $this->size = array($min, $max);
        return $this;
    }
    /**
     * Creates a location for upload storage
     *
     * @param $dir string the folder name to create
     * @param int $permission chmod permission
     *
     * @return $this
     */
    public function setLocation($dir = "bulletproof", $permission = 0666)
    {
        if (!file_exists($dir) && !is_dir($dir) && !$this->location) {
            $createFolder = @mkdir("" . $dir, (int) $permission, true);
            if (!$createFolder) {
                $this->error = "Folder " . $dir . " could not be created";
                return;
            }
        }
        $this->location = $dir;
        return $this;
    }
    /**
     * Sets acceptable max image height and width
     *
     * @param $maxWidth int max width value
     * @param $maxHeight int max height value
     *
     * @return $this
     */
    public function setDimension($maxWidth, $maxHeight)
    {
        $this->dimensions = array($maxWidth, $maxHeight);
        return $this;
    }
    /**
     * Returns the image name
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
           return  uniqid(true) . "_" . str_shuffle(implode(range("e", "q")));
        }
        return $this->name;
    }
    /**
     * Returns the full path of the image ex "location/image.mime"
     *
     * @return string
     */
    public function getFullPath()
    {
        $this->fullPath = $this->location . "/" . $this->name . "." . $this->mime;
        return $this->fullPath;
    }
    /**
     * Returns the image size in bytes
     *
     * @return int
     */
    public function getSize()
    {
        return (int) $this->_files["size"]['value'];
    }
    /**
     * Returns the image height in pixels
     *
     * @return int
     */
    public function getHeight()
    {
        if ($this->height != null) {
            return $this->height;
        }
        list(, $height) = getImageSize($this->_files["tmp_name"]['value']); 
        return $height;
    }
    /**
     * Returns the image width
     *
     * @return int
     */
    public function getWidth()
    {
        if ($this->width != null) {
            return $this->width;
        }
        list($width) = getImageSize($this->_files["tmp_name"]['value']); 
        return $width;
    }
    /**
     * Returns the storage / folder name
     *
     * @return string
     */
    public function getLocation()
    {
        if(!$this->location){
            $this->setLocation(); 
        }
        return $this->location; 
    }
    /**
     * Returns a JSON format of the image width, height, name, mime ...
     *
     * @return string
     */
    public function getJson()
    {
        return json_encode($this->serialize);
    }
    /**
     * Returns the image mime type
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }
    /**
     * Returns error string or false if no errors occurred
     *
     * @return string|bool
     */
    public function getError(){
        return $this->error != "" ? $this->error : false;
    }
    /**
     * Checks for the common upload errors
     *
     * @param $e int error constant
     */
    protected function uploadErrors($e)
    {
        $errors = array(
            UPLOAD_ERR_OK           => "",
            UPLOAD_ERR_INI_SIZE     => "Image is larger than the specified amount set by the server",
            UPLOAD_ERR_FORM_SIZE    => "Image is larger than the specified amount specified by browser",
            UPLOAD_ERR_PARTIAL      => "Image could not be fully uploaded. Please try again later",
            UPLOAD_ERR_NO_FILE      => "Image is not found",
            UPLOAD_ERR_NO_TMP_DIR   => "Can't write to disk, due to server configuration ( No tmp dir found )",
            UPLOAD_ERR_CANT_WRITE   => "Failed to write file to disk. Please check you file permissions",
            UPLOAD_ERR_EXTENSION    => "A PHP extension has halted this file upload process"
        );
        return $errors[$e];
    }
    /**
     * Main upload method.
     * This is where all the monkey business happens
     *
     * @return $this|bool
     */
    public function upload()
    {
        /* modify variable names for convenience */
        $image = $this; 
        $files = $this->_files;
        
        /* initialize image properties */
        $image->name     = $image->getName();
        $image->width    = $image->getWidth();
        $image->height   = $image->getHeight(); 
        $image->location = $image->getLocation();
        /* get image sizes */
        list($minSize, $maxSize) = $image->size;
        /* check for common upload errors */#
        if($image->error = $image->uploadErrors($files["error"]['value'])){
            return ;
        }
        /* check image for valid mime types and return mime */
        $image->mime = $image->getImageMime($files["tmp_name"]['value']);
        /* validate image mime type */
        if (!in_array($image->mime, $image->mimeTypes)) {
            $ext = implode(", ", $image->mimeTypes);
            $image->error = "Invalid File! Only ($ext) image types are allowed";
            return ;
        }     
        /* check image size based on the settings */
        if ($files["size"] < $minSize || $files["size"]['value'] > $maxSize) {
            $min = intval($minSize / 1000) ?: 1; $max = intval($maxSize / 1000);
            
            $image->error = "Image size should be atleast more than min: $min and less than max: $max kb";
            return ;
        }
        /* check image dimension */
        list($allowedWidth, $allowedHeight) = $image->dimensions;
        if ($image->height > $allowedHeight || $image->width > $allowedWidth) {
            $image->error = "Image height/width should be less than ' $allowedHeight \ $allowedWidth ' pixels";
            return ;
        }
        if($image->height < 4 || $image->width < 4){
            $image->error = "Invalid! Image height/width is too small or maybe corrupted"; 
            return ;
        }
 
        /* set and get folder name */
        $image->fullPath = $image->location. "/" . $image->name . "." . $image->mime;
        /* gather image info for json storage */ 
        $image->serialize = array(
            "name"     => $image->name,
            "mime"     => $image->mime,
            "height"   => $image->height,
            "width"    => $image->width,
            "size"     => $files["size"]['value'],
            "location" => $image->location,
            "fullpath" => $image->fullPath
        );
        if ($image->error === "") {
            $moveUpload = $image->moveUploadedFile($files["tmp_name"]['value'], $image->fullPath);
            if (false !== $moveUpload) {
                return $image;
            }
        }
        
        $image->error = "Upload failed, Unknown error occured";
        return false;
    }
    /**
     * Final upload method to be called, isolated for testing purposes
     *
     * @param $tmp_name int the temporary location of the image file
     * @param $destination int upload destination
     *
     * @return bool
     */
    public function moveUploadedFile($tmp_name, $destination)
    {
        return move_uploaded_file($tmp_name, $destination);
    }
}

namespace Bulletproof;
function resize($image, $mimeType, $imgWidth, $imgHeight, $newWidth, $newHeight, $ratio = FALSE, $upsize = TRUE){           
    
    // First, calculate the height.
    $height = intval($newWidth / $imgWidth * $imgHeight);
    // If the height is too large, set it to the maximum height and calculate the width.
    if ($height > $newHeight) {
        $height = $newHeight;
        $newWidth = intval($height / $imgHeight * $imgWidth);
    }
    // If we don't allow upsizing check if the new width or height are too big.
    if (!$upsize) {
        // If the given width is larger then the image height, then resize it.
        if ($newWidth > $imgWidth) {
            $newWidth = $imgWidth;
            $height = intval($newWidth / $imgWidth * $imgHeight);
        }
        // If the given height is larger then the image height, then resize it.
        if ($height > $imgHeight) {
            $height = $imgHeight;
            $newWidth = intval($height / $imgHeight * $imgWidth);
        }
    }
    if ($ratio == true)
    {
        $source_aspect_ratio = $imgWidth / $imgHeight;
        $thumbnail_aspect_ratio = $newWidth / $newHeight;
        if ($imgWidth <= $newWidth && $imgHeight <= $newHeight) {
            $newWidth = $imgWidth;
            $newHeight = $imgHeight;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $newWidth = (int) ($newHeight * $source_aspect_ratio);
            $newHeight = $newHeight;
        } else {
            $newWidth = $newWidth;
            $newHeight = (int) ($newWidth / $source_aspect_ratio);
        }
    }
            
    $imgString = file_get_contents($image);
    $imageFromString = imagecreatefromstring($imgString);
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled(
        $tmp,
        $imageFromString,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $imgWidth,
        $imgHeight
    );
    switch ($mimeType) {
        case "jpeg":
        case "jpg":
            imagejpeg($tmp, $image, 90);
            break;
        case "png":
            imagepng($tmp, $image, 0);
            break;
        case "gif":
            imagegif($tmp, $image);
            break;
        default:
            throw new \Exception(" Only jpg, jpeg, png and gif files can be resized ");
            break;
    }
}