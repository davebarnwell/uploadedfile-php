uploadedfile
============

Handle an uploaded file

    try {
      $file = new UploadedFile('formFilename',array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG), 200000);
      if ($file->uploadOK()) {
        $file->save('/some/directory','somefile.'.$file->getExtension());
      }
    } catch (Exception $e) {
      // oversize or not valid image type
      echo 'Invalid';
    }
    