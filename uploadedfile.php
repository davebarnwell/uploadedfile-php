<?php
/**
 * handles saving uploaded files, typically images, but will handle all file types
 *
 * @property int size
 * @property string name
 * @property string tmp_name
 * @author Dave Barnwell <dave@freshsauce.co.uk>
 */
class UploadedFile {
  private $uploadError    = true;
  private $fileUploaded   = false;

  public $uploadFile;
  public $form_field_name = null;
  public $image_type      = null;
  public $height          = null;
  public $width           = null;
  public $extension       = null;
  public $allowed_types   = null;

  /**
   * takes the form fildname and checks it was uploaded, throws if upload had errors
   *
   * @param string $formFileName the form field name assocaited with the uploaded file
   * @param array $allowed_types_user defauts to images, throws error if mime type is not in array
   * @param string $max_size if provided throws an error if the file is larger in bytes
   */
  function __construct($formFileName, $allowed_types_user = null, $max_size = null)
  {
    if ($allowed_types_user === null || !is_array($allowed_types_user)) {
      $allowed_types_user = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
    }
    $this->allowed_types = $allowed_types_user;
    $this->form_field_name = $formFileName;

    if (isset($_FILES[$formFileName]['error']) && $_FILES[$formFileName]['error'] == UPLOAD_ERR_NO_FILE) {
      // no file, don't thrown an error
      $this->uploadFile = $_FILES[$formFileName];
    } else {
      if (is_uploaded_file($_FILES[$formFileName]['tmp_name'])) {
        $this->fileUploaded = true;
        $this->uploadFile = $_FILES[$formFileName];
        $this->uploadError(); // will throw an exception on error
        list($this->width, $this->height, $this->image_type) = getimagesize($_FILES[$formFileName]['tmp_name']);
        $this->extension = image_type_to_extension($this->image_type);
        if (!in_array($this->image_type, $this->allowed_types)) {
          throw new Exception("Invalid image type[{$this->image_type}]", 1);
        }
        if ($max_size !== NULL && $this->size > $max_size) {
          throw new Exception("Invalid file size[{$this->size}]", 1);
        }
      } else {
        // no uploaded file
        $this->fileUploaded = false;
      }
    }
  }
  
  public function recieved() {
    return $this->fileUploaded; // indicates a file was recieved, but may have errors thrown as exceptions on construction
  }
  
  public function uploadOK() {
    return $this->fileUploaded && !$this->uploadError;
  }
  
  public function getExtension() {
    return ($this->extension == '.jpeg') ? '.jpg' : $this->extension;
  }
  
  private function uploadError() {
    $this->uploadError = false;
    if ($this->uploadFile['error'] != UPLOAD_ERR_OK) {
      $this->uploadError = true;
      switch ($this->uploadFile['error']) {
        case UPLOAD_ERR_INI_SIZE:
          throw new Exception('File to large');
        case UPLOAD_ERR_FORM_SIZE:
          throw new Exception('Form data to large');
        case UPLOAD_ERR_PARTIAL:
          throw new Exception('The file only partially uploaded');
        case UPLOAD_ERR_NO_FILE:
          throw new Exception('No file was uploaded');
        case UPLOAD_ERR_NO_TMP_DIR:
          throw new Exception('No temporary directory');
        case UPLOAD_ERR_CANT_WRITE:
          throw new Exception('Can\'t write uploaded file to disk');
        case UPLOAD_ERR_EXTENSION:
          throw new Exception('An extension stopped the upload');
        default:
          throw new Exception('File did not upload sucessfully');
      }
    }
    return false;
  }
  
  public function __get($name) {
    if ($this->uploadError) {
      throw new Exception('Can\'t get property['.$name.'] a file upload error occured.');
    }
    if (!array_key_exists($name, $this->uploadFile)) {
      throw new Exception('No Such property['.$name.']');      
    }
    return $this->uploadFile[$name];
  }
  
  public function getSize() {
    return $this->size;
  }

  public function getRawType() {
    $info = getimagesize($this->uploadFile->tmp_name);
    return $info[2];
  }

  public function getType() {
    return $this->type;
  }

  /**
   * saves the file to the given path and filename
   *
   * @param string $path 
   * @param string $name defaults to the name of the uploaded file (please override this)
   * @return bool
   */
  public function save($path, $name = null) {
    if ($name === null) {
      $name = $this->name; // pull user provided name from image upload; Dont trust user provided names!
    }
    if (strlen($path) == 0) {
      throw new Exception('Can\'t save file as no path specified.');
    }
    if ($this->uploadError) {
      throw new Exception('Can\'t get save file as an upload error occured.');
    }
    if (substr($path, -1) != DIRECTORY_SEPARATOR) {
      $path .= DIRECTORY_SEPARATOR;
    }
    return move_uploaded_file($this->tmp_name, $path.$name);
  }
}
?>
